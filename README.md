# 🏠 UAP_PDT - siSRI (Sistem Rekomendasi Indekos)
**siSRI (Sistem Rekomendasi Indekos)** adalah sistem pemesanan dan manajemen indekos berbasis web yang dibangun menggunakan PHP dan MySQL. Fokus utama sistem adalah mengelola pemesanan kamar kos secara efisien dengan penerapan stored procedure, stored function, trigger, transaction, serta sistem backup otomatis.

<img src="assets/img/siSri.png" alt="Tampilan Web" width="1000">

**📌 DETAIL KONSEP**

**-- TRANSACTION --**

Setiap procedure di SiSRI dilengkapi dengan START TRANSACTION dan COMMIT/ROLLBACK untuk menjamin bahwa proses hanya akan disimpan ke database jika seluruh tahapan berhasil.
Implementasi transaction

    START TRANSACTION;
    
    -- perhitungan harga dan insert booking
    
    IF price IS NULL THEN
        ROLLBACK;
    ELSE
        COMMIT;
    END IF;


**-- STORED PROCEDURE --**

Stored procedure adalah instruksi yang disimpan di database untuk mengeksekusi operasi penting secara otomatis. Dalam sistem terdistribusi, ia menjamin efisiensi, konsistensi, dan keamanan eksekusi antar node dan pengguna.


<img src="assets/img/procedure.png" alt="Procedure" width="600">

Beberapa contoh precedure yang digunakan :

bookRoom

Melakukan proses pemesanan dan menghitung total harga secara otomatis. Jika harga tidak ditemukan, transaksi dibatalkan.

    CALL bookRoom(room_id, user_id, start_date, end_date, @total_price);

ApproveBookingAndSetRoomStatus

Menyetujui pemesanan sekaligus mengubah status kamar menjadi terisi.

    CALL ApproveBookingAndSetRoomStatus(booking_id);

RejectBooking

Menolak pemesanan dan melakukan rollback jika status tidak memenuhi syarat.

    CALL RejectBooking(booking_id);


**-- TRIGGERS --**

<img src="assets/img/Trigger.png" alt="Procedure" width="600">

    CREATE TRIGGER trg_update_ketersediaan_kamar
    AFTER UPDATE ON bookings
    FOR EACH ROW
    BEGIN
        IF NEW.status = 'confirmed' AND OLD.status != 'confirmed' THEN
            UPDATE indekos
            SET status_ketersediaan = 'terisi'
            WHERE id = NEW.room_id;
        END IF;
    
        IF NEW.status = 'rejected' AND OLD.status = 'confirmed' THEN
            UPDATE indekos
            SET status_ketersediaan = 'tersedia'
            WHERE id = NEW.room_id;
        END IF;
    END;


**-- FUNCTIONS --**


CalculateBookingDuration(start_date, end_date)<br>

Menghitung jumlah hari pemesanan.

    SELECT CalculateBookingDuration('2025-06-15', '2025-06-17'); -- Hasil: 2

CalculateBookingTotalPrice(room_id, start_date, end_date)<br>

Mengalikan harga per hari dengan durasi, berdasarkan data indekos.<br>

    SELECT CalculateBookingTotalPrice(1, '2025-06-15', '2025-06-17'); -- Hasil: harga * 2 
    
calculateTotalPrice(room_id, start_date, end_date)<br>

Fungsi alternatif sederhana dengan perhitungan internal.



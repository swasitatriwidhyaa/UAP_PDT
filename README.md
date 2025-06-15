# UAP_PDT - siSRI
**siSRI (Sistem Rekomendasi Indekos)** adalah sebuah platform yang dikembangkan dengan PHP dan MySQL untuk membantu pengguna menemukan indekos ideal mereka. Sistem ini dirancang untuk memberikan rekomendasi yang akurat dan relevan dengan memanfaatkan fitur canggih seperti stored procedure untuk efisiensi pencarian, trigger untuk otomatisasi, transaction untuk memastikan konsistensi data, stored function untuk perhitungan spesifik, serta mekanisme backup otomatis untuk menjaga keamanan data indekos dan pengguna dari hal yang tidak diinginkan.

<img src="assets/img/siSri.png" alt="Tampilan Web" width="1000">

**DETAIL KONSEP**

**-- STORED PROCEDURE --**

Stored procedure adalah instruksi yang disimpan di database untuk mengeksekusi operasi penting secara otomatis. Dalam sistem terdistribusi, ia menjamin efisiensi, konsistensi, dan keamanan eksekusi antar node dan pengguna.


<img src="assets/img/procedure.png" alt="Procedure" width="600">

Beberapa contoh precedure yang digunakan :
bookRoom
Melakukan proses pemesanan dan menghitung total harga secara otomatis. Jika harga tidak ditemukan, transaksi dibatalkan.
CALL bookRoom(room_id, user_id, start_date, end_date, @total_price);



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


<img src="assets/img/procedure.png" alt="Procedure" width="600">




**-- TRANSACTION --**

Implementasi transaction

    START TRANSACTION;
    
    UPDATE bookings
    SET status = 'confirmed'
    WHERE id = booking_id_param;
    
    UPDATE indekos
    SET status_ketersediaan = 'terisi'
    WHERE id = v_room_id;
    
    COMMIT;



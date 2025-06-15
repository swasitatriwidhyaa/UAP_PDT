<?php
// /sisri/models/bookingModel.php
require_once __DIR__ . '/../config/db.php';

class BookingModel {
    private $pdo;
    private $table_name = "bookings";

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo; // Ambil dari global $pdo
    }

    // Menyimpan pemesanan ke database
    public function storeBooking($userId, $roomId, $checkInDate, $checkOutDate, $totalPrice) {
        $query = "INSERT INTO " . $this->table_name . " 
            (user_id, room_id, start_date, end_date, total_price, status, created_at) 
            VALUES (:userId, :roomId, :checkInDate, :checkOutDate, :totalPrice, 'pending', NOW())";
        $stmt = $this->pdo->prepare($query);

        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':roomId', $roomId, PDO::PARAM_INT);
        $stmt->bindParam(':checkInDate', $checkInDate, PDO::PARAM_STR);
        $stmt->bindParam(':checkOutDate', $checkOutDate, PDO::PARAM_STR);
        $stmt->bindParam(':totalPrice', $totalPrice, PDO::PARAM_STR);

        if ($stmt->execute()) {
            return $this->pdo->lastInsertId(); // Mengembalikan ID pemesanan yang baru dibuat
        }
        return false;
    }

    // Mendapatkan 5 pemesanan terbaru
    public function getRecentBookings($limit = 5) {
        $query = "SELECT b.id, u.username, r.nama AS room_name, 
                         b.total_price, b.status, b.created_at 
                  FROM bookings b
                  JOIN users u ON b.user_id = u.id
                  JOIN indekos r ON b.room_id = r.id
                  ORDER BY b.created_at DESC LIMIT :limit";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();  // Mengembalikan data pemesanan terbaru
    }

    // Mengupdate status booking (approve/reject)
    public function updateBookingStatus($bookingId, $status) {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :bookingId";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':status', $status, PDO::PARAM_STR);
        $stmt->bindParam(':bookingId', $bookingId, PDO::PARAM_INT);
        return $stmt->execute();
    }

    // Menghitung total harga berdasarkan harga per bulan (bukan per malam)
    public function calculateTotalPrice($roomId, $checkInDate, $checkOutDate) {
        $query = "SELECT harga FROM indekos WHERE id = :roomId";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':roomId', $roomId, PDO::PARAM_INT);
        $stmt->execute();
        $room = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($room) {
            $hargaPerBulan = $room['harga'];
            $start = new DateTime($checkInDate);
            $end = new DateTime($checkOutDate);
            $interval = $start->diff($end);

            $bulan = ($interval->y * 12) + $interval->m;
            if ($interval->d > 0) $bulan += 1; // kalau ada sisa hari, tetap dihitung 1 bulan

            if ($bulan < 1) $bulan = 1; // Minimal booking 1 bulan
            return $hargaPerBulan * $bulan;
        }
        return 0;
    }
}
?>

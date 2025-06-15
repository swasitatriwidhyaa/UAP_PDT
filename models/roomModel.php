<?php
// /sisri/models/roomModel.php
require_once __DIR__ . '/../config/db.php';

class RoomModel {
    private $pdo;
    private $table_name = "indekos"; // Tabel indekos

    public function __construct() {
        global $pdo;
        $this->pdo = $pdo;
    }

    // Menampilkan semua kosan (dengan filter pencarian)
    public function getAllRooms($searchQuery = '') {
        if ($searchQuery != '') {
            $query = "SELECT * FROM " . $this->table_name . " 
                      WHERE nama LIKE :searchQuery OR lokasi LIKE :searchQuery 
                      ORDER BY created_at DESC";
            $stmt = $this->pdo->prepare($query);
            $searchParam = "%" . $searchQuery . "%";
            $stmt->execute([':searchQuery' => $searchParam]);
        } else {
            $query = "SELECT * FROM " . $this->table_name . " ORDER BY created_at DESC";
            $stmt = $this->pdo->prepare($query);
            $stmt->execute();
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Mendapatkan detail kosan berdasarkan ID
    public function getRoomById($roomId) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = :roomId LIMIT 1";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':roomId', $roomId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTotalRooms() {
        $query = "SELECT COUNT(*) AS total FROM " . $this->table_name;
        $stmt = $this->pdo->prepare($query);
        $stmt->execute();
        return $stmt->fetchColumn();
    }

    public function addRoom($name, $price, $location, $description, $image_path) {
        $query = "INSERT INTO " . $this->table_name . " (nama, harga, lokasi, deskripsi, gambar, created_at, updated_at)
                  VALUES (:name, :price, :location, :description, :image_path, NOW(), NOW())";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':image_path', $image_path);
        return $stmt->execute();
    }

    public function updateRoom($roomId, $name, $price, $location, $description, $image_path) {
        $query = "UPDATE " . $this->table_name . " 
                  SET nama = :name, harga = :price, lokasi = :location, deskripsi = :description, gambar = :image_path, updated_at = NOW()
                  WHERE id = :roomId";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':roomId', $roomId);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':location', $location);
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':image_path', $image_path);
        return $stmt->execute();
    }

    public function deleteRoom($roomId) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = :roomId";
        $stmt = $this->pdo->prepare($query);
        $stmt->bindParam(':roomId', $roomId);
        return $stmt->execute();
    }

    public function checkRoomAvailability($roomId, $checkInDate, $checkOutDate) {
        $query = "SELECT COUNT(*) FROM bookings WHERE room_id = :roomId 
                AND (
                    (start_date BETWEEN :checkIn1 AND :checkOut1) 
                    OR 
                    (end_date BETWEEN :checkIn2 AND :checkOut2)
                )";
        $stmt = $this->pdo->prepare($query);
        $stmt->execute([
            ':roomId' => $roomId,
            ':checkIn1' => $checkInDate,
            ':checkOut1' => $checkOutDate,
            ':checkIn2' => $checkInDate,
            ':checkOut2' => $checkOutDate
        ]);
        $count = $stmt->fetchColumn();
        return $count == 0;
    }

}
?>

<?php
// /sisri/models/reviewModel.php
require_once __DIR__ . '/../config/db.php';

class ReviewModel {
    private $conn;
    private $table_name = "reviews";

    public function __construct() {
        $this->conn = getDbConnection(); // Menggunakan PDO
    }

    // Menambah review baru
    public function addReview($userId, $roomId, $rating, $comment) {
        $query = "INSERT INTO " . $this->table_name . " (user_id, room_id, rating, comment, created_at) 
                  VALUES (:userId, :roomId, :rating, :comment, NOW())";
        $stmt = $this->conn->prepare($query);

        // Binding parameter menggunakan PDO
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->bindParam(':roomId', $roomId, PDO::PARAM_INT);
        $stmt->bindParam(':rating', $rating, PDO::PARAM_INT);
        $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);

        return $stmt->execute(); // Eksekusi query
    }

    // Mendapatkan review berdasarkan ID kamar
    public function getReviewsByRoomId($roomId) {
        $query = "SELECT r.*, u.username FROM " . $this->table_name . " r 
                  JOIN users u ON r.user_id = u.id 
                  WHERE r.room_id = :roomId 
                  ORDER BY r.created_at DESC";
        $stmt = $this->conn->prepare($query);
        
        // Binding parameter menggunakan PDO
        $stmt->bindParam(':roomId', $roomId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Mengambil hasil dengan PDO
    }
}
?>

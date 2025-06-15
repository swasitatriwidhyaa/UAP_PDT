<?php
// /sisri/models/userModel.php
require_once __DIR__ . '/../config/db.php';

class UserModel {
    private $conn;
    private $table_name = "users";

    public function __construct() {
        $this->conn = getDbConnection();
    }

    // Menemukan user berdasarkan username atau email
    public function findUser($identifier) {
        $query = "SELECT id, username, email, password_hash, role FROM " . $this->table_name . " WHERE username = :identifier OR email = :identifier";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':identifier', $identifier, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Membuat user baru
    public function createUser($username, $email, $passwordHash) {
        $query = "INSERT INTO " . $this->table_name . " (username, email, password_hash) VALUES (:username, :email, :passwordHash)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->bindParam(':passwordHash', $passwordHash, PDO::PARAM_STR);
        return $stmt->execute();
    }

    // Mendapatkan user berdasarkan ID
    public function getUserById($userId) {
        $query = "SELECT id, username, email, role FROM " . $this->table_name . " WHERE id = :userId LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Metode baru untuk mendapatkan total pengguna
    public function getTotalUsers() {
        $query = "SELECT COUNT(id) AS total FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }

    // Menutup koneksi database
    public function closeConnection() {
        $this->conn = null;
    }
}
?>

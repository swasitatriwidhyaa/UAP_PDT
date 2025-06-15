<?php
// /sisri/models/transactionModel.php
require_once __DIR__ . '/../config/db.php';

class TransactionModel {
    private $conn;
    private $table_name = "transactions";

    public function __construct() {
        $this->conn = getDbConnection();
    }

    // Menambah transaksi baru
    public function addTransaction($bookingId, $amount, $type, $status, $description = null) {
        $this->conn->beginTransaction();

        try {
            $query = "INSERT INTO " . $this->table_name . " (booking_id, amount, type, status, description, created_at) 
                      VALUES (:bookingId, :amount, :type, :status, :description, NOW())";
            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(':bookingId', $bookingId, PDO::PARAM_INT);
            $stmt->bindParam(':amount', $amount, PDO::PARAM_STR);
            $stmt->bindParam(':type', $type, PDO::PARAM_STR);
            $stmt->bindParam(':status', $status, PDO::PARAM_STR);
            $stmt->bindParam(':description', $description, PDO::PARAM_STR);

            if (!$stmt->execute()) {
                throw new Exception("Failed to add transaction.");
            }

            $transactionId = $this->conn->lastInsertId(); // Mengambil ID transaksi terakhir
            $this->conn->commit();
            return $transactionId;
        } catch (Exception $e) {
            $this->conn->rollBack(); // Rollback jika terjadi kesalahan
            return false;
        }
    }

    // Mendapatkan transaksi berdasarkan booking_id
    public function getTransactionsByBookingId($bookingId) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE booking_id = :bookingId ORDER BY created_at DESC";
        $stmt = $this->conn->prepare($query);
        
        $stmt->bindParam(':bookingId', $bookingId, PDO::PARAM_INT); // Bind parameter
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // Mengambil semua transaksi
    }

    // Fungsi untuk backup database
    public function backupDatabase() {
        $backupFile = "/path/to/backup/sisri_db_" . date("Y-m-d_H-i-s") . ".sql";
        $command = "mysqldump -u root -pYourPassword sisri_db > $backupFile"; // Gantilah 'YourPassword' dengan password MySQL Anda
        system($command);
    }
}
?>

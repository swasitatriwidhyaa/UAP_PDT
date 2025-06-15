<?php
// /sisri/controllers/transactionController.php
require_once __DIR__ . '/../models/transactionModel.php';

class TransactionController {
    private $transactionModel;

    public function __construct() {
        $this->transactionModel = new TransactionModel();
    }

    // Menambah transaksi baru
    public function create() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $bookingId = $_POST['booking_id'];
            $amount = $_POST['amount'];
            $type = $_POST['type'];
            $status = $_POST['status'];
            $description = $_POST['description'] ?? null;

            $transactionId = $this->transactionModel->addTransaction($bookingId, $amount, $type, $status, $description);

            if ($transactionId) {
                setFlashMessage('Transaksi berhasil ditambahkan.', 'success');
                redirect("/sisri/views/transaction/list.php");
            } else {
                setFlashMessage('Terjadi kesalahan dalam proses transaksi.', 'error');
                redirect("/sisri/views/transaction/create.php");
            }
        } else {
            redirect("/sisri/views/transaction/create.php");
        }
    }

    // Menampilkan daftar transaksi
    public function list() {
        if (isset($_GET['booking_id'])) {
            $transactions = $this->transactionModel->getTransactionsByBookingId($_GET['booking_id']);
            include __DIR__ . '/../views/transaction/list.php';
        } else {
            setFlashMessage('Booking ID tidak ditemukan.', 'error');
            redirect("/sisri/views/transaction/list.php");
        }
    }

    // Melakukan backup database
    public function backup() {
        try {
            $this->transactionModel->backupDatabase();
            setFlashMessage('Backup database berhasil.', 'success');
        } catch (Exception $e) {
            setFlashMessage('Terjadi kesalahan saat melakukan backup: ' . $e->getMessage(), 'error');
        }
        redirect("/sisri/views/transaction/list.php");
    }
}
?>

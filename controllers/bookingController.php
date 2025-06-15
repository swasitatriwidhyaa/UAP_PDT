<?php
// /sisri/controllers/bookingController.php
require_once __DIR__ . '/../models/bookingModel.php';
require_once __DIR__ . '/../models/roomModel.php';
require_once __DIR__ . '/../config/session.php';

class BookingController {
    private $bookingModel;
    private $roomModel;

    public function __construct() {
        $this->bookingModel = new BookingModel();
        $this->roomModel = new RoomModel();
    }

    // Menampilkan form booking untuk indekos tertentu
    public function create($roomId) {
        if (!isLoggedIn()) {
            setFlashMessage('Anda harus login untuk membuat pemesanan.', 'warning');
            redirect('/sisri/login.php');
        }

        // Mengambil data kamar berdasarkan roomId
        $room = $this->roomModel->getRoomById($roomId);
        if (!$room) {
            setFlashMessage('Indekos tidak ditemukan.', 'error');
            redirect('/sisri/rooms');
        }

        // Menyiapkan title halaman dan memuat tampilan
        $pageTitle = "Pesan Indekos - siSRI";
        include __DIR__ . '/../includes/header.php'; // Memasukkan header
        include __DIR__ . '/../../views/booking/create.php'; // Memuat form booking
    }

    // Fungsi untuk menyimpan pemesanan
    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            // Ambil data dari form
            $roomId = $_POST['room_id'];
            $userId = $_SESSION['user_id']; // Menggunakan user_id dari sesi
            $checkInDate = $_POST['check_in_date'];
            $checkOutDate = $_POST['check_out_date'];

            // Validasi data (tanggal check-out harus lebih besar dari tanggal check-in)
            if ($checkInDate >= $checkOutDate) {
                setFlashMessage('Tanggal check-out harus lebih besar dari tanggal check-in.', 'error');
                redirect("/sisri/views/booking/create.php?id=$roomId");
            }

            // Memeriksa ketersediaan kamar untuk periode tersebut
            $roomAvailability = $this->roomModel->checkRoomAvailability($roomId, $checkInDate, $checkOutDate);
            if (!$roomAvailability) {
                setFlashMessage('Kamar ini sudah dipesan untuk tanggal tersebut.', 'error');
                redirect("/sisri/views/booking/create.php?id=$roomId");
            }

            // Memanggil prosedur untuk menghitung harga total
            $totalPrice = $this->bookingModel->calculateTotalPrice($roomId, $checkInDate, $checkOutDate);

            if ($totalPrice <= 0) {
                setFlashMessage('Terjadi kesalahan dalam perhitungan harga.', 'error');
                redirect("/sisri/views/booking/create.php?id=$roomId");
            }

            // Masukkan data pemesanan ke database
            $bookingId = $this->bookingModel->storeBooking($userId, $roomId, $checkInDate, $checkOutDate, $totalPrice);
            if ($bookingId) {
                setFlashMessage('Pemesanan berhasil!', 'success');
                redirect("/sisri/views/booking/booking_list.php");
            } else {
                setFlashMessage('Pemesanan gagal, coba lagi.', 'error');
                redirect("/sisri/views/booking/create.php?id=$roomId");
            }
        }
    }
}
?>

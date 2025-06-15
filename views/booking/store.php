<?php
// /sisri/booking/store.php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../models/bookingModel.php';
require_once __DIR__ . '/../../models/roomModel.php';

if (!isLoggedIn()) {
    setFlashMessage('Anda harus login untuk memesan indekos.', 'warning');
    redirect('/sisri/login.php');
}

$bookingModel = new BookingModel();
$roomModel = new RoomModel();

// Pastikan data dikirimkan menggunakan POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $roomId = $_POST['room_id'];
    $userId = $_POST['user_id'];
    $checkInDate = $_POST['check_in_date'];
    $checkOutDate = $_POST['check_out_date'];

    // Validasi tanggal (check-out harus lebih besar dari check-in)
    if ($checkInDate >= $checkOutDate) {
        setFlashMessage('Tanggal check-out harus lebih besar dari tanggal check-in.', 'error');
        redirect("/sisri/views/booking/create.php?id=$roomId");
    }

    // Memeriksa ketersediaan kamar untuk periode tersebut
    $roomAvailability = $roomModel->checkRoomAvailability($roomId, $checkInDate, $checkOutDate);
    if (!$roomAvailability) {
        setFlashMessage('Kamar ini sudah dipesan untuk tanggal tersebut.', 'error');
        redirect("/sisri/views/booking/create.php?id=$roomId");
    }

    // Menghitung total harga berdasarkan harga per bulan
    $totalPrice = $bookingModel->calculateTotalPrice($roomId, $checkInDate, $checkOutDate);

    if ($totalPrice <= 0) {
        setFlashMessage('Terjadi kesalahan dalam perhitungan harga.', 'error');
        redirect("/sisri/views/booking/create.php?id=$roomId");
    }

    // Menyimpan data pemesanan ke database
    $bookingId = $bookingModel->storeBooking($userId, $roomId, $checkInDate, $checkOutDate, $totalPrice);
    if ($bookingId) {
        setFlashMessage('Pemesanan berhasil!', 'success');
        redirect("/sisri/views/booking/booking_list.php");
    } else {
        setFlashMessage('Pemesanan gagal, coba lagi.', 'error');
        redirect("/sisri/views/booking/create.php?id=$roomId");
    }
}
?>

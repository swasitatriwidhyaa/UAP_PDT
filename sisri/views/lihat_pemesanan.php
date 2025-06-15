<?php
// /views/lihat_pemesanan.php
require_once __DIR__ . '/../config/session.php'; // Memastikan session aktif
require_once __DIR__ . '/../config/db.php'; // Koneksi ke database

// Cek apakah pengguna sudah login
if (!isLoggedIn()) {
    redirect('/sisri/login.php');
}

$pageTitle = "Lihat Pemesanan - siSRI"; // Judul halaman
require_once __DIR__ . '/../includes/header.php'; // Memasukkan header

// Ambil user_id dari session
$user_id = $_SESSION['user_id'];

// Ambil ID pemesanan dari URL (misalnya ?id=1)
$reservation_id = $_GET['id'] ?? null;

// Validasi ID pemesanan
if ($reservation_id === null || !is_numeric($reservation_id)) {
    setFlashMessage('ID pemesanan tidak valid.', 'error');
    redirect('/sisri/views/riwayat_pemesanan.php'); // Redirect ke riwayat pemesanan
}

// Query untuk mengambil detail pemesanan berdasarkan id dan user_id
$query = "SELECT r.id, f.name AS room_name, r.check_in_date, r.check_out_date, r.status, r.created_at, r.updated_at
          FROM reservations r
          JOIN rooms f ON r.room_id = f.id
          WHERE r.user_id = :user_id AND r.id = :reservation_id";

$stmt = $conn->prepare($query);
$stmt->execute([':user_id' => $user_id, ':reservation_id' => $reservation_id]);

// Ambil hasil query
$reservation = $stmt->fetch();

// Cek apakah pemesanan ditemukan
if (!$reservation) {
    setFlashMessage('Pemesanan tidak ditemukan.', 'error');
    redirect('/sisri/views/riwayat_pemesanan.php'); // Redirect ke riwayat pemesanan
}
?>

<div class="container py-5">
    <h1 class="display-5 fw-bold text-primary">Detail Pemesanan Anda</h1>
    <p class="col-md-8 fs-4 text-muted">Berikut adalah detail pemesanan yang telah Anda buat.</p>
    <hr class="my-4">

    <!-- Tampilkan detail pemesanan -->
    <div class="mb-3">
        <label class="form-label">Nama Kamar</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($reservation['room_name']) ?>" readonly>
    </div>
    <div class="mb-3">
        <label class="form-label">Tanggal Check-In</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($reservation['check_in_date']) ?>" readonly>
    </div>
    <div class="mb-3">
        <label class="form-label">Tanggal Check-Out</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($reservation['check_out_date']) ?>" readonly>
    </div>
    <div class="mb-3">
        <label class="form-label">Status</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($reservation['status']) ?>" readonly>
    </div>
    <div class="mb-3">
        <label class="form-label">Dibuat Pada</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($reservation['created_at']) ?>" readonly>
    </div>
    <div class="mb-3">
        <label class="form-label">Terakhir Diperbarui</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($reservation['updated_at']) ?>" readonly>
    </div>

    <div class="text-center mt-4">
        <a href="/sisri/views/riwayat_pemesanan.php" class="btn btn-primary">Kembali ke Riwayat Pemesanan</a>
    </div>
</div>

<?php
// Menutup koneksi
require_once __DIR__ . '/../includes/footer.php'; // Memasukkan footer
?>

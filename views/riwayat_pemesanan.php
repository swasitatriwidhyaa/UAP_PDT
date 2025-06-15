<?php
// /views/riwayat_pemesanan.php
require_once __DIR__ . '/../config/session.php'; // Memastikan session aktif
require_once __DIR__ . '/../config/db.php'; // Koneksi ke database
require_once __DIR__ . '/../includes/header.php'; // Memasukkan header

// Cek apakah pengguna sudah login
if (!isLoggedIn()) {
    redirect('/sisri/login.php');
}

$pageTitle = "Riwayat Pemesanan - siSRI"; // Judul halaman

// Ambil user_id dari session
$user_id = $_SESSION['user_id'];

// Mendapatkan koneksi PDO dari db.php
$conn = getDbConnection();

// Query untuk mengambil riwayat pemesanan berdasarkan user_id
$query = "SELECT r.id, f.name AS room_name, r.check_in_date, r.check_out_date, r.status
          FROM reservations r
          JOIN rooms f ON r.room_id = f.id
          WHERE r.user_id = :user_id"; // Mengambil data pemesanan berdasarkan user_id

$stmt = $conn->prepare($query);
$stmt->execute([':user_id' => $user_id]); // Menggunakan user_id yang disimpan di session

// Ambil hasil query
$reservations = $stmt->fetchAll();

// Cek jika ada data pemesanan
if ($reservations):
?>

<div class="container py-5">
    <h1 class="display-5 fw-bold text-primary">Riwayat Pemesanan Anda</h1>
    <p class="col-md-8 fs-4 text-muted">Lihat pemesanan yang telah Anda lakukan.</p>
    <hr class="my-4">
    
    <!-- Tampilkan pesan jika tidak ada pemesanan -->
    <?php if (empty($reservations)): ?>
        <div class="alert alert-warning" role="alert">
            Anda belum memiliki pemesanan.
        </div>
    <?php else: ?>
        <!-- Tabel untuk menampilkan riwayat pemesanan -->
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kamar</th>
                    <th>Tanggal Check-In</th>
                    <th>Tanggal Check-Out</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reservations as $index => $reservation): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($reservation['room_name']) ?></td>
                        <td><?= htmlspecialchars($reservation['check_in_date']) ?></td>
                        <td><?= htmlspecialchars($reservation['check_out_date']) ?></td>
                        <td><?= htmlspecialchars($reservation['status']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <div class="text-center mt-4">
        <a href="/sisri/views/dashboard.php" class="btn btn-primary">Kembali ke Dashboard</a>
    </div>
</div>

<?php
// Tidak perlu menutup koneksi atau statement secara eksplisit
// Statement dan koneksi PDO akan ditutup secara otomatis pada akhir skrip
endif;

require_once __DIR__ . '/../includes/footer.php'; // Memasukkan footer
?>

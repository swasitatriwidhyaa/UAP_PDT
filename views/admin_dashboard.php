<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    redirect('/sisri/login.php');
}

$pageTitle = "Dashboard Admin - siSRI";
require_once __DIR__ . '/../includes/header.php';

$message = getFlashMessage();

$query_users = "SELECT COUNT(*) AS user_count FROM users";
$query_indekos = "SELECT COUNT(*) AS indekos_count FROM indekos";
$query_pemesanan = "SELECT COUNT(*) AS pemesanan_count FROM bookings WHERE status = 'pending'";

// Statistik
$stmt_users = $pdo->prepare($query_users);
$stmt_indekos = $pdo->prepare($query_indekos);
$stmt_pemesanan = $pdo->prepare($query_pemesanan);

$stmt_users->execute();
$stmt_indekos->execute();
$stmt_pemesanan->execute();

$users_count = $stmt_users->fetch()['user_count'];
$indekos_count = $stmt_indekos->fetch()['indekos_count'];
$pemesanan_count = $stmt_pemesanan->fetch()['pemesanan_count'];

// Pemesanan Terbaru
$query_recent_bookings = "
    SELECT b.id, u.username, r.nama AS room_name, 
           b.total_price, 
           b.status, b.created_at 
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN indekos r ON b.room_id = r.id
    ORDER BY b.created_at DESC LIMIT 5";
$stmt_recent_bookings = $pdo->prepare($query_recent_bookings);
$stmt_recent_bookings->execute();
$recentBookings = $stmt_recent_bookings->fetchAll();
?>


<style>
    /* Hero Section untuk Dashboard Admin */
    .admin-hero-section {
        background: url('/sisri/assets/img/kos2.jpg') no-repeat center center;
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        position: relative;
        color: white;
        padding: 6rem 0;
        border-radius: 1rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        animation: fadeIn 1s ease-out;
        text-align: center;
        overflow: hidden;
    }
    .admin-hero-section::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 0;
    }
    .admin-hero-section .container-fluid {
        position: relative;
        z-index: 1;
    }
    .admin-hero-section h1 {
        font-weight: 700;
        font-size: 3.5rem;
        margin-bottom: 1rem;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
    }
    .admin-hero-section p {
        font-size: 1.25rem;
        opacity: 0.9;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
    }
    .admin-hero-section .btn {
        font-weight: 600;
        letter-spacing: 0.5px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        transition: transform 0.2s ease;
    }
    .admin-hero-section .btn:hover {
        transform: translateY(-3px);
    }
    .admin-stat-card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 0.25rem 0.75rem rgba(0, 0, 0, 0.08);
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        background-color: #ffffff;
    }
    .admin-stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.15);
    }
</style>

<div class="container py-4">
    <?php if ($message): ?>
        <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Hero Section -->
    <div class="admin-hero-section mb-5">
        <div class="container-fluid py-5">
            <h1 class="display-5 fw-bold">Selamat Datang, Admin <?= htmlspecialchars($_SESSION['username'] ?? 'Anda') ?>!</h1>
            <p class="col-md-8 mx-auto fs-4">Ini adalah panel kontrol utama Anda. Pantau statistik, kelola indekos, pengguna, dan pemesanan.</p>
            <hr class="my-4 border-white opacity-50">
            <div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
                <a class="btn btn-light btn-lg px-4" href="/sisri/views/manage_rooms.php" role="button"><i class="bi bi-building-fill me-2"></i> Kelola Indekos</a>
                <a class="btn btn-outline-light btn-lg px-4" href="/sisri/views/rooms/kelola_pengguna.php" role="button"><i class="bi bi-people-fill me-2"></i> Kelola Pengguna</a>
                <a class="btn btn-outline-light btn-lg px-4" href="#" role="button"><i class="bi bi-gear-fill me-2"></i> Pengaturan Sistem</a>
            </div>
        </div>
    </div>

    <!-- Statistik Umum -->
    <h2 class="mb-4 fw-bold text-success">Statistik Umum</h2>

    <div class="row row-cols-1 row-cols-md-3 g-4 mb-4">
        <div class="col">
            <div class="card h-100 p-3 bg-primary text-white admin-stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Total Pengguna</h5>
                            <h2 class="display-4 fw-bold"><?= htmlspecialchars($users_count) ?></h2>
                        </div>
                        <i class="bi bi-people-fill display-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 p-3 bg-info text-white admin-stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Total Indekos</h5>
                            <h2 class="display-4 fw-bold"><?= htmlspecialchars($indekos_count) ?></h2>
                        </div>
                        <i class="bi bi-building-fill display-4"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card h-100 p-3 bg-warning text-white admin-stat-card"> 
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="card-title mb-0">Pemesanan Pending</h5>
                            <h2 class="display-4 fw-bold"><?= htmlspecialchars($pemesanan_count) ?></h2>
                        </div>
                        <i class="bi bi-clock-fill display-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pemesanan Terbaru -->
    <div class="card shadow-sm p-4 mb-4 admin-stat-card">
        <div class="card-body">
            <h4 class="card-title fw-bold text-success mb-3">Pemesanan Terbaru</h4>
            <?php if (!empty($recentBookings)): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID Booking</th>
                                <th>Pengguna</th>
                                <th>Indekos</th>
                                <th>Total Harga</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentBookings as $booking): ?>
                                <tr>
                                    <td><?= htmlspecialchars($booking['id']) ?></td>
                                    <td><?= htmlspecialchars($booking['username']) ?></td>
                                    <td><?= htmlspecialchars($booking['room_name']) ?></td>
                                    <td>Rp <?= number_format($booking['total_price'], 0, ',', '.') ?></td>
                                    <td><span class="badge bg-<?= ($booking['status'] == 'pending') ? 'warning' : (($booking['status'] == 'confirmed') ? 'success' : 'danger') ?>"><?= htmlspecialchars($booking['status']) ?></span></td>
                                    <td><?= date('d M Y', strtotime($booking['created_at'])) ?></td>
                                    <td>
                                        <a href="/sisri/views/rooms/view_booking.php?id=<?= htmlspecialchars($booking['id']) ?>" class="btn btn-sm btn-outline-primary">Lihat Detail</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p class="text-muted text-center">Tidak ada pemesanan terbaru.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php'; // Memasukkan footer
?>

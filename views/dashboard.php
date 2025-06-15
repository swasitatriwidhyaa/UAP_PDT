<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php';

if (!isLoggedIn()) {
    redirect('/sisri/login.php');
}

$pageTitle = "Dashboard - siSRI";
require_once __DIR__ . '/../includes/header.php';

$searchQuery = $_GET['search'] ?? '';

// Fungsi mencari indekos (rekomendasi atau berdasarkan pencarian)
if ($searchQuery != '') {
    $query = "SELECT * FROM indekos WHERE nama LIKE :searchQuery OR lokasi LIKE :searchQuery ORDER BY created_at DESC LIMIT 3";
    $stmt = $pdo->prepare($query);
    $searchParam = "%" . $searchQuery . "%";
    $stmt->execute([':searchQuery' => $searchParam]);
    $indekos = $stmt->fetchAll();
} else {
    $query = "SELECT * FROM indekos ORDER BY created_at DESC LIMIT 3";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $indekos = $stmt->fetchAll();
}

// Ambil semua booking milik user (status & tanggal)
$userId = $_SESSION['user_id'];
$queryBookings = "SELECT room_id, status, start_date, end_date FROM bookings WHERE user_id = :user_id";
$stmtBookings = $pdo->prepare($queryBookings);
$stmtBookings->execute([':user_id' => $userId]);
$userBookings = $stmtBookings->fetchAll(PDO::FETCH_ASSOC);

// Buat array lookup [room_id => array(status, start_date, end_date)]
$bookingStatusMap = [];
foreach ($userBookings as $booking) {
    $bookingStatusMap[$booking['room_id']] = [
        'status'     => $booking['status'],
        'start_date' => $booking['start_date'],
        'end_date'   => $booking['end_date'],
    ];
}
?>

<div class="p-5 mb-4 bg-white rounded-3 shadow-sm">
    <div class="container-fluid py-5">
        <h1 class="display-5 fw-bold text-success">Selamat Datang, <?= htmlspecialchars($_SESSION['username'] ?? 'Pengguna') ?>!</h1>
        <p class="col-md-8 fs-4 text-muted">Di sini Anda dapat mengelola profil Anda, melihat daftar indekos yang direkomendasikan, dan melacak pemesanan Anda.</p>
        <hr class="my-4">
        <div class="d-grid gap-2 d-md-flex justify-content-md-start">
            <a class="btn btn-primary btn-lg px-4" href="/sisri/views/cari_indekos.php" role="button"><i class="bi bi-search me-2"></i> Cari Indekos</a>
            <a class="btn btn-outline-secondary btn-lg px-4" href="/sisri/views/booking/booking_list.php" role="button"><i class="bi bi-journal-text me-2"></i> Lihat Pemesanan</a>
            <a class="btn btn-outline-info btn-lg px-4" href="/sisri/views/kelola_profil.php" role="button"><i class="bi bi-person-circle me-2"></i> Kelola Profil</a>
        </div>
    </div>
</div>

<!-- Menampilkan Rekomendasi Indekos -->
<div class="container py-5">
    <h2 class="display-6 fw-bold text-primary">Rekomendasi Indekos</h2>
    <p class="col-md-8 fs-4 text-muted">Berikut adalah beberapa rekomendasi indekos yang mungkin sesuai dengan preferensi Anda.</p>
    <hr class="my-4">

    <!-- Form Pencarian -->
    <form class="d-flex mb-4" action="/sisri/views/dashboard.php" method="GET">
        <input class="form-control me-2" type="search" placeholder="Cari Indekos" name="search" value="<?= htmlspecialchars($searchQuery) ?>" aria-label="Search">
        <button class="btn btn-outline-success" type="submit">Cari</button>
    </form>

    <!-- Menampilkan Data Indekos -->
    <div class="row">
        <?php if (empty($indekos)): ?>
            <div class="alert alert-warning" role="alert">
                Tidak ada rekomendasi indekos ditemukan.
            </div>
        <?php else: ?>
            <?php foreach ($indekos as $indekos_item): ?>
                <div class="col-md-4 mb-3">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title"><?= htmlspecialchars($indekos_item['nama']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($indekos_item['lokasi']) ?></p>
                            <p class="card-text">Harga: Rp <?= number_format($indekos_item['harga'], 0, ',', '.') ?> per bulan</p>
                            <p class="card-text"><?= htmlspecialchars($indekos_item['deskripsi']) ?></p>

                            <?php if ($indekos_item['gambar']): ?>
                                <img src="/sisri/uploads/<?= htmlspecialchars($indekos_item['gambar']) ?>" alt="Gambar Indekos" class="img-fluid mb-3">
                            <?php endif; ?>

                            <!-- STATUS & KETERANGAN BOOKING -->
                            <?php
                            $statusBooking = $bookingStatusMap[$indekos_item['id']] ?? null;
                            if ($statusBooking):
                                $statusPesan = $statusBooking['status'];
                                $badge = 'secondary';
                                $statusTampil = 'Menunggu Persetujuan';
                                if ($statusPesan == 'confirmed') {
                                    $badge = 'success'; $statusTampil = 'Dipesan';
                                } elseif ($statusPesan == 'pending') {
                                    $badge = 'warning'; $statusTampil = 'Menunggu Persetujuan';
                                } elseif ($statusPesan == 'rejected') {
                                    $badge = 'danger'; $statusTampil = 'Ditolak';
                                }
                                $tglDari = $statusBooking['start_date'];
                                $tglSampai = $statusBooking['end_date'];
                            ?>
                                <p>
                                    <span class="badge bg-<?= $badge ?>"><?= $statusTampil ?></span><br>
                                    <small class="text-muted">
                                        <?= $statusTampil ?> dari <?= date('d-m-Y', strtotime($tglDari)) ?> sampai <?= date('d-m-Y', strtotime($tglSampai)) ?>
                                    </small>
                                </p>
                            <?php endif; ?>

                            <a href="/sisri/views/detail.php?id=<?= $indekos_item['id'] ?>" class="btn btn-primary">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>

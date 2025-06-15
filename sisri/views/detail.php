<?php
require_once __DIR__ . '/../config/session.php'; // Memastikan session aktif
require_once __DIR__ . '/../config/db.php';     // Mendapatkan $pdo (PDO object)

// Cek parameter ID di URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $roomId = $_GET['id'];
    $query = "SELECT * FROM indekos WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':id' => $roomId]);
    $room = $stmt->fetch();

    if (!$room) {
        setFlashMessage('Indekos tidak ditemukan.', 'error');
        redirect('/sisri/views/rekomendasi_indekos.php');
    }
} else {
    setFlashMessage('ID indekos tidak ditemukan.', 'error');
    redirect('/sisri/views/rekomendasi_indekos.php');
}

$pageTitle = "Buat Pemesanan - siSRI";
require_once __DIR__ . '/../includes/header.php';
$message = getFlashMessage();

// Ambil status booking user pada indekos ini (jika login & bukan admin)
$userBooking = null;
if (isLoggedIn() && $_SESSION['role'] !== 'admin') {
    $userId = $_SESSION['user_id'];
    $stmtBook = $pdo->prepare("SELECT status, start_date, end_date FROM bookings WHERE user_id = :user_id AND room_id = :room_id ORDER BY created_at DESC LIMIT 1");
    $stmtBook->execute([':user_id' => $userId, ':room_id' => $roomId]);
    $userBooking = $stmtBook->fetch(PDO::FETCH_ASSOC);
}

$averageRating = null; // Placeholder, implementasi rating sesuai kebutuhan
$reviews = [];
?>

<div class="container py-4">
    <?php if ($message): ?>
        <div class="alert alert-<?= htmlspecialchars($message['type']) ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <h1 class="mb-3 fw-bold text-success"><?= htmlspecialchars($room['nama'] ?? 'Nama Indekos') ?></h1>
            <p class="text-muted"><i class="bi bi-geo-alt-fill me-1"></i> <?= htmlspecialchars($room['lokasi'] ?? 'Lokasi tidak tersedia') ?></p>

            <!-- Foto Indekos -->
            <?php if (isset($room['gambar']) && !empty($room['gambar'])): ?>
                <img src="/sisri/uploads/<?= htmlspecialchars($room['gambar']) ?>" class="img-fluid rounded shadow-sm mb-4" alt="<?= htmlspecialchars($room['nama']) ?>" style="max-height: 450px; object-fit: cover; width: 100%;">
            <?php else: ?>
                <img src="/sisri/assets/img/kos.jpg" class="img-fluid rounded shadow-sm mb-4" alt="Default Room Image" style="max-height: 400px; object-fit: cover; width: 100%;">
            <?php endif; ?>

            <h3 class="fw-bold text-primary mb-3">Deskripsi Indekos</h3>
            <p class="text-muted"><?= nl2br(htmlspecialchars($room['deskripsi'] ?? 'Deskripsi belum tersedia.')) ?></p>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm p-3 mb-4 sticky-top" style="top: 80px;">
                <div class="card-body">
                    <h4 class="card-title fw-bold text-success mb-3">Detail Harga & Pemesanan</h4>
                    <p class="card-text fs-5">Harga:
                        <span class="fw-bold text-primary">
                            <?= "Rp " . number_format($room['harga'] ?? 0, 0, ',', '.') ?> / bulan
                        </span>
                    </p>
                    <hr>

                    <!-- Tambahkan status booking user di sini jika ada -->
                    <?php if ($userBooking): ?>
                        <?php
                        $statusPesan = $userBooking['status'];
                        $badge = 'secondary';
                        $statusTampil = 'Menunggu Persetujuan';
                        if ($statusPesan == 'confirmed') {
                            $badge = 'success'; $statusTampil = 'Dipesan';
                        } elseif ($statusPesan == 'pending') {
                            $badge = 'warning'; $statusTampil = 'Menunggu Persetujuan';
                        } elseif ($statusPesan == 'rejected') {
                            $badge = 'danger'; $statusTampil = 'Ditolak';
                        }
                        $tglDari = $userBooking['start_date'];
                        $tglSampai = $userBooking['end_date'];
                        ?>
                        <p>
                            <span class="badge bg-<?= $badge ?>"><?= $statusTampil ?></span><br>
                            <small class="text-muted">
                                <?= $statusTampil ?> dari <?= date('d-m-Y', strtotime($tglDari)) ?> sampai <?= date('d-m-Y', strtotime($tglSampai)) ?>
                            </small>
                        </p>
                        <hr>
                    <?php endif; ?>

                    <p class="mb-2">Rating Rata-rata:
                        <?php if (isset($averageRating) && $averageRating !== null): ?>
                            <span class="badge bg-warning text-dark fs-6"><i class="bi bi-star-fill me-1"></i> <?= number_format($averageRating, 1) ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Belum ada rating</span>
                        <?php endif; ?>
                    </p>

                    <p class="mb-4">Total Ulasan: <span class="fw-bold"><?= count($reviews ?? []) ?></span></p>

                    <?php if (isLoggedIn() && $_SESSION['role'] !== 'admin'): ?>
                        <a href="#booking-form" class="btn btn-success d-block mb-2"><i class="bi bi-calendar-check me-2"></i> Pesan Sekarang</a>
                        <a href="/sisri/review/create/<?= htmlspecialchars($room['id'] ?? '') ?>" class="btn btn-outline-success d-block"><i class="bi bi-star me-2"></i> Beri Ulasan</a>
                    <?php elseif (!isLoggedIn()): ?>
                        <a href="/sisri/login.php" class="btn btn-success d-block mb-2">Login untuk Memesan</a>
                        <a href="/sisri/register.php" class="btn btn-outline-success d-block">Daftar untuk Ulasan</a>
                    <?php endif; ?>

                    <?php if (isLoggedIn() && $_SESSION['role'] === 'admin'): ?>
                        <hr>
                        <h5 class="fw-bold text-primary">Aksi Admin</h5>
                        <a href="/sisri/admin/edit_room/<?= htmlspecialchars($room['id'] ?? '') ?>" class="btn btn-warning btn-sm d-block mb-2"><i class="bi bi-pencil-square me-1"></i> Edit Indekos</a>
                        <a href="/sisri/admin/delete_room/<?= htmlspecialchars($room['id'] ?? '') ?>" class="btn btn-danger btn-sm d-block" onclick="return confirm('Anda yakin ingin menghapus indekos ini?');"><i class="bi bi-trash me-1"></i> Hapus Indekos</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Pemesanan Indekos -->
    <div class="mt-5" id="booking-form">
        <h3 class="fw-bold text-success mb-3">Form Pemesanan</h3>
        <form action="/sisri/views/booking/store.php" method="POST">
            <input type="hidden" name="room_id" value="<?= htmlspecialchars($room['id']) ?>">
            <input type="hidden" name="user_id" value="<?= htmlspecialchars($_SESSION['user_id']) ?>">

            <div class="mb-3">
                <label for="check_in_date" class="form-label">Tanggal Check-In</label>
                <input type="date" class="form-control" id="check_in_date" name="check_in_date" required>
            </div>

            <div class="mb-3">
                <label for="check_out_date" class="form-label">Tanggal Check-Out</label>
                <input type="date" class="form-control" id="check_out_date" name="check_out_date" required>
            </div>

            <div class="mb-4">
                <label for="total_price" class="form-label">Total Harga</label>
                <input type="text" class="form-control" id="total_price" name="total_price" readonly>
            </div>

            <button type="submit" class="btn btn-success btn-lg"><i class="bi bi-calendar-check me-2"></i> Pesan Sekarang</button>
        </form>
    </div>
</div>

<!-- Script untuk kalkulasi harga otomatis -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkIn = document.getElementById('check_in_date');
    const checkOut = document.getElementById('check_out_date');
    const totalPriceInput = document.getElementById('total_price');
    const hargaPerBulan = <?= json_encode($room['harga']); ?>;

    // Fungsi untuk menghitung bulan penuh
    function hitungBulan(start, end) {
        const startDate = new Date(start);
        const endDate = new Date(end);
        if (isNaN(startDate) || isNaN(endDate)) return 0;

        // Menghitung perbedaan bulan
        let bulan = (endDate.getFullYear() - startDate.getFullYear()) * 12 + (endDate.getMonth() - startDate.getMonth());

        // Jika tanggal akhir (check-out) lebih kecil dari tanggal awal (check-in) pada bulan yang sama, kurangi 1 bulan
        if (endDate.getDate() < startDate.getDate()) {
            bulan--;
        }

        // Pastikan bulan minimal 1
        if (bulan < 1) bulan = 1;

        return bulan;
    }

    function updateTotalPrice() {
        const start = checkIn.value;
        const end = checkOut.value;
        if (start && end) {
            const bulan = hitungBulan(start, end); // Hitung bulan penuh
            const total = hargaPerBulan * bulan;
            totalPriceInput.value = 'Rp ' + total.toLocaleString('id-ID');
        } else {
            totalPriceInput.value = '';
        }
    }

    // Event listener untuk menghitung harga setiap kali user memilih tanggal
    checkIn.addEventListener('change', updateTotalPrice);
    checkOut.addEventListener('change', updateTotalPrice);
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
<?php
// /views/rekomendasi_indekos.php
require_once __DIR__ . '/../config/session.php'; // Memastikan session aktif
require_once __DIR__ . '/../config/db.php'; // Koneksi ke database

// Cek apakah pengguna sudah login
if (!isLoggedIn()) {
    redirect('/sisri/login.php');
}

$pageTitle = "Rekomendasi Indekos - siSRI"; // Judul halaman
require_once __DIR__ . '/../includes/header.php'; // Memasukkan header

// Ambil data pencarian dari URL (jika ada)
$searchQuery = $_GET['search'] ?? ''; // Jika tidak ada search, maka kosong

// Debugging: Cek apa yang diterima di parameter pencarian
var_dump($searchQuery); // Untuk memastikan nilai searchQuery

// Pastikan query pencarian dijalankan dengan benar
if ($searchQuery != '') {
    $query = "SELECT * FROM indekos WHERE nama LIKE :searchQuery OR lokasi LIKE :searchQuery"; // Ambil indekos berdasarkan nama atau lokasi
    $stmt = $pdo->prepare($query);
    
    // Menambahkan wildcard pada pencarian
    $searchParam = "%" . $searchQuery . "%";  // Menambahkan wildcard untuk pencarian
    $stmt->execute([':searchQuery' => $searchParam]); // Eksekusi query dengan parameter yang benar
    $indekos = $stmt->fetchAll();
} else {
    // Jika tidak ada query pencarian, ambil semua indekos
    $query = "SELECT * FROM indekos";
    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $indekos = $stmt->fetchAll();
}
?>

<div class="container py-5">
    <h1 class="display-5 fw-bold text-primary">Rekomendasi Indekos</h1>
    <p class="col-md-8 fs-4 text-muted">Berikut adalah beberapa rekomendasi indekos yang sesuai dengan preferensi Anda.</p>
    <hr class="my-4">

    <!-- Form Pencarian -->
    <form class="d-flex mb-4" action="/sisri/views/rekomendasi_indekos.php" method="GET">
        <input class="form-control me-2" type="search" placeholder="Cari Indekos" name="search" value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" aria-label="Search">
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

                            <!-- Menampilkan gambar -->
                            <?php if ($indekos_item['gambar']): ?>
                                <img src="/sisri/uploads/<?= htmlspecialchars($indekos_item['gambar']) ?>" alt="Gambar Indekos" class="img-fluid mb-3">
                            <?php endif; ?>
                            
                            <a href="/sisri/views/rooms/detail.php" class="btn btn-primary">Lihat Detail</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
// Menutup koneksi
require_once __DIR__ . '/../includes/footer.php'; // Memasukkan footer
?>

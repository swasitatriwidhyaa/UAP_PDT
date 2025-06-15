<?php
// /views/cari_indekos.php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php'; // Pastikan db.php terimpor dengan benar

if (!isLoggedIn()) {
    redirect('/sisri/login.php');
}

$pageTitle = "Cari Indekos - siSRI";
require_once __DIR__ . '/../includes/header.php';

// Ambil data pencarian dari form (jika ada)
$location = $_GET['location'] ?? '';
$price = $_GET['price'] ?? 0;

// Query untuk mencari indekos berdasarkan lokasi dan harga
$query = "SELECT * FROM indekos WHERE lokasi LIKE :location AND harga <= :price";
$stmt = $pdo->prepare($query);
$stmt->execute([':location' => "%$location%", ':price' => $price]);
$indekos = $stmt->fetchAll();
?>

<div class="container py-5">
    <h1 class="display-5 fw-bold text-primary">Cari Indekos</h1>
    <p class="col-md-8 fs-4 text-muted">Temukan indekos yang sesuai dengan preferensi dan anggaran Anda.</p>
    <hr class="my-4">
    
    <!-- Form pencarian indekos -->
    <form action="/sisri/cari_indekos.php" method="get">
        <div class="mb-3">
            <label for="location" class="form-label">Lokasi</label>
            <input type="text" class="form-control" id="location" name="location" value="<?= htmlspecialchars($location) ?>" placeholder="Masukkan lokasi">
        </div>
        <div class="mb-3">
            <label for="price" class="form-label">Harga Maksimum</label>
            <input type="number" class="form-control" id="price" name="price" value="<?= htmlspecialchars($price) ?>" placeholder="Masukkan harga maksimum">
        </div>
        <button type="submit" class="btn btn-primary">Cari Indekos</button>
    </form>

    <hr class="my-4">
    
    <!-- Daftar Indekos -->
    <h3>Indekos yang Ditemukan</h3>
    <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php foreach ($indekos as $item) { ?>
        <div class="col">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($item['nama']) ?></h5>
                    <p class="card-text">Lokasi: <?= htmlspecialchars($item['lokasi']) ?></p>
                    <p class="card-text">Harga: Rp. <?= number_format($item['harga'], 0, ',', '.') ?></p>
                    <a href="#" class="btn btn-primary">Lihat Detail</a>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>

<?php
// /sisri/views/rooms/detail.php
$pageTitle = htmlspecialchars($room['nama'] ?? 'Detail Indekos') . " - siSRI";
require_once __DIR__ . '/../../includes/header.php';
$message = getFlashMessage();
?>

<div class="container py-4">
    <?php if ($message): ?>
        <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
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
                            <?php 
                                // Menampilkan harga dengan format Rp dan pemisah ribuan
                                echo "Rp " . number_format($room['harga'] ?? 0, 0, ',', '.');
                            ?> / bulan
                        </span>
                    </p>
                    <hr>

                    <p class="mb-2">Rating Rata-rata:
                        <?php if (isset($averageRating) && $averageRating !== null): ?>
                            <span class="badge bg-warning text-dark fs-6"><i class="bi bi-star-fill me-1"></i> <?= number_format($averageRating, 1) ?></span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Belum ada rating</span>
                        <?php endif; ?>
                    </p>

                    <p class="mb-4">Total Ulasan: <span class="fw-bold"><?= count($reviews ?? []) ?></span></p>

                    <?php if (isLoggedIn() && $_SESSION['role'] !== 'admin'): ?>
                        <!-- Button for booking now -->
                        <a href="/sisri/booking/create/<?= htmlspecialchars($room['id'] ?? '') ?>" class="btn btn-success d-block mb-2"><i class="bi bi-calendar-check me-2"></i> Pesan Sekarang</a>
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

    <div class="mt-5">
        <h3 class="fw-bold text-success mb-3">Ulasan Pengguna (<?= count($reviews ?? []) ?>)</h3>
        <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="card shadow-sm mb-3">
                    <div class="card-body">
                        <h6 class="card-subtitle mb-2 text-primary fw-bold"><?= htmlspecialchars($review['username']) ?></h6>
                        <div class="mb-2">
                            <?php for ($i = 0; $i < $review['rating']; $i++): ?><i class="bi bi-star-fill text-warning"></i><?php endfor; ?>
                            <?php for ($i = 0; $i < (5 - $review['rating']); $i++): ?><i class="bi bi-star text-warning"></i><?php endfor; ?>
                            <small class="text-muted ms-2"><?= date('d M Y', strtotime($review['created_at'])) ?></small>
                        </div>
                        <p class="card-text"><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info text-center" role="alert">
                Belum ada ulasan untuk indekos ini. Jadilah yang pertama!
                <?php if (isLoggedIn() && $_SESSION['role'] !== 'admin'): ?>
                    <hr>
                    <a href="/sisri/review/booking.php<?= htmlspecialchars($room['id'] ?? '') ?>" class="btn btn-outline-info">Beri Ulasan Sekarang</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 

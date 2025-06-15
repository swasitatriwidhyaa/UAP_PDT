<?php
// /sisri/views/rooms/edit_form.php
require_once __DIR__ . '/../../config/session.php';  // Pastikan session aktif
require_once __DIR__ . '/../../config/db.php';  // Pastikan koneksi ke database
require_once __DIR__ . '/../../includes/header.php';  // Pastikan header sudah ada

$message = getFlashMessage();  // Ambil pesan flash jika ada

// Cek apakah ID indekos ada di URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    setFlashMessage('ID indekos tidak ditemukan.', 'error');
    redirect('/sisri/views/rooms/list.php');
}

$id = $_GET['id']; // ID indekos yang akan diedit

// Query untuk mendapatkan data indekos berdasarkan ID
$query = "SELECT * FROM indekos WHERE id = :id";
$stmt = $pdo->prepare($query);
$stmt->execute([':id' => $id]);

// Ambil data indekos
$formData = $stmt->fetch();

// Cek jika indekos tidak ditemukan
if (!$formData) {
    setFlashMessage('Indekos tidak ditemukan.', 'error');
    redirect('/sisri/views/rooms/list.php');
}
?>

<div class="container py-5">
    <div class="card shadow-lg p-4" style="max-width: 800px; margin: auto; border-radius: 1rem;">
        <div class="card-body">
            <h2 class="card-title text-center mb-4 fw-bold text-success">Edit Indekos: <?= htmlspecialchars($formData['nama'] ?? 'ID ' . ($_GET['id'] ?? '')) ?></h2>
            <p class="text-center text-muted mb-4">Perbarui informasi indekos ini.</p>

            <?php if ($message): ?>
                <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="/sisri/views/rooms/update_room.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id" value="<?= htmlspecialchars($formData['id'] ?? '') ?>">
                <input type="hidden" name="current_image" value="<?= htmlspecialchars($formData['gambar'] ?? '') ?>">

                <div class="mb-3">
                    <label for="name" class="form-label">Nama Indekos</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Contoh: Indekos Bahagia Putri" value="<?= htmlspecialchars($formData['nama'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Harga per Bulan (Rp)</label>
                    <input type="number" class="form-control" id="price" name="price" placeholder="Contoh: 750000" min="0" value="<?= htmlspecialchars($formData['harga'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="location" class="form-label">Lokasi</label>
                    <input type="text" class="form-control" id="location" name="location" placeholder="Contoh: Jalan Pahlawan No. 10, Bandar Lampung" value="<?= htmlspecialchars($formData['lokasi'] ?? '') ?>" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description" rows="5" placeholder="Deskripsi lengkap indekos, fasilitas, dan keunggulan." required><?= htmlspecialchars($formData['deskripsi'] ?? '') ?></textarea>
                </div>
                <div class="mb-4">
                    <label for="image" class="form-label">Gambar Utama Indekos (Biarkan kosong jika tidak berubah)</label>
                    <?php if ($formData['gambar']): ?>
                        <div class="mb-2">
                            Gambar Saat Ini: <br><img src="/sisri/uploads/<?= htmlspecialchars($formData['gambar']) ?>" alt="Current Image" style="max-width: 150px; height: auto; border-radius: 5px;">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    <small class="form-text text-muted">Opsional. Format: JPG, PNG. Max ukuran: 2MB.</small>
                </div>
                
                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="/sisri/views/manage_rooms.php" class="btn btn-secondary btn-lg px-4"><i class="bi bi-arrow-left-circle me-2"></i> Kembali</a>
                    <button type="submit" class="btn btn-success btn-lg px-4"><i class="bi bi-save me-2"></i> Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

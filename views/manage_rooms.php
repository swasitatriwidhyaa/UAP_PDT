<?php
// /sisri/views/rooms/manage_rooms.php
// Pastikan session.php di-include dengan jalur yang benar
require_once __DIR__ . '/../config/session.php';  // Pastikan session aktif
require_once __DIR__ . '/../config/db.php';  // Pastikan koneksi ke database
require_once __DIR__ . '/../includes/header.php';  // Memasukkan header

// Cek apakah pengguna sudah login dan apakah role-nya admin
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    redirect('/sisri/login.php');
}

// Ambil pesan flash jika ada
$message = getFlashMessage();

// Query untuk mendapatkan data indekos
$query_indekos = "SELECT * FROM indekos";  // Ambil semua data dari tabel indekos
$stmt_indekos = $pdo->prepare($query_indekos);  // Persiapkan query
$stmt_indekos->execute();  // Eksekusi query

// Ambil hasil query
$rooms = $stmt_indekos->fetchAll();  // Ambil data sebagai array
?>

<div class="container py-4">
    <h2 class="fw-bold text-success mb-4">Kelola Indekos</h2>
    
    <!-- Menampilkan pesan flash jika ada -->
    <?php if ($message): ?>
        <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Tombol untuk menambah indekos baru -->
    <div class="d-grid gap-2 d-md-flex justify-content-md-end mb-4">
        <a href="/sisri/views/rooms/add_form.php" class="btn btn-success btn-lg"><i class="bi bi-plus-circle me-2"></i> Tambah Indekos Baru</a>
    </div>

    <!-- Daftar Indekos -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Indekos</th>
                    <th>Harga</th>
                    <th>Lokasi</th>
                    <th>Deskripsi</th>
                    <th>Gambar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rooms as $index => $room): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($room['nama']) ?></td>
                        <td>Rp <?= number_format($room['harga'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($room['lokasi']) ?></td>
                        <td><?= htmlspecialchars($room['deskripsi']) ?></td>
                        <td>
                            <?php if ($room['gambar']): ?>
                                <img src="/sisri/uploads/<?= htmlspecialchars($room['gambar']) ?>" alt="Gambar Indekos" style="max-width: 100px;">
                            <?php else: ?>
                                <i class="bi bi-image-fill text-muted" style="font-size: 2rem;"></i>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/sisri/views/rooms/edit_form.php?id=<?= $room['id'] ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                            <a href="/sisri/views/rooms/delete.php?id=<?= $room['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Anda yakin ingin menghapus indekos ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

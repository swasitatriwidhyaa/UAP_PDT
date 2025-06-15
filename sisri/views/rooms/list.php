<?php
// /sisri/views/rooms/list.php

// Pastikan session.php di-include dengan jalur yang benar
require_once __DIR__ . '/../../config/session.php';  // Jalur yang benar
require_once __DIR__ . '/../../includes/header.php';  // Jalur yang benar
require_once __DIR__ . '/../../config/db.php';  // Koneksi ke database

// Cek apakah pengguna sudah login dan apakah role-nya admin
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    redirect('/sisri/login.php');
}

$pageTitle = "Kelola Indekos - siSRI";

// Ambil pesan flash jika ada
$message = getFlashMessage();  // Ini memastikan bahwa $message di-set dengan benar

// Query untuk mendapatkan data indekos
$query_indekos = "SELECT * FROM indekos";
$stmt_indekos = $pdo->prepare($query_indekos);
$stmt_indekos->execute();

$indekos_list = $stmt_indekos->fetchAll();
?>

<div class="container py-5">
    <h2 class="fw-bold text-success mb-4">Daftar Indekos</h2>
    
    <!-- Menampilkan pesan flash jika ada -->
    <?php if ($message): ?>
        <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

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
                <?php foreach ($indekos_list as $index => $indekos): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($indekos['nama']) ?></td>
                        <td>Rp <?= number_format($indekos['harga'], 0, ',', '.') ?></td>
                        <td><?= htmlspecialchars($indekos['lokasi']) ?></td>
                        <td><?= htmlspecialchars($indekos['deskripsi']) ?></td>
                        <td>
                            <?php if ($indekos['gambar']): ?>
                                <img src="/sisri/uploads/<?= htmlspecialchars($indekos['gambar']) ?>" alt="Gambar" style="max-width: 100px;">
                            <?php else: ?>
                                <span>Gambar tidak tersedia</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <a href="/sisri/views/rooms/edit_form.php?id=<?= $indekos['id'] ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                            <a href="/sisri/views/rooms/delete.php?id=<?= $indekos['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Anda yakin ingin menghapus indekos ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php'; // Memasukkan footer
?>

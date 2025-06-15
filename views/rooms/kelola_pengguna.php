<?php
// /sisri/views/kelola_pengguna.php
// Pastikan session.php di-include dengan jalur yang benar
require_once __DIR__ . '/../../config/session.php';  // Pastikan session aktif
require_once __DIR__ . '/../../config/db.php';  // Pastikan koneksi ke database
require_once __DIR__ . '/../../includes/header.php';  // Memasukkan header

// Cek apakah pengguna sudah login dan apakah role-nya admin
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    redirect('/sisri/login.php');
}

// Ambil pesan flash jika ada
$message = getFlashMessage();

// Query untuk mendapatkan data pengguna
$query_users = "SELECT * FROM users";  // Ambil semua data dari tabel users
$stmt_users = $pdo->prepare($query_users);  // Persiapkan query
$stmt_users->execute();  // Eksekusi query

// Ambil hasil query
$users = $stmt_users->fetchAll();  // Ambil data sebagai array
?>

<div class="container py-4">
    <h2 class="fw-bold text-success mb-4">Kelola Pengguna</h2>
    
    <!-- Menampilkan pesan flash jika ada -->
    <?php if ($message): ?>
        <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Daftar Pengguna -->
    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Pengguna</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $index => $user): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($user['username']) ?></td>
                        <td><?= htmlspecialchars($user['email']) ?></td>
                        <td><?= htmlspecialchars($user['role']) ?></td>
                        <td>
                            <!-- Edit Pengguna -->
                            <a href="/sisri/views/users/edit_form.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-warning">Edit</a>
                            <!-- Hapus Pengguna -->
                            <a href="/sisri/views/users/delete.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Anda yakin ingin menghapus pengguna ini?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

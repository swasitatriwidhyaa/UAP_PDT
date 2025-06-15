<?php
// /views/kelola_profil.php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/db.php'; // Pastikan koneksi database

if (!isLoggedIn()) {
    redirect('/sisri/login.php');
}

$pageTitle = "Kelola Profil - siSRI";
require_once __DIR__ . '/../includes/header.php';

// Ambil data pengguna dari session
$user_id = $_SESSION['user_id'];
$query = "SELECT username, email FROM users WHERE id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->execute([':user_id' => $user_id]);
$user = $stmt->fetch();
?>

<div class="container py-5">
    <h1 class="display-5 fw-bold text-primary">Kelola Profil</h1>
    <p class="col-md-8 fs-4 text-muted">Perbarui data diri Anda.</p>
    <hr class="my-4">
    
    <!-- Form untuk mengedit profil -->
    <form action="/sisri/views/update_profil.php" method="POST">
        <div class="mb-3">
            <label for="username" class="form-label">Nama Pengguna</label>
            <input type="text" class="form-control" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Password Baru</label>
            <input type="password" class="form-control" id="password" name="password">
            <small class="form-text text-muted">Kosongkan jika tidak ingin mengubah password.</small>
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
    </form>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>

<?php
require_once __DIR__ . '/../config/session.php'; // Memastikan session aktif
require_once __DIR__ . '/../config/db.php'; // Koneksi ke database


if (!isLoggedIn()) {
    redirect('/sisri/login.php');   // Redirect DI SINI, sebelum output HTML
}
require_once __DIR__ . '/../includes/header.php';

$userId = $_SESSION['user_id'];

// Ambil data user saat ini
$query = "SELECT username, email FROM users WHERE id = :id";
$stmt = $pdo->prepare($query);
$stmt->execute([':id' => $userId]);
$user = $stmt->fetch();

if (!$user) {
    setFlashMessage('Data user tidak ditemukan.', 'error');
    redirect('/sisri/dashboard.php');
}

// Proses update jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);

    // Validasi sederhana
    if ($username === '' || $email === '') {
        setFlashMessage('Username dan Email tidak boleh kosong.', 'warning');
        redirect('/sisri/update_profil.php');
    }

    // Cek email sudah dipakai user lain atau tidak
    $cek = $pdo->prepare("SELECT id FROM users WHERE email = :email AND id != :id");
    $cek->execute([':email' => $email, ':id' => $userId]);
    if ($cek->fetch()) {
        setFlashMessage('Email sudah digunakan oleh pengguna lain.', 'error');
        redirect('/sisri/update_profil.php');
    }

    // Update profil di database
    $update = $pdo->prepare("UPDATE users SET username = :username, email = :email WHERE id = :id");
    $sukses = $update->execute([
        ':username' => $username,
        ':email'    => $email,
        ':id'       => $userId
    ]);

    if ($sukses) {
        setFlashMessage('Profil berhasil diperbarui.', 'success');
        // Update session username/email jika digunakan di navbar dsb
        $_SESSION['username'] = $username;
        $_SESSION['email'] = $email;
        redirect('/sisri/update_profil.php');
    } else {
        setFlashMessage('Gagal memperbarui profil. Coba lagi!', 'error');
        redirect('/sisri/update_profil.php');
    }
}

require_once __DIR__ . '/includes/header.php';
$message = getFlashMessage();
?>

<div class="container py-5">
    <h2 class="mb-4 fw-bold text-success">Update Profil</h2>
    <?php if ($message): ?>
        <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <form method="POST" class="col-md-6 col-lg-5 mx-auto shadow p-4 rounded">
        <div class="mb-3">
            <label class="form-label fw-semibold">Username</label>
            <input type="text" class="form-control" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label fw-semibold">Email</label>
            <input type="email" class="form-control" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <button type="submit" class="btn btn-success w-100">Simpan Perubahan</button>
        <a href="/sisri/dashboard.php" class="btn btn-secondary w-100 mt-2">Kembali ke Dashboard</a>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

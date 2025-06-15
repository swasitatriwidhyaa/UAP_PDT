<?php
// /sisri/register.php
require_once __DIR__ . '/config/session.php';  // Memastikan session aktif
require_once __DIR__ . '/config/db.php'; // Menggunakan db.php untuk koneksi ke database

if (isLoggedIn()) {
    // Jika sudah login, cek role dan arahkan ke dashboard yang sesuai
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        redirect('/sisri/views/admin_dashboard.php');
    } else {
        redirect('/sisri/views/dashboard.php');
    }
}

$pageTitle = "Daftar Akun - siSRI"; // Tetap didefinisikan untuk title tag

$message = getFlashMessage(); // Ambil pesan flash jika ada

// Proses form registrasi ketika form di-submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // Validasi input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        setFlashMessage('Semua kolom harus diisi.', 'error');
        $_SESSION['form_data'] = $_POST; 
        redirect('/sisri/register.php');
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        setFlashMessage('Format email tidak valid.', 'error');
        $_SESSION['form_data'] = $_POST;
        redirect('/sisri/register.php');
    }

    if (strlen($username) < 3 || strlen($username) > 50) {
        setFlashMessage('Username harus antara 3 dan 50 karakter.', 'error');
        $_SESSION['form_data'] = $_POST;
        redirect('/sisri/register.php');
    }

    if (strlen($password) < 6) {
        setFlashMessage('Kata sandi minimal 6 karakter.', 'error');
        $_SESSION['form_data'] = $_POST;
        redirect('/sisri/register.php');
    }

    if ($password !== $confirm_password) {
        setFlashMessage('Konfirmasi kata sandi tidak cocok.', 'error');
        $_SESSION['form_data'] = $_POST;
        redirect('/sisri/register.php');
    }

    // Menggunakan PDO langsung dari db.php, tanpa perlu getDbConnection()
    // Cek apakah username atau email sudah terdaftar
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = :username OR email = :email");
    $stmt->execute([':username' => $username, ':email' => $email]);

    if ($stmt->rowCount() > 0) {
        setFlashMessage('Username atau Email sudah terdaftar.', 'error');
        $_SESSION['form_data'] = $_POST;
        redirect('/sisri/register.php');
    }

    // Hash kata sandi menggunakan password_hash
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Masukkan pengguna baru ke database
    $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password_hash)");
    $stmt->execute([
        ':username' => $username,
        ':email' => $email,
        ':password_hash' => $password_hash
    ]);

    if ($stmt->rowCount() > 0) {
        setFlashMessage('Registrasi berhasil! Silakan login.', 'success');
        unset($_SESSION['form_data']); // Hapus data form setelah berhasil
        redirect('/sisri/login.php'); // Redirect ke halaman login setelah registrasi sukses
    } else {
        setFlashMessage('Terjadi kesalahan saat registrasi.', 'error');
        $_SESSION['form_data'] = $_POST;
        redirect('/sisri/register.php');
    }
}

// Ambil data form dari sesi jika ada (untuk mengisi ulang form setelah error)
$formData = $_SESSION['form_data'] ?? [];
unset($_SESSION['form_data']); // Hapus setelah diambil
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'siSRI') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }
        .card {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
        }
        .card-title {
            color: #28a745;
            font-weight: 700;
        }
        .form-label {
            font-weight: 500;
            color: #343a40;
        }
        .input-group-text {
            background-color: #28a745;
            color: white;
            border-color: #28a745;
        }
        .form-control:focus {
            border-color: #218838;
            box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);
        }
        .btn-success { 
            background-color: #28a745;
            border-color: #28a745;
            transition: background-color 0.3s ease, border-color 0.3s ease, transform 0.2s ease;
        }
        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
            transform: translateY(-2px);
        }
        .btn-success:active {
            transform: translateY(0);
        }
        .text-success {
            color: #28a745 !important;
        }
        .text-muted {
            color: #6c757d !important;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="card shadow-lg p-4 mx-auto" style="max-width: 500px; width: 100%;">
        <div class="card-body">
            <h2 class="card-title text-center mb-4">Daftar Akun siSRI</h2>
            <p class="text-center text-muted mb-4">Buat akun baru untuk mulai mencari indekos impian Anda.</p>

            <?php if ($message): ?>
                <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="/sisri/register.php" method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username Anda" required value="<?= htmlspecialchars($formData['username'] ?? '') ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-envelope-fill"></i></span>
                        <input type="email" class="form-control" id="email" name="email" placeholder="Masukkan email Anda" required value="<?= htmlspecialchars($formData['email'] ?? '') ?>">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Minimal 6 karakter" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="confirm_password" class="form-label">Konfirmasi Kata Sandi</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Ulangi kata sandi Anda" required>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-success btn-lg fw-bold"><i class="bi bi-person-plus-fill me-2"></i> Daftar</button>
                </div>
            </form>
            <p class="text-center mt-4">Sudah punya akun? <a href="/sisri/login.php" class="text-success text-decoration-none fw-bold">Login di sini</a></p>
            <p class="text-center"><a href="/sisri/" class="text-muted text-decoration-none">Kembali ke Beranda</a></p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
<?php
// /sisri/login.php
require_once __DIR__ . '/config/session.php';
require_once __DIR__ . '/config/db.php';

if (isLoggedIn()) {
    if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        redirect('/sisri/views/admin_dashboard.php');
    } else {
        redirect('/sisri/views/dashboard.php');
    }
}

$pageTitle = "Login - siSRI";
$message = getFlashMessage();

// Proses form login ketika form di-submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['username_or_email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        setFlashMessage('Email dan Kata Sandi harus diisi.', 'error');
        $_SESSION['form_data_login'] = $_POST;
        redirect('/sisri/login.php');
    }

    // Pakai global $pdo dari db.php
    $query = "SELECT id, username, email, password_hash, role FROM users WHERE email = :email LIMIT 1";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // Set session login
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['email'] = $user['email'];

        setFlashMessage('Selamat datang, ' . htmlspecialchars($user['username']) . '!', 'success');
        unset($_SESSION['form_data_login']);

        if ($user['role'] === 'admin') {
            redirect('/sisri/views/admin_dashboard.php');
        } else {
            redirect('/sisri/views/dashboard.php');
        }
    } else {
        setFlashMessage('Email atau kata sandi salah.', 'error');
        $_SESSION['form_data_login'] = $_POST;
        redirect('/sisri/login.php');
    }
}

$formDataLogin = $_SESSION['form_data_login'] ?? [];
unset($_SESSION['form_data_login']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'siSRI') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Poppins', sans-serif; background-color: #f0f2f5; display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .card { border: none; border-radius: 1rem; box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.1);}
        .card-title { color: #28a745; font-weight: 700; }
        .form-label { font-weight: 500; color: #343a40;}
        .input-group-text { background-color: #28a745; color: white; border-color: #28a745;}
        .form-control:focus { border-color: #218838; box-shadow: 0 0 0 0.25rem rgba(40, 167, 69, 0.25);}
        .btn-success { background-color: #28a745; border-color: #28a745;}
        .btn-success:hover { background-color: #218838; border-color: #1e7e34;}
        .btn-success:active { transform: translateY(0);}
        .text-success { color: #28a745 !important;}
        .text-muted { color: #6c757d !important;}
    </style>
</head>
<body>
<div class="container py-5">
    <div class="card shadow-lg p-4 mx-auto" style="max-width: 450px; width: 100%;">
        <div class="card-body">
            <h2 class="card-title text-center mb-4">Login ke siSRI</h2>
            <p class="text-center text-muted mb-4">Masuk untuk mengakses fitur lengkap siSRI.</p>

            <?php if ($message): ?>
                <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="/sisri/login.php" method="POST">
                <div class="mb-3">
                    <label for="username_or_email" class="form-label">Email</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-fill"></i></span>
                        <input type="text" class="form-control" id="username_or_email" name="username_or_email" placeholder="Masukkan email Anda" required value="<?= htmlspecialchars($formDataLogin['username_or_email'] ?? '') ?>">
                    </div>
                </div>
                <div class="mb-4">
                    <label for="password" class="form-label">Kata Sandi</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control" id="password" name="password" placeholder="Masukkan kata sandi Anda" required>
                    </div>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-success btn-lg fw-bold"><i class="bi bi-box-arrow-in-right me-2"></i> Login</button>
                </div>
            </form>
            <p class="text-center mt-4">Belum punya akun? <a href="/sisri/register.php" class="text-success text-decoration-none fw-bold">Daftar sekarang</a></p>
            <p class="text-center"><a href="/sisri/" class="text-muted text-decoration-none">Kembali ke Beranda</a></p>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

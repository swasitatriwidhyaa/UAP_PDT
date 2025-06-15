<?php
// /sisri/controllers/authController.php
require_once __DIR__ . '/../models/userModel.php';
require_once __DIR__ . '/../config/session.php'; // Untuk setFlashMessage dan redirect

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    // Metode untuk menangani proses registrasi
    public function registerUser($username, $email, $password, $confirm_password) {
        // Implementasi logika validasi dan pendaftaran di sini
        // Mirip dengan yang ada di register.php, tapi dipindahkan ke dalam metode ini
        if ($password !== $confirm_password) {
            setFlashMessage('Konfirmasi kata sandi tidak cocok.', 'error');
            return false;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        // Cek apakah username atau email sudah ada
        if ($this->userModel->findUser($username) || $this->userModel->findUser($email)) {
            setFlashMessage('Username atau Email sudah terdaftar.', 'error');
            return false;
        }

        if ($this->userModel->createUser($username, $email, $passwordHash)) {
            setFlashMessage('Registrasi berhasil! Silakan login.', 'success');
            return true;
        } else {
            setFlashMessage('Terjadi kesalahan saat registrasi.', 'error');
            return false;
        }
    }

    // Metode untuk menangani proses login
    public function loginUser($username_or_email, $password) {
        $user = $this->userModel->findUser($username_or_email);

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            setFlashMessage('Selamat datang, ' . htmlspecialchars($user['username']) . '!', 'success');
            return true;
        } else {
            setFlashMessage('Username/Email atau Kata Sandi salah.', 'error');
            return false;
        }
    }

    // Metode untuk logout
    public function logout() {
        // Logika logout (menghancurkan sesi)
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
        setFlashMessage('Anda telah berhasil logout.', 'info');
    }

    // Metode untuk menutup koneksi model
    public function __destruct() {
        $this->userModel->closeConnection();
    }
}
?>
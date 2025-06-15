<?php
// /sisri/logout.php
require_once __DIR__ . '/config/session.php';

// Hapus semua variabel sesi
$_SESSION = array();

// Hapus cookie sesi
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan sesi
session_destroy();

setFlashMessage('Anda telah berhasil logout.', 'info');
redirect('/sisri/login.php');
?>
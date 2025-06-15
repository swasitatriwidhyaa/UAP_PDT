<?php
// /sisri/config/session.php

// Pastikan tidak ada karakter sebelum tag <?php di sini
if (session_status() === PHP_SESSION_NONE) {
    session_start(); // Mulai session di awal, sebelum ada output apapun
}

/**
 * Memeriksa apakah pengguna sudah login.
 * @return bool True jika sudah login, false jika belum.
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Mengarahkan pengguna ke URL tertentu.
 * @param string $url URL tujuan.
 */
function redirect($url) {
    // Pastikan tidak ada output sebelum header()
    header("Location: " . $url);
    exit(); // Menghentikan eksekusi setelah redirect
}

/**
 * Menampilkan pesan flash (notifikasi sementara).
 * @param string $message Pesan yang akan ditampilkan.
 * @param string $type Tipe pesan (success, error, warning, info).
 */
function setFlashMessage($message, $type = 'info') {
    $_SESSION['flash_message'] = $message;
    $_SESSION['flash_type'] = $type;
}

/**
 * Mendapatkan dan menghapus pesan flash.
 * @return array|null Array berisi pesan dan tipe, atau null jika tidak ada pesan.
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = [
            'message' => $_SESSION['flash_message'],
            'type' => $_SESSION['flash_type'] ?? 'info'
        ];
        unset($_SESSION['flash_message']);
        unset($_SESSION['flash_type']);
        return $message;
    }
    return null;
}
?>

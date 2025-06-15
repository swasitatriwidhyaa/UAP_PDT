<?php
// /views/home.php
require_once __DIR__ . '/../config/session.php'; // Pastikan session_start() ada di sini atau di session.php

if (session_status() === PHP_SESSION_NONE) { // Pastikan sesi dimulai
    session_start();
}

// Tambahkan fungsi isLoggedIn jika belum ada di session.php
if (!function_exists('isLoggedIn')) {
    function isLoggedIn() {
        return isset($_SESSION['user_id']); // Atau 'username', tergantung logika Anda
    }
}


$pageTitle = "Beranda - siSRI";
require_once __DIR__ . '/../includes/header.php'; // Pastikan header ini memuat Bootstrap CSS
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<style>
    body {
        background-color: #f8f9fa; /* Warna latar belakang yang lebih lembut */
    }
    .hero-home {
        /* Ganti path gambar Anda di sini! */
        background: url('/sisri/assets/img/kos.jpg') no-repeat center center; /* Path gambar Anda */
        background-size: cover; /* Pastikan gambar mencakup seluruh area */
        position: relative; /* Penting untuk overlay dan gelombang */
        color: white; /* Teks tetap putih agar kontras dengan gambar */
        padding: 6rem 0; /* Padding lebih besar untuk kesan "hero" */
        border-radius: 0 0 1.5rem 1.5rem; /* Sudut bawah yang lebih melengkung */
        box-shadow: 0 0.75rem 1.5rem rgba(0, 0, 0, 0.2); /* Bayangan lebih kuat */
        animation: slideInDown 1s ease-out; /* Animasi masuk dari atas */
        overflow: hidden; /* Penting untuk efek gelombang */
        z-index: 0; /* Pastikan ini tidak mengganggu elemen lain jika ada z-index tinggi */
    }
    /* Overlay gelap untuk membuat teks lebih mudah dibaca di atas gambar */
    .hero-home::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5); /* Warna hitam transparan 50% */
        z-index: -1; /* Di bawah konten hero tapi di atas gambar */
    }

    /* Efek gelombang di bawah hero section */
    .hero-home::after {
        content: '';
        position: absolute;
        bottom: -20px; /* Sesuaikan untuk posisi gelombang */
        left: 0;
        width: 100%;
        height: 100px; /* Tinggi gelombang */
        background: url('data:image/svg+xml;utf8,<svg viewBox="0 0 1440 100" xmlns="http://www.w3.org/2000/svg"><path fill="%23f8f9fa" fill-opacity="1" d="M0,64L80,74.7C160,85,320,107,480,90.7C640,75,800,27,960,26.7C1120,27,1280,75,1360,98.7L1440,123L1440,320L1360,320C1280,320,1120,320,960,320C800,320,640,320,480,320C320,320,160,320,80,320L0,320Z"></path></svg>') no-repeat center bottom;
        background-size: cover;
        z-index: 1; /* Di atas konten hero */
    }


    @keyframes slideInDown {
        from { opacity: 0; transform: translateY(-50px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .hero-home h1 {
        font-weight: 700;
        font-size: 3.8rem; /* Ukuran font lebih besar lagi */
        margin-bottom: 1.5rem;
        text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.4); /* Bayangan teks lebih kuat untuk visibilitas di atas gambar */
    }
    .hero-home p {
        font-size: 1.35rem;
        opacity: 0.95;
        line-height: 1.6;
        text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3); /* Bayangan teks */
    }
    .card-feature {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 0.35rem 1rem rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        background-color: #ffffff;
    }
    .card-feature:hover {
        transform: translateY(-8px); /* Efek angkat yang lebih dramatis */
        box-shadow: 0 0.75rem 2rem rgba(0, 0, 0, 0.2);
    }
    .card-feature .card-title {
        color: #28a745; /* Warna hijau yang konsisten */
        font-weight: 600;
        font-size: 1.6rem;
        margin-bottom: 0.75rem;
    }
    .card-feature .card-text {
        font-size: 1.05rem;
        color: #555;
    }
    .feature-icon {
        font-size: 3rem; /* Ukuran ikon fitur */
        color: #28a745;
        margin-bottom: 1rem;
        display: block; /* Agar ikon berada di baris sendiri */
    }
    .btn-primary {
        background-color: #28a745;
        border-color: #28a745;
        transition: background-color 0.3s ease, border-color 0.3s ease;
    }
    .btn-primary:hover {
        background-color: #218838;
        border-color: #1e7e34;
    }
    .btn-outline-secondary {
        color: #6c757d;
        border-color: #6c757d;
        transition: all 0.3s ease;
    }
    .btn-outline-secondary:hover {
        background-color: #6c757d;
        color: white;
    }
</style>

<div class="container-fluid px-0"> <div class="hero-home text-center mb-5">
        <div class="hero-overlay"></div>
        <div class="container py-5 position-relative" style="z-index: 1;"> <h1 class="display-5 fw-bold">Temukan Indekos Impian Anda</h1>
            <p class="col-lg-8 fs-4 mx-auto">siSRI adalah platform rekomendasi indekos cerdas yang membantu Anda menemukan tempat tinggal terbaik di Bandar Lampung dengan mudah dan cepat, sesuai dengan preferensi Anda.</p>
            <?php if (!isLoggedIn()): ?>
            <?php else: ?>
                <p class="lead text-white">
                    Selamat datang kembali, <span class="fw-bold text-warning"><?= htmlspecialchars($_SESSION['username'] ?? 'Pengguna') ?></span>!
                    <br>Siap menjelajahi indekos atau melihat pemesanan Anda?
                    <br><a class="btn btn-warning btn-lg mt-4 px-5 shadow-sm" href="/sisri/views/dashboard.php" role="button"><i class="bi bi-house-door-fill me-2"></i> Lihat Dashboard Saya</a>
                </p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container py-5"> <h2 class="text-center fw-bold mb-5 text-success">Mengapa Memilih siSRI?</h2>
    <div class="row row-cols-1 row-cols-md-3 g-4 text-center">
        <div class="col">
            <div class="card card-feature h-100 p-4">
                <div class="card-body">
                    <i class="bi bi-search feature-icon"></i>
                    <h5 class="card-title">Pencarian Mudah</h5>
                    <p class="card-text">Cari indekos berdasarkan lokasi, harga, dan fasilitas yang Anda inginkan dengan filter canggih.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card card-feature h-100 p-4">
                <div class="card-body">
                    <i class="bi bi-lightbulb-fill feature-icon"></i>
                    <h5 class="card-title">Rekomendasi Cerdas</h5>
                    <p class="card-text">Dapatkan rekomendasi indekos yang paling sesuai dengan preferensi dan riwayat pencarian Anda.</p>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card card-feature h-100 p-4">
                <div class="card-body">
                    <i class="bi bi-chat-dots-fill feature-icon"></i>
                    <h5 class="card-title">Ulasan Terpercaya</h5>
                    <p class="card-text">Baca ulasan jujur dari penyewa lain dan bagikan pengalaman Anda untuk membantu komunitas.</p>
                </div>
            </div>
        </div>
    </div>
</div>


<?php
require_once __DIR__ . '/../includes/footer.php';
?>
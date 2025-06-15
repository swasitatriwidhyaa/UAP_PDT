<?php

require_once __DIR__ . '/../config/session.php';   // 1. PALING ATAS!
require_once __DIR__ . '/../config/db.php';        // 2. Koneksi DB
require_once __DIR__ . '/../includes/header.php';  // 3. HTML baru dimulai di sini


// Pastikan fungsi isLoggedIn ada sebagai fallback jika ada masalah loading session.php,

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
            background-color: #f0f2f5; /* Latar belakang body sedikit abu-abu muda */
            padding-top: 70px; /* Sesuaikan tinggi navbar */
        }
        
        /* --- Navbar (Header) Styles --- */
        .navbar {
            background-color: #28a745 !important; /* Latar belakang hijau solid */
            border-bottom: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .navbar-brand {
            font-weight: 700;
            color: #ffffff !important; /* Warna teks siSRI putih */
            font-size: 1.85rem;
            letter-spacing: -0.5px;
            transition: color 0.3s ease;
        }
        .navbar-brand:hover {
            color: #e0e0e0 !important;
        }

        .navbar .nav-link {
            color: #ffffff !important; /* Warna teks link putih */
            font-weight: 500;
            margin-right: 18px;
            transition: color 0.3s ease, background-color 0.3s ease;
            padding: 8px 12px;
            border-radius: 5px;
        }
        .navbar .nav-link:hover {
            color: #f0f0f0 !important;
            background-color: rgba(255, 255, 255, 0.2); /* Highlight putih transparan saat hover */
        }
        .navbar .nav-link.active {
            color: #28a745 !important; /* Teks hijau untuk link aktif */
            background-color: #ffffff !important; /* Latar belakang putih solid untuk link aktif */
            font-weight: 600;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .navbar-toggler {
            border-color: rgba(255, 255, 255, 0.5) !important;
        }
        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 0.8%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e") !important;
        }

        /* --- Footer Styles --- */
        .main-footer {
            background-color: #28a745; /* Latar belakang HIJAU untuk footer */
            color: #ffffff; /* Teks PUTIH */
            padding: 1.5rem 0; /* Padding sedikit lebih kecil */
            box-shadow: 0 -4px 8px rgba(0, 0, 0, 0.2);
            border-top: none;
            text-align: center; /* Pastikan semua teks di footer rata tengah */
        }
        .footer-copyright {
            font-size: 0.95rem; /* Ukuran font sedikit lebih kecil */
            opacity: 0.9; /* Sedikit transparan */
            margin-bottom: 0;
        }

        /* --- General Button & Text Colors --- */
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

        .btn-outline-success {
            color: #28a745;
            border-color: #28a745;
            background-color: transparent;
            transition: all 0.3s ease;
        }
        .btn-outline-success:hover {
            background-color: #28a745;
            color: white;
        }

        .btn-primary { 
            background-color: #28a745;
            border-color: #28a745;
            transition: background-color 0.3s ease, border-color 0.3s ease, transform 0.2s ease;
        }
        .btn-primary:hover {
            background-color: #218838;
            border-color: #1e7e34;
            transform: translateY(-2px);
        }

        .btn-light.text-success.border-success {
            background-color: #ffffff;
            border-color: #28a745 !important;
            color: #28a745 !important;
        }
        .btn-light.text-success.border-success:hover {
            background-color: #f0f0f0;
            color: #218838 !important;
        }
        .btn-outline-light.text-success.border-success {
            background-color: transparent;
            border-color: #28a745 !important;
            color: #28a745 !important;
        }
        .btn-outline-light.text-success.border-success:hover {
            background-color: #28a745;
            color: #ffffff !important;
        }
        
        .text-success { 
            color: #28a745 !important;
        }
        .text-primary { 
            color: #28a745 !important;
        }
        
        /* --- Styles untuk form di login/register (jika di-include di header) --- */
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

        /* Animasi Umum */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        @keyframes slideInDown {
            from { opacity: 0; transform: translateY(-50px); }
            to { opacity: 1; transform: translateY(0); }
        }

    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg fixed-top">
    <div class="container">
        <a class="navbar-brand" href="/sisri/index.php">siSRI</a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
            <ul class="navbar-nav">
                <?php if (isLoggedIn()): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], 'dashboard.php') !== false && $_SESSION['role'] !== 'admin') ? 'active' : '' ?>" href="/sisri/views/dashboard.php">
                            <i class="bi bi-speedometer me-1"></i>Dashboard
                        </a>
                    </li>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], 'admin/dashboard') !== false) ? 'active' : '' ?>" href="/sisri/views/admin_dashboard.php">
                            <i class="bi bi-person-gear me-1"></i>Admin Panel
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="/sisri/logout.php">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], 'index.php') !== false && strpos($_SERVER['REQUEST_URI'], 'dashboard') === false) ? 'active' : '' ?>" aria-current="page" href="/sisri/index.php">
                            <i class="bi bi-house-door-fill me-1"></i>Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], 'login.php') !== false) ? 'active' : '' ?>" href="/sisri/login.php">
                            <i class="bi bi-box-arrow-in-right me-1"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (strpos($_SERVER['REQUEST_URI'], 'register.php') !== false) ? 'active' : '' ?>" href="/sisri/register.php">
                            <i class="bi bi-person-plus-fill me-1"></i>Daftar
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4">
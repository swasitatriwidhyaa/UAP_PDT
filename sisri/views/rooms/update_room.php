<?php
// /sisri/views/rooms/update_room.php

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';  // Koneksi ke database

// Cek apakah pengguna sudah login dan apakah role-nya admin
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    redirect('/sisri/login.php');
}

// Cek apakah data form ada
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $location = $_POST['location'];
    $description = $_POST['description'];
    $current_image = $_POST['current_image'];

    // Handle file upload
    $image = null;
    if (!empty($_FILES['image']['name'])) {
        // Jika ada file gambar baru, upload gambar
        $target_dir = __DIR__ . "/../../uploads/";
        $image = basename($_FILES['image']['name']);
        $target_file = $target_dir . $image;
        move_uploaded_file($_FILES['image']['tmp_name'], $target_file);
    } else {
        // Gunakan gambar lama jika tidak ada gambar baru
        $image = $current_image;
    }

    // Query untuk memperbarui data indekos
    $query = "UPDATE indekos SET nama = :name, harga = :price, lokasi = :location, deskripsi = :description, gambar = :image WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':id' => $id,
        ':name' => $name,
        ':price' => $price,
        ':location' => $location,
        ':description' => $description,
        ':image' => $image,
    ]);

    // Set pesan flash dan arahkan ke halaman daftar indekos
    setFlashMessage('Indekos berhasil diperbarui.', 'success');
    redirect('/sisri/views/rooms/list.php'); // Redirect ke halaman list.php
}
?>

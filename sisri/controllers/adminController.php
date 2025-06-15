<?php
// /sisri/controllers/adminController.php
require_once __DIR__ . '/../models/roomModel.php';
require_once __DIR__ . '/../models/userModel.php';
require_once __DIR__ . '/../models/bookingModel.php';
require_once __DIR__ . '/../config/session.php';

class AdminController {
    private $roomModel;
    private $userModel;
    private $bookingModel;

    public function __construct() {
        $this->roomModel = new RoomModel();
        $this->userModel = new UserModel();
        $this->bookingModel = new BookingModel();
    }

    private function checkAdminAccess() {
        if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
            setFlashMessage('Akses ditolak. Anda bukan administrator.', 'error');
            redirect('/sisri/login.php');
        }
    }

    public function dashboard() {
        $this->checkAdminAccess();
        
        // Memanggil metode dari model untuk mendapatkan statistik
        $totalUsers = $this->userModel->getTotalUsers();
        $totalRooms = $this->roomModel->getTotalRooms();
        $recentBookings = $this->bookingModel->getRecentBookings(5); // Ambil 5 booking terbaru
        
        $pageTitle = "Dashboard Admin - siSRI";
        include __DIR__ . '/../includes/header.php';
        include __DIR__ . '/../views/admin_dashboard.php';
        include __DIR__ . '/../includes/footer.php';
    }

    public function manageRooms() {
        $this->checkAdminAccess();
        
        $rooms = $this->roomModel->getAllRooms();
        $message = getFlashMessage(); // Untuk menampilkan pesan flash (setelah add/edit/delete)

        $pageTitle = "Kelola Indekos - siSRI";
        include __DIR__ . '/../includes/header.php';
        include __DIR__ . '/../views/manage_rooms.php';
        include __DIR__ . '/../includes/footer.php';
    }

    public function addRoom() {
        $this->checkAdminAccess();
        $message = getFlashMessage(); // Ambil pesan flash (error/sukses)

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $location = trim($_POST['location'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $image_path = null;

            // --- Validasi Input ---
            if (empty($name) || $price <= 0 || empty($location) || empty($description)) {
                setFlashMessage('Semua kolom wajib diisi dan harga harus lebih besar dari nol.', 'error');
                $_SESSION['form_data_add_room'] = $_POST; 
                redirect('/sisri/admin/add_room');
            }

            // --- Logika Upload Gambar ---
            if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                $upload_dir = __DIR__ . '/../uploads/';
                // Pastikan direktori uploads ada dan writable (chmod 777 atau 755)
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true); // Buat direktori jika belum ada
                }
                
                $file_name = uniqid('room_') . '_' . basename($_FILES['image']['name']);
                $target_file = $upload_dir . $file_name;
                $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                // Validasi tipe file
                $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                if (!in_array($imageFileType, $allowed_types)) {
                    setFlashMessage("Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan.", "error");
                    $_SESSION['form_data_add_room'] = $_POST;
                    redirect('/sisri/admin/add_room');
                }

                // Validasi ukuran file (misal 2MB = 2 * 1024 * 1024 bytes)
                if ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                    setFlashMessage("Maaf, ukuran file terlalu besar (maks 2MB).", "error");
                    $_SESSION['form_data_add_room'] = $_POST;
                    redirect('/sisri/admin/add_room');
                }

                if (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                    $image_path = '/sisri/uploads/' . $file_name; // Path relatif untuk database
                } else {
                    setFlashMessage("Gagal mengunggah gambar. Pastikan folder 'uploads' bisa ditulisi.", "error");
                    $_SESSION['form_data_add_room'] = $_POST;
                    redirect('/sisri/admin/add_room');
                }
            }

            // --- Panggil Model untuk Menyimpan Data ---
            if ($this->roomModel->addRoom($name, $price, $location, $description, $image_path)) {
                setFlashMessage('Indekos berhasil ditambahkan!', 'success');
                unset($_SESSION['form_data_add_room']); // Hapus data form jika sukses
                redirect('/sisri/admin/manage_rooms'); // <--- PERBAIKAN: Redirect ke URL router
            } else {
                setFlashMessage('Terjadi kesalahan saat menambahkan indekos ke database.', 'error');
                $_SESSION['form_data_add_room'] = $_POST;
                redirect('/sisri/admin/add_room');
            }
        }
        
        // Tampilkan form tambah indekos (untuk GET request atau setelah POST error)
        $formData = $_SESSION['form_data_add_room'] ?? [];
        unset($_SESSION['form_data_add_room']);
        
        $pageTitle = "Tambah Indekos - siSRI";
        include __DIR__ . '/../includes/header.php';
        include __DIR__ . '/../views/rooms/add_form.php';
        include __DIR__ . '/../includes/footer.php';
    }
    

    // Mengedit indekos (Admin - Update)
    public function editRoom($roomId) {
        $this->checkAdminAccess();
        $message = getFlashMessage(); // Ambil pesan flash

        $room = $this->roomModel->getRoomById($roomId);
        if (!$room) {
            setFlashMessage('Indekos tidak ditemukan.', 'error');
            redirect('/sisri/admin/manage_rooms');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $price = floatval($_POST['price'] ?? 0);
            $location = trim($_POST['location'] ?? '');
            $description = trim($_POST['description'] ?? '');
            $current_image = $_POST['current_image'] ?? null;
            $image_path = $current_image;

            // --- Validasi Input ---
            if (empty($name) || $price <= 0 || empty($location) || empty($description)) {
                setFlashMessage('Semua kolom wajib diisi dan harga harus lebih besar dari nol.', 'error');
                // Tidak perlu redirect, form akan diisi ulang dari $formData di bawah
            } else {
                // --- Logika Upload Gambar Baru ---
                if (isset($_FILES['image']) && $_FILES['image']['error'] == UPLOAD_ERR_OK) {
                    $upload_dir = __DIR__ . '/../uploads/';
                    if (!is_dir($upload_dir)) { mkdir($upload_dir, 0777, true); }
                    
                    $file_name = uniqid('room_') . '_' . basename($_FILES['image']['name']);
                    $target_file = $upload_dir . $file_name;
                    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

                    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
                    if (!in_array($imageFileType, $allowed_types)) {
                        setFlashMessage("Maaf, hanya file JPG, JPEG, PNG & GIF yang diizinkan.", "error");
                    } elseif ($_FILES['image']['size'] > 2 * 1024 * 1024) {
                        setFlashMessage("Maaf, ukuran file terlalu besar (maks 2MB).", "error");
                    } elseif (move_uploaded_file($_FILES['image']['tmp_name'], $target_file)) {
                        $image_path = '/sisri/uploads/' . $file_name;
                        // Hapus gambar lama jika ada dan berbeda dengan yang baru diupload
                        if ($current_image && $current_image !== $image_path && file_exists(__DIR__ . '/../' . $current_image)) {
                            unlink(__DIR__ . '/../' . $current_image);
                        }
                    } else {
                        setFlashMessage("Gagal mengunggah gambar baru.", "error");
                    }
                }

                if ($this->roomModel->updateRoom($roomId, $name, $price, $location, $description, $image_path)) {
                    setFlashMessage('Indekos berhasil diperbarui!', 'success');
                    redirect('/sisri/admin/manage_rooms'); // <--- PERBAIKAN: Redirect ke URL router
                } else {
                    setFlashMessage('Terjadi kesalahan saat memperbarui indekos.', 'error');
                }
            }
        }
        
        $formData = $_POST ?? $room; // Isi form dari POST jika ada error, jika tidak dari data room asli

        $pageTitle = "Edit Indekos - siSRI";
        include __DIR__ . '/../includes/header.php';
        include __DIR__ . '/../views/rooms/edit_form.php';
        include __DIR__ . '/../includes/footer.php';
    }

    public function deleteRoom($roomId) {
        $this->checkAdminAccess(); // Pastikan hanya admin yang bisa mengakses
        
        $room = $this->roomModel->getRoomById($roomId);
        if (!$room) {
            setFlashMessage('Indekos tidak ditemukan untuk dihapus.', 'error');
            redirect('/sisri/admin/manage_rooms');
        }

        // Hapus file gambar terkait jika ada
        if ($room['gambar'] && file_exists(__DIR__ . '/../' . $room['gambar'])) {
            unlink(__DIR__ . '/../' . $room['gambar']);
        }

        if ($this->roomModel->deleteRoom($roomId)) {
            setFlashMessage('Indekos berhasil dihapus!', 'success');
        } else {
            setFlashMessage('Gagal menghapus indekos.', 'error');
        }
        redirect('/sisri/admin/manage_rooms'); // <--- PERBAIKAN: Redirect ke URL router
    }
    

    public function __destruct() {

    }
}

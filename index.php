<?php
// /sisri/index.php
require_once __DIR__ . '/config/session.php'; // Pastikan session dimulai

// --- LOAD CONTROLLERS ---
require_once __DIR__ . '/controllers/authController.php';
require_once __DIR__ . '/controllers/roomController.php';
require_once __DIR__ . '/controllers/bookingController.php';
require_once __DIR__ . '/controllers/reviewController.php';
require_once __DIR__ . '/controllers/adminController.php';

// --- LOGIKA REDIRECT AWAL SETELAH LOGIN ---
// Jika pengguna sudah login dan mencoba mengakses root atau login/register, arahkan sesuai role
if (isLoggedIn()) {
    $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $currentPath = str_replace('/sisri', '', $currentPath); // Hapus base path

    // Jika bukan rute admin, atau mencoba login/register lagi
    if ($currentPath === '/' || $currentPath === '/login.php' || $currentPath === '/register.php') {
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
            redirect('/sisri/admin/dashboard');
        } else {
            redirect('/sisri/views/dashboard.php');
        }
    }
    // Jika sudah login dan di rute lain, biarkan router di bawah yang menanganinya
}


// --- ROUTER SEDERHANA ---
$request = $_SERVER['REQUEST_URI'];
$request = explode('?', $request)[0]; // Hapus query string
$request = str_replace('/sisri', '', $request); // Hapus base path jika ada subfolder
$request = trim($request, '/'); // Hapus slash di awal/akhir

switch ($request) {
    case '': // Halaman home (jika belum login)
        require_once __DIR__ . '/views/home.php';
        break;
    case 'login':
        require_once __DIR__ . '/login.php'; // File login.php di root
        break;
    case 'register':
        require_once __DIR__ . '/register.php'; // File register.php di root
        break;
    case 'logout':
        require_once __DIR__ . '/logout.php'; // File logout.php di root
        break;
    
    // --- Rute Admin ---
    case 'admin/dashboard':
        $adminController = new AdminController();
        $adminController->dashboard();
        break;
    case 'views/manage_rooms.php':
        $adminController = new AdminController();
        $adminController->manageRooms();
        break;
    case 'admin/add_room': // Menampilkan form atau memproses POST add room
        $adminController = new AdminController();
        $adminController->addRoom();
        break;
    case (preg_match('/^admin\/edit_room\/(\d+)$/', $request, $matches) ? true : false): // Regex untuk ID
        $roomId = $matches[1];
        $adminController = new AdminController();
        $adminController->editRoom($roomId); // Memanggil metode editRoom
        break;
    case (preg_match('/^admin\/delete_room\/(\d+)$/', $request, $matches) ? true : false): // Regex untuk ID
        $roomId = $matches[1];
        $adminController = new AdminController();
        $adminController->deleteRoom($roomId); // Memanggil metode deleteRoom
        break;

    // --- Rute Pengguna Biasa ---
    case 'rooms': // Menampilkan semua indekos
        $roomController = new RoomController();
        $roomController->index();
        break;
    case (preg_match('/^rooms\/show\/(\d+)$/', $request, $matches) ? true : false): // Regex untuk ID
        $roomId = $matches[1];
        $roomController = new RoomController();
        $roomController->show($roomId);
        break;
    case (preg_match('/^booking\/create\/(\d+)$/', $request, $matches) ? true : false): // Regex untuk ID indekos yang akan dipesan
        $roomId = $matches[1];
        $bookingController = new BookingController();
        $bookingController->create($roomId);
        break;
    case 'booking/store': // Memproses POST dari form booking
        $bookingController = new BookingController();
        $bookingController->store();
        break;
    case 'my_bookings': // Melihat daftar pemesanan user
        $bookingController = new BookingController();
        $bookingController->userBookings();
        break;
    case (preg_match('/^review\/create\/(\d+)$/', $request, $matches) ? true : false): // Regex untuk ID indekos yang akan diulas
        $roomId = $matches[1];
        $reviewController = new ReviewController();
        $reviewController->create($roomId);
        break;
    case 'review/store': // Memproses POST dari form review
        $reviewController = new ReviewController();
        $reviewController->store();
        break;

    // --- Rute untuk views/dashboard.php (jika diakses langsung oleh user biasa) ---
    case 'views/dashboard.php': // Ini adalah path yang diberikan ke redirect user biasa
        require_once __DIR__ . '/views/dashboard.php';
        break;

    default:
        // Halaman 404 Not Found
        header("HTTP/1.0 404 Not Found");
        echo "<h1>404 Not Found</h1><p>Halaman tidak ditemukan.</p>";
        break;
}
?>
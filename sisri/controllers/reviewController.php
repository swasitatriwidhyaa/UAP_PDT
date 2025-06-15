<?php
// /sisri/controllers/reviewController.php
require_once __DIR__ . '/../models/reviewModel.php';
require_once __DIR__ . '/../models/roomModel.php'; // Untuk mendapatkan detail indekos jika perlu di view
require_once __DIR__ . '/../config/session.php';

class ReviewController {
    private $reviewModel;
    private $roomModel; // Untuk menampilkan detail indekos di form review

    public function __construct() {
        $this->reviewModel = new ReviewModel();
        $this->roomModel = new RoomModel();
    }

    // Menampilkan form tambah review untuk indekos tertentu
    public function create($roomId) {
        if (!isLoggedIn()) {
            setFlashMessage('Anda harus login untuk memberikan ulasan.', 'warning');
            redirect('/sisri/login.php');
        }

        $room = $this->roomModel->getRoomById($roomId);
        if (!$room) {
            setFlashMessage('Indekos tidak ditemukan untuk diulas.', 'error');
            redirect('/sisri/rooms');
        }
        
        $pageTitle = "Berikan Ulasan - siSRI";
        include __DIR__ . '/../includes/header.php';
        include __DIR__ . '/../views/review.php'; // VIEW: Form review
        include __DIR__ . '/../includes/footer.php';
    }

    // Memproses pengiriman review dari form
    public function store() {
        if (!isLoggedIn()) {
            setFlashMessage('Anda harus login untuk memberikan ulasan.', 'warning');
            redirect('/sisri/login.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'];
            $roomId = $_POST['room_id'] ?? null;
            $rating = intval($_POST['rating'] ?? 0); // Pastikan integer
            $comment = trim($_POST['comment'] ?? '');

            // Basic validation
            if (empty($roomId) || $rating < 1 || $rating > 5 || empty($comment)) {
                setFlashMessage('Rating dan komentar harus diisi dengan benar.', 'error');
                redirect('/sisri/review/create/' . $roomId); // Redirect kembali ke form
            }

            if ($this->reviewModel->addReview($userId, $roomId, $rating, $comment)) {
                setFlashMessage('Ulasan Anda berhasil ditambahkan!', 'success');
                redirect('/sisri/rooms/show/' . $roomId); // Arahkan kembali ke detail indekos
            } else {
                setFlashMessage('Gagal menambahkan ulasan. Silakan coba lagi.', 'error');
                redirect('/sisri/review/create/' . $roomId);
            }
        } else {
            redirect('/sisri/'); // Arahkan ke beranda jika bukan POST
        }
    }
    
    public function __destruct() {
        $this->reviewModel->closeConnection();
        $this->roomModel->closeConnection();
    }
}
?>
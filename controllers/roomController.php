<?php
// /sisri/controllers/roomController.php
require_once __DIR__ . '/../models/roomModel.php';
require_once __DIR__ . '/../models/reviewModel.php';
require_once __DIR__ . '/../config/session.php';

class RoomController {
    private $roomModel;
    private $reviewModel;

    public function __construct() {
        $this->roomModel = new RoomModel();
        $this->reviewModel = new ReviewModel();
    }

    // Menampilkan daftar indekos
    public function index() {
        $rooms = $this->roomModel->getAllRooms();
        
        $pageTitle = "Daftar Indekos - siSRI";
        include __DIR__ . '/../includes/header.php';
        include __DIR__ . '/../views/rooms/list.php'; // <--- JALUR DIUBAH (views/rooms/list.php)
        include __DIR__ . '/../includes/footer.php';
    }

    // Menampilkan detail satu indekos
    public function show($id) {
        $room = $this->roomModel->getRoomById($id);
        if (!$room) {
            setFlashMessage('Indekos tidak ditemukan.', 'error');
            redirect('/sisri/rooms');
        }
        $reviews = $this->reviewModel->getReviewsByRoomId($id);
        $averageRating = $this->reviewModel->getAverageRatingForRoom($id);

        $pageTitle = htmlspecialchars($room['nama']) . " - siSRI";
        include __DIR__ . '/../includes/header.php';
        include __DIR__ . '/../views/rooms/detail.php'; // <--- JALUR DIUBAH (views/rooms/detail.php)
        include __DIR__ . '/../includes/footer.php';
    }

    public function __destruct() {
        $this->roomModel->closeConnection();
        $this->reviewModel->closeConnection();
    }
}
?>
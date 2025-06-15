<?php
// /sisri/views/rooms/add_form.php
require_once __DIR__ . '/../../config/session.php'; // Pastikan session aktif
require_once __DIR__ . '/../../config/db.php'; // Koneksi ke database

// Cek apakah pengguna sudah login dan apakah role-nya admin
if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    redirect('/sisri/login.php');
}

$pageTitle = "Tambah Indekos - siSRI";

// Pesan flash (jika ada)
$message = getFlashMessage();

// Jika form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $name = trim($_POST['name']);
    $price = $_POST['price'];
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);
    $image = null;

    // Menangani upload gambar
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        // Cek ekstensi file gambar
        $allowedExtensions = ['jpg', 'jpeg', 'png'];
        $fileExtension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);

        if (in_array(strtolower($fileExtension), $allowedExtensions)) {
            // Tentukan nama file baru untuk gambar dan pindahkan file ke direktori uploads
            $image = uniqid() . '.' . $fileExtension;
            move_uploaded_file($_FILES['image']['tmp_name'], __DIR__ . '/../../uploads/' . $image);
        } else {
            setFlashMessage('Hanya file JPG atau PNG yang diperbolehkan.', 'error');
            redirect('/sisri/views/rooms/add_form.php');
        }
    }

    // Validasi input
    if (empty($name) || empty($price) || empty($location) || empty($description)) {
        setFlashMessage('Semua kolom harus diisi.', 'error');
        redirect('/sisri/views/rooms/add_form.php');
    }

    // Query untuk menyimpan data ke tabel indekos
    $query = "INSERT INTO indekos (nama, harga, lokasi, deskripsi, gambar) 
              VALUES (:name, :price, :location, :description, :image)";

    $stmt = $pdo->prepare($query);
    $stmt->execute([
        ':name' => $name,
        ':price' => $price,
        ':location' => $location,
        ':description' => $description,
        ':image' => $image
    ]);

    setFlashMessage('Indekos berhasil ditambahkan!', 'success');
    redirect('/sisri/views/rooms/list.php');
}

require_once __DIR__ . '/../../includes/header.php'; // Memasukkan header
?>

<div class="container py-5">
    <div class="card shadow-lg p-4" style="max-width: 800px; margin: auto; border-radius: 1rem;">
        <div class="card-body">
            <h2 class="card-title text-center mb-4 fw-bold text-success">Tambah Indekos Baru</h2>
            <p class="text-center text-muted mb-4">Lengkapi detail indekos yang akan ditambahkan.</p>

            <?php if ($message): ?>
                <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="/sisri/views/rooms/add_form.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama Indekos</label>
                    <input type="text" class="form-control" id="name" name="name" placeholder="Contoh: Indekos Bahagia Putri" required>
                </div>
                <div class="mb-3">
                    <label for="price" class="form-label">Harga per Bulan (Rp)</label>
                    <input type="number" class="form-control" id="price" name="price" placeholder="Contoh: 750000" min="0" required>
                </div>
                <div class="mb-3">
                    <label for="location" class="form-label">Lokasi</label>
                    <input type="text" class="form-control" id="location" name="location" placeholder="Contoh: Jalan Pahlawan No. 10, Bandar Lampung" required>
                </div>
                <div class="mb-3">
                    <label for="description" class="form-label">Deskripsi</label>
                    <textarea class="form-control" id="description" name="description" rows="5" placeholder="Deskripsi lengkap indekos, fasilitas, dan keunggulan." required></textarea>
                </div>
                <div class="mb-4">
                    <label for="image" class="form-label">Gambar Utama Indekos</label>
                    <input type="file" class="form-control" id="image" name="image" accept="image/*">
                    <small class="form-text text-muted">Opsional. Format: JPG, PNG. Max ukuran: 2MB.</small>
                </div>
                
                <div class="d-grid">
                    <button type="submit" class="btn btn-success btn-lg fw-bold">Tambah Indekos</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
require_once __DIR__ . '/../../includes/footer.php'; // Memasukkan footer
?>

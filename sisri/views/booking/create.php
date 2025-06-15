<?php
// /sisri/views/booking/create.php
require_once __DIR__ . '/../../config/session.php'; // Memastikan session aktif
require_once __DIR__ . '/../../config/db.php'; // Koneksi ke database

// Cek apakah pengguna sudah login
if (!isLoggedIn()) {
    redirect('/sisri/login.php');
}

// Ambil data indekos berdasarkan ID dari URL
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $roomId = $_GET['id'];

    // Query untuk mendapatkan data indekos
    $query = "SELECT * FROM indekos WHERE id = :id";
    $stmt = $pdo->prepare($query);
    $stmt->execute([':id' => $roomId]);

    $room = $stmt->fetch();

    if (!$room) {
        setFlashMessage('Indekos tidak ditemukan.', 'error');
        redirect('/sisri/views/rekomendasi_indekos.php'); // Redirect jika indekos tidak ditemukan
    }
} else {
    setFlashMessage('ID indekos tidak ditemukan.', 'error');
    redirect('/sisri/views/rekomendasi_indekos.php'); // Redirect jika tidak ada ID di URL
}

$pageTitle = "Buat Pemesanan - siSRI"; // Judul halaman
require_once __DIR__ . '/../../includes/header.php'; // Memasukkan header

$message = getFlashMessage(); // Ambil pesan flash jika ada
?>

<div class="container py-5">
    <?php if ($message): ?>
        <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <h1 class="display-5 fw-bold text-primary mb-4">Buat Pemesanan untuk <?= htmlspecialchars($room['nama']) ?></h1>
    <p class="fs-4 text-muted">Isi form berikut untuk memesan indekos ini.</p>

    <form action="/sisri/views/booking/store.php" method="POST">
        <input type="hidden" name="room_id" value="<?= htmlspecialchars($room['id']) ?>">
        <input type="hidden" name="user_id" value="<?= htmlspecialchars($_SESSION['user_id']) ?>">

        <div class="mb-3">
            <label for="check_in_date" class="form-label">Tanggal Check-In</label>
            <input type="date" class="form-control" id="check_in_date" name="check_in_date" required>
        </div>

        <div class="mb-3">
            <label for="check_out_date" class="form-label">Tanggal Check-Out</label>
            <input type="date" class="form-control" id="check_out_date" name="check_out_date" required>
        </div>

        <div class="mb-4">
            <label for="total_price" class="form-label">Total Harga</label>
            <input type="text" class="form-control" id="total_price" name="total_price" value="Rp <?= number_format($room['harga'], 0, ',', '.') ?>" readonly>
        </div>

        <button type="submit" class="btn btn-success btn-lg"><i class="bi bi-calendar-check me-2"></i> Pesan Sekarang</button>
    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const checkInDateInput = document.getElementById('check_in_date');
        const checkOutDateInput = document.getElementById('check_out_date');
        const totalPriceInput = document.getElementById('total_price');
        const roomPricePerMonth = <?= $room['harga'] ?? 0 ?>;

        // Fungsi untuk menghitung total harga berdasarkan tanggal check-in dan check-out
        function calculateTotalPrice() {
            const checkInDate = new Date(checkInDateInput.value);
            const checkOutDate = new Date(checkOutDateInput.value);

            if (checkInDate && checkOutDate && checkInDate < checkOutDate) {
                const diffTime = Math.abs(checkOutDate - checkInDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); // Menghitung jumlah hari
                const diffMonths = diffDays / 30; // Asumsi 30 hari per bulan
                const totalPrice = roomPricePerMonth * diffMonths; // Menghitung total harga
                totalPriceInput.value = 'Rp ' + (totalPrice).toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                totalPriceInput.name = 'total_price_calculated'; // Mengubah nama agar tidak bentrok
                totalPriceInput.dataset.actualPrice = totalPrice; // Simpan nilai asli
            } else {
                totalPriceInput.value = 'Akan dihitung otomatis';
                totalPriceInput.name = 'total_price'; // Kembali ke nama asli jika tidak valid
                totalPriceInput.dataset.actualPrice = '';
            }
        }

        checkInDateInput.addEventListener('change', calculateTotalPrice);
        checkOutDateInput.addEventListener('change', calculateTotalPrice);

        // Pastikan name dikirim saat submit, ganti dengan hidden field jika perlu
        document.querySelector('form').addEventListener('submit', function() {
            const actualPrice = totalPriceInput.dataset.actualPrice;
            if (actualPrice) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'total_price';
                hiddenInput.value = actualPrice;
                this.appendChild(hiddenInput);
            }
        });
    });
</script>

<?php
require_once __DIR__ . '/../../includes/footer.php'; // Memasukkan footer
?>

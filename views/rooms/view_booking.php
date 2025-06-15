<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';

if (!isLoggedIn() || $_SESSION['role'] !== 'admin') {
    redirect('/sisri/login.php');
}

$bookingId = $_GET['id'] ?? null;
if (!$bookingId) {
    setFlashMessage('Booking tidak ditemukan.', 'error');
    redirect('/sisri/views/admin_dashboard.php');
}

// Ambil data booking
$query = "SELECT b.*, u.username, r.nama AS room_name FROM bookings b
          JOIN users u ON b.user_id = u.id
          JOIN indekos r ON b.room_id = r.id
          WHERE b.id = :id";
$stmt = $pdo->prepare($query);
$stmt->execute([':id' => $bookingId]);
$booking = $stmt->fetch();

if (!$booking) {
    setFlashMessage('Booking tidak ditemukan.', 'error');
    redirect('/sisri/views/admin_dashboard.php');
}

// Proses approve/reject booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    $newStatus = '';
    if ($action === 'approve') {
        $newStatus = 'confirmed';
    } elseif ($action === 'reject') {
        $newStatus = 'rejected';
    }
    if ($newStatus) {
        $update = $pdo->prepare("UPDATE bookings SET status = :status WHERE id = :id");
        $update->execute([':status' => $newStatus, ':id' => $bookingId]);
        setFlashMessage('Status booking berhasil diubah.', 'success');
        redirect('/sisri/views/rooms/view_booking.php?id=' . $bookingId);
    }
}
require_once __DIR__ . '/../../includes/header.php';
$message = getFlashMessage();
?>

<div class="container py-4">
    <?php if ($message): ?>
        <div class="alert alert-<?= $message['type'] ?>"><?= htmlspecialchars($message['message']) ?></div>
    <?php endif; ?>
    <h3 class="fw-bold mb-4">Detail Pemesanan</h3>
    <table class="table table-bordered mb-4">
        <tr><th>ID</th><td><?= htmlspecialchars($booking['id']) ?></td></tr>
        <tr><th>Nama User</th><td><?= htmlspecialchars($booking['username']) ?></td></tr>
        <tr><th>Nama Indekos</th><td><?= htmlspecialchars($booking['room_name']) ?></td></tr>
        <tr><th>Tanggal Mulai</th><td><?= htmlspecialchars($booking['start_date']) ?></td></tr>
        <tr><th>Tanggal Selesai</th><td><?= htmlspecialchars($booking['end_date']) ?></td></tr>
        <tr><th>Total Harga</th><td>Rp <?= number_format($booking['total_price'], 0, ',', '.') ?></td></tr>
        <tr><th>Status</th><td>
            <?php
                $status = $booking['status'];
                $badge = 'secondary';
                if ($status == 'pending') $badge = 'warning';
                elseif ($status == 'confirmed') $badge = 'success';
                elseif ($status == 'rejected') $badge = 'danger';
            ?>
            <span class="badge bg-<?= $badge ?>"><?= ucfirst($status) ?></span>
        </td></tr>
    </table>

    <!-- Tampilkan tombol hanya jika status masih pending -->
    <?php if ($booking['status'] == 'pending'): ?>
        <form method="POST">
            <button name="action" value="approve" class="btn btn-success me-2" onclick="return confirm('Setujui booking ini?')">Setujui</button>
            <button name="action" value="reject" class="btn btn-danger" onclick="return confirm('Tolak booking ini?')">Tolak</button>
        </form>
    <?php endif; ?>
    <a href="/sisri/views/admin_dashboard.php" class="btn btn-secondary mt-3">Kembali</a>
</div>
<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

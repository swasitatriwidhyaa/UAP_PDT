<?php
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/header.php';

$userId = $_SESSION['user_id'];
$pageTitle = "Riwayat Pesanan";

// Ambil pesan flash jika ada
$message = getFlashMessage();

// Ambil data pesanan user dan status pembayaran
$query = "SELECT b.id, r.nama AS nama_indekos, b.start_date, b.end_date, b.total_price, b.status 
          FROM bookings b 
          JOIN indekos r ON b.room_id = r.id 
          WHERE b.user_id = :user_id
          ORDER BY b.created_at DESC";
$stmt = $pdo->prepare($query);
$stmt->execute([':user_id' => $userId]);
$bookings = $stmt->fetchAll();
?>

<div class="container py-5">
    <h2 class="fw-bold text-success mb-4">Riwayat Pesanan</h2>

    <?php if ($message): ?>
        <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($message['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Indekos</th>
                    <th>Tanggal Mulai</th>
                    <th>Tanggal Selesai</th>
                    <th>Total Harga</th>
                    <th>Status Pembayaran</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($bookings as $index => $booking): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($booking['nama_indekos']) ?></td>
                        <td><?= date('d M Y', strtotime($booking['start_date'])) ?></td>
                        <td><?= date('d M Y', strtotime($booking['end_date'])) ?></td>
                        <td>Rp <?= number_format($booking['total_price'], 0, ',', '.') ?></td>
                        <td>
                            <?php
                            // Tampilkan status pembayaran dengan badge
                            $status = $booking['status'];
                            $badgeClass = 'secondary';
                            $statusText = 'Status Tidak Diketahui';
                            
                            if ($status == 'pending') {
                                $badgeClass = 'warning';
                                $statusText = 'Menunggu Pembayaran';
                            } elseif ($status == 'confirmed') {
                                $badgeClass = 'success';
                                $statusText = 'Pembayaran Diterima';
                            } elseif ($status == 'rejected') {
                                $badgeClass = 'danger';
                                $statusText = 'Pembayaran Ditolak';
                            }
                            ?>
                            <span class="badge bg-<?= $badgeClass ?>">
                                <?= $statusText ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>

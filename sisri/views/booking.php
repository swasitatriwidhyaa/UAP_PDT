<?php
// /sisri/views/booking.php
// $room, $message (dari controller) akan tersedia di sini
?>

<div class="container py-5">
    <div class="card shadow-lg p-4" style="max-width: 800px; margin: auto; border-radius: 1rem;">
        <div class="card-body">
            <h2 class="card-title text-center mb-4 fw-bold text-success">Pesan Indekos: <?= htmlspecialchars($room['nama'] ?? '') ?></h2>
            <p class="text-center text-muted mb-4">Lengkapi detail pemesanan Anda.</p>

            <?php if ($message): ?>
                <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="/sisri/booking/store" method="POST">
                <input type="hidden" name="room_id" value="<?= htmlspecialchars($room['id'] ?? '') ?>">
                <div class="mb-3">
                    <label for="room_name" class="form-label">Nama Indekos</label>
                    <input type="text" class="form-control" id="room_name" value="<?= htmlspecialchars($room['nama'] ?? '') ?>" readonly>
                </div>
                <div class="mb-3">
                    <label for="room_price" class="form-label">Harga per Bulan</label>
                    <input type="text" class="form-control" id="room_price" value="Rp <?= number_format($room['harga'] ?? 0, 0, ',', '.') ?>" readonly>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="start_date" class="form-label">Tanggal Mulai Sewa</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="end_date" class="form-label">Tanggal Selesai Sewa</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="total_price" class="form-label">Total Harga</label>
                    <input type="text" class="form-control" id="total_price" name="total_price" placeholder="Akan dihitung otomatis" readonly>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-success btn-lg fw-bold">Konfirmasi Pemesanan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Contoh JS sederhana untuk menghitung total harga (perlu validasi di backend!)
    document.addEventListener('DOMContentLoaded', function() {
        const startDateInput = document.getElementById('start_date');
        const endDateInput = document.getElementById('end_date');
        const totalPriceInput = document.getElementById('total_price');
        const roomPricePerMonth = <?= $room['harga'] ?? 0 ?>;

        function calculateTotalPrice() {
            const startDate = new Date(startDateInput.value);
            const endDate = new Date(endDateInput.value);

            if (startDate && endDate && startDate < endDate) {
                const diffTime = Math.abs(endDate - startDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)); 
                const diffMonths = diffDays / 30; // Asumsi 30 hari per bulan
                const totalPrice = roomPricePerMonth * diffMonths;
                totalPriceInput.value = 'Rp ' + (totalPrice).toLocaleString('id-ID', {minimumFractionDigits: 0, maximumFractionDigits: 0});
                totalPriceInput.name = 'total_price_calculated'; // Ubah nama agar tidak bentrok
                totalPriceInput.dataset.actualPrice = totalPrice; // Simpan nilai asli
            } else {
                totalPriceInput.value = 'Akan dihitung otomatis';
                totalPriceInput.name = 'total_price'; // Kembali ke nama asli jika tidak valid
                totalPriceInput.dataset.actualPrice = '';
            }
        }

        startDateInput.addEventListener('change', calculateTotalPrice);
        endDateInput.addEventListener('change', calculateTotalPrice);
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
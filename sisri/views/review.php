<?php
// /sisri/views/review.php
// $room (jika didapatkan dari controller) akan tersedia di sini
?>

<div class="container py-5">
    <div class="card shadow-lg p-4" style="max-width: 700px; margin: auto; border-radius: 1rem;">
        <div class="card-body">
            <h2 class="card-title text-center mb-4 fw-bold text-success">Berikan Ulasan untuk Indekos</h2>
            <p class="text-center text-muted mb-4">Bagikan pengalaman Anda tentang indekos ini.</p>

            <?php if ($message): ?>
                <div class="alert alert-<?= $message['type'] ?> alert-dismissible fade show" role="alert">
                    <?= htmlspecialchars($message['message']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <form action="/sisri/review/store" method="POST">
                <input type="hidden" name="room_id" value="<?= htmlspecialchars($_GET['room_id'] ?? '') ?>"> <div class="mb-3">
                    <label for="rating" class="form-label">Rating</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-star-fill"></i></span>
                        <select class="form-select" id="rating" name="rating" required>
                            <option value="">Pilih Rating</option>
                            <option value="1">1 Bintang</option>
                            <option value="2">2 Bintang</option>
                            <option value="3">3 Bintang</option>
                            <option value="4">4 Bintang</option>
                            <option value="5">5 Bintang</option>
                        </select>
                    </div>
                </div>
                <div class="mb-4">
                    <label for="comment" class="form-label">Komentar</label>
                    <textarea class="form-control" id="comment" name="comment" rows="5" placeholder="Tulis ulasan Anda di sini..." required></textarea>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-success btn-lg fw-bold">Kirim Ulasan</button>
                </div>
            </form>
        </div>
    </div>
</div>
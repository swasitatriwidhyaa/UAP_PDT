<?php
// /sisri/includes/footer.php
?>

</div> <!-- Closing div for container -->

<!-- Footer Section -->
<footer class="main-footer">
    <div class="container">
        <p class="footer-copyright mb-0">&copy; <?= date('Y') ?> siSRI. All rights reserved.</p>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="/sisri/assets/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="/sisri/assets/js/custom.js"></script>

<!-- Custom Styles -->
<style>
    /* Pastikan body mengisi 100% layar */
    html, body {
        height: 100%; /* Mengisi seluruh layar */
        margin: 0; /* Menghapus margin default */
        display: flex; /* Gunakan Flexbox */
        flex-direction: column; /* Susunan vertikal */
    }

    /* Wrapper utama untuk konten */
    .container {
        flex: 1; /* Konten utama mengisi ruang yang tersisa */
    }

    /* Footer tetap di bawah */
    footer.main-footer {
        margin-top: auto; /* Pastikan footer tetap di bawah */
    }
</style>

<script>
    // Pastikan footer selalu berada di bawah halaman
    document.addEventListener('DOMContentLoaded', function () {
        const body = document.querySelector('body');
        const footer = document.querySelector('footer.main-footer');
        
        // Pastikan body mengisi seluruh tinggi layar
        if (body.offsetHeight < window.innerHeight) {
            footer.style.position = 'absolute';
            footer.style.bottom = '0';
            footer.style.width = '100%';
        } else {
            footer.style.position = 'static';
        }
    });
</script>

</body>
</html>

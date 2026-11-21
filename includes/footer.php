<?php
/**
 * Bagian bawah layout.
 */
if (!defined('APP_NAME')) {
    http_response_code(403);
    exit('Akses langsung tidak diizinkan.');
}
?>
        </div><!-- /.content -->

        <footer class="app-footer">
            <span>&copy; <?= date('Y') ?> <?= APP_NAME ?> &mdash; <?= TOKO_NAMA ?></span>
            <span>Dibuat dengan PHP Native &amp; MySQL</span>
        </footer>
    </main>
</div>

<script src="<?= url('assets/js/app.js') ?>?v=1.0"></script>
</body>
</html>

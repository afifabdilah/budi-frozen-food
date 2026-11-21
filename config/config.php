<?php
/**
 * Konfigurasi utama aplikasi Budi Frozen.
 * Semua halaman meng-include file ini paling atas.
 */
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ---------------------------------------------------------------------
// Identitas aplikasi
// ---------------------------------------------------------------------
define('APP_NAME', 'Budi Frozen');
define('APP_SUBTITLE', 'Pencatatan Produk Frozen Food');
define('TOKO_NAMA', 'Toko Frozen Food Pak Budi');
define('STOK_MENIPIS', 10); // batas peringatan stok menipis

// ---------------------------------------------------------------------
// Pengaturan database (sesuaikan dengan XAMPP Anda)
// ---------------------------------------------------------------------
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'toko_frozen_food');
define('DB_USER', 'root');
define('DB_PASS', '');

// ---------------------------------------------------------------------
// Lokasi folder aplikasi (untuk link & asset)
// ---------------------------------------------------------------------
define('APP_DIR', dirname(__DIR__));

$docRoot = !empty($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
$appRoot = realpath(APP_DIR);

if ($docRoot && $appRoot && str_starts_with(str_replace('\\', '/', $appRoot), str_replace('\\', '/', $docRoot))) {
    $relative = substr(str_replace('\\', '/', $appRoot), strlen(str_replace('\\', '/', $docRoot)));
} else {
    $relative = '';
}
define('BASE_URL', rtrim($relative, '/')); // contoh: '' atau '/frozenfood'

require_once __DIR__ . '/../includes/fungsi.php';

// ---------------------------------------------------------------------
// Koneksi database
// ---------------------------------------------------------------------
try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    echo '<!doctype html><html lang="id"><head><meta charset="utf-8">';
    echo '<title>Koneksi Database Gagal</title>';
    echo '<style>body{font-family:Segoe UI,sans-serif;background:#0b1f33;color:#e2e8f0;display:grid;place-items:center;min-height:100vh;margin:0}';
    echo '.box{background:#122a42;padding:32px 36px;border-radius:14px;max-width:560px;border:1px solid #1e3a55}';
    echo 'h1{margin:0 0 12px;font-size:20px;color:#f87171}code{background:#0b1f33;padding:2px 6px;border-radius:5px;color:#7dd3fc}';
    echo 'ul{line-height:1.9;padding-left:20px;color:#cbd5e1}</style></head><body><div class="box">';
    echo '<h1>Koneksi database gagal</h1>';
    echo '<p>Pastikan MySQL di XAMPP sudah berjalan dan database sudah di-import.</p>';
    echo '<ul>';
    echo '<li>Import <code>database/frozenfood.sql</code> lewat phpMyAdmin</li>';
    echo '<li>Atau jalankan: <code>mysql -u root &lt; database/frozenfood.sql</code></li>';
    echo '<li>Periksa host/user/password di <code>config/config.php</code></li>';
    echo '</ul>';
    echo '<p style="color:#94a3b8;font-size:13px;margin-bottom:0">Detail: ' . htmlspecialchars($e->getMessage()) . '</p>';
    echo '</div></body></html>';
    exit;
}

<?php
/**
 * Hapus produk (POST + CSRF).
 */
require __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('produk/index.php');
}

csrf_check();

$id = (int) ($_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT nama_produk FROM produk WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$produk = $stmt->fetch();

if (!$produk) {
    set_flash('danger', 'Data produk tidak ditemukan.');
    redirect('produk/index.php');
}

$pdo->prepare('DELETE FROM produk WHERE id = :id')->execute(['id' => $id]);

set_flash('success', 'Produk "' . $produk['nama_produk'] . '" berhasil dihapus.');
redirect('produk/index.php');

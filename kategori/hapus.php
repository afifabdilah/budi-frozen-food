<?php
/**
 * Hapus kategori (POST + CSRF).
 * Kategori tidak boleh dihapus jika masih dipakai produk.
 */
require __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('kategori/index.php');
}

csrf_check();

$id = (int) ($_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT nama_kategori FROM kategori WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$kategori = $stmt->fetch();

if (!$kategori) {
    set_flash('danger', 'Data kategori tidak ditemukan.');
    redirect('kategori/index.php');
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM produk WHERE kategori_id = :id');
$stmt->execute(['id' => $id]);
$jumlah = (int) $stmt->fetchColumn();

if ($jumlah > 0) {
    set_flash('warning', 'Kategori "' . $kategori['nama_kategori'] . '" tidak bisa dihapus karena masih dipakai oleh ' . $jumlah . ' produk. Pindahkan produk ke kategori lain terlebih dahulu.');
    redirect('kategori/index.php');
}

$pdo->prepare('DELETE FROM kategori WHERE id = :id')->execute(['id' => $id]);

set_flash('success', 'Kategori "' . $kategori['nama_kategori'] . '" berhasil dihapus.');
redirect('kategori/index.php');

<?php
/**
 * Hapus supplier (POST + CSRF).
 * Supplier tidak boleh dihapus jika masih dipakai produk.
 */
require __DIR__ . '/../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('supplier/index.php');
}

csrf_check();

$id = (int) ($_POST['id'] ?? 0);

$stmt = $pdo->prepare('SELECT nama_supplier FROM supplier WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $id]);
$supplier = $stmt->fetch();

if (!$supplier) {
    set_flash('danger', 'Data supplier tidak ditemukan.');
    redirect('supplier/index.php');
}

$stmt = $pdo->prepare('SELECT COUNT(*) FROM produk WHERE supplier_id = :id');
$stmt->execute(['id' => $id]);
$jumlah = (int) $stmt->fetchColumn();

if ($jumlah > 0) {
    set_flash('warning', 'Supplier "' . $supplier['nama_supplier'] . '" tidak bisa dihapus karena masih memasok ' . $jumlah . ' produk. Pindahkan produk ke supplier lain terlebih dahulu.');
    redirect('supplier/index.php');
}

$pdo->prepare('DELETE FROM supplier WHERE id = :id')->execute(['id' => $id]);

set_flash('success', 'Supplier "' . $supplier['nama_supplier'] . '" berhasil dihapus.');
redirect('supplier/index.php');

<?php
/**
 * Daftar kategori produk.
 */
require __DIR__ . '/../config/config.php';

$daftarKategori = $pdo->query(
    'SELECT k.*, COUNT(p.id) AS jumlah_produk, COALESCE(SUM(p.stok), 0) AS total_unit
     FROM kategori k
     LEFT JOIN produk p ON p.kategori_id = k.id
     GROUP BY k.id
     ORDER BY k.nama_kategori ASC'
)->fetchAll();

$pageTitle    = 'Kategori';
$pageSubtitle = 'Kelompok produk frozen food';
$active       = 'kategori';
require __DIR__ . '/../includes/header.php';
?>

<div class="page-actions">
    <a class="btn btn-primary" href="<?= url('kategori/form.php') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Kategori
    </a>
</div>

<div class="card">
    <div class="card-head">
        <div>
            <h2>Daftar Kategori</h2>
            <p><?= count($daftarKategori) ?> kategori terdaftar</p>
        </div>
    </div>
    <div class="card-body flush">
        <?php if (!$daftarKategori): ?>
            <div class="empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                <h3>Belum ada kategori</h3>
                <p>Tambahkan kategori agar produk bisa dikelompokkan.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table sticky-actions">
                    <thead>
                        <tr>
                            <th>Kategori</th>
                            <th>Deskripsi</th>
                            <th class="center">Jumlah Produk</th>
                            <th class="center">Total Stok</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($daftarKategori as $k): ?>
                        <tr>
                            <td>
                                <div class="cell-main">
                                    <span class="produk-ico" style="background:var(--primary-soft);color:var(--primary)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>
                                    </span>
                                    <div>
                                        <strong><?= e($k['nama_kategori']) ?></strong>
                                        <small>Kelompok produk</small>
                                    </div>
                                </div>
                            </td>
                            <td><?= e($k['deskripsi'] ?: '-') ?></td>
                            <td class="center">
                                <a href="<?= url('produk/index.php?kategori=' . $k['id']) ?>" title="Lihat produk kategori ini">
                                    <span class="badge <?= (int) $k['jumlah_produk'] > 0 ? 'primary' : '' ?>"><?= (int) $k['jumlah_produk'] ?> produk</span>
                                </a>
                            </td>
                            <td class="center"><span class="stok-num"><?= number_format((int) $k['total_unit'], 0, ',', '.') ?></span> unit</td>
                            <td>
                                <div class="row-actions">
                                    <a class="icon-btn" href="<?= url('produk/index.php?kategori=' . $k['id']) ?>" title="Lihat produk">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    <a class="icon-btn primary" href="<?= url('kategori/form.php?id=' . $k['id']) ?>" title="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.83 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"/></svg>
                                    </a>
                                    <form method="post" action="<?= url('kategori/hapus.php') ?>" style="display:inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $k['id'] ?>">
                                        <button class="icon-btn danger" type="submit" title="Hapus"
                                                data-confirm="Hapus kategori <?= e($k['nama_kategori']) ?>?">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>

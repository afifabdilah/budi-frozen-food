<?php
/**
 * Dashboard: ringkasan stok dan produk toko.
 */
require __DIR__ . '/config/config.php';

$totalProduk   = (int) $pdo->query('SELECT COUNT(*) FROM produk')->fetchColumn();
$totalKategori = (int) $pdo->query('SELECT COUNT(*) FROM kategori')->fetchColumn();
$totalSupplier = (int) $pdo->query('SELECT COUNT(*) FROM supplier')->fetchColumn();
$nilaiStok     = (float) $pdo->query('SELECT COALESCE(SUM(harga * stok), 0) FROM produk')->fetchColumn();
$totalUnit     = (int) $pdo->query('SELECT COALESCE(SUM(stok), 0) FROM produk')->fetchColumn();

$stokMenipis = $pdo->query(
    'SELECT p.*, k.nama_kategori, s.nama_supplier
     FROM produk p
     JOIN kategori k ON k.id = p.kategori_id
     JOIN supplier s ON s.id = p.supplier_id
     WHERE p.stok <= ' . STOK_MENIPIS . '
     ORDER BY p.stok ASC, p.nama_produk ASC
     LIMIT 6'
)->fetchAll();

$produkTerbaru = $pdo->query(
    'SELECT p.*, k.nama_kategori, s.nama_supplier
     FROM produk p
     JOIN kategori k ON k.id = p.kategori_id
     JOIN supplier s ON s.id = p.supplier_id
     ORDER BY p.id DESC
     LIMIT 5'
)->fetchAll();

$perKategori = $pdo->query(
    'SELECT k.id, k.nama_kategori, COUNT(p.id) AS jumlah, COALESCE(SUM(p.stok), 0) AS unit
     FROM kategori k
     LEFT JOIN produk p ON p.kategori_id = k.id
     GROUP BY k.id, k.nama_kategori
     ORDER BY jumlah DESC, k.nama_kategori ASC'
)->fetchAll();

$maxKategori = 0;
foreach ($perKategori as $row) {
    $maxKategori = max($maxKategori, (int) $row['jumlah']);
}

$pageTitle    = 'Dashboard';
$pageSubtitle = 'Ringkasan produk dan stok toko';
$active       = 'dashboard';
require __DIR__ . '/includes/header.php';
?>

<div class="grid grid-4 mb-3">
    <div class="stat">
        <div class="stat-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg></div>
        <div class="stat-info">
            <div class="label">Total Produk</div>
            <div class="value"><?= number_format($totalProduk, 0, ',', '.') ?></div>
            <div class="note"><?= number_format($totalUnit, 0, ',', '.') ?> unit stok</div>
        </div>
    </div>
    <div class="stat">
        <div class="stat-icon green"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg></div>
        <div class="stat-info">
            <div class="label">Kategori</div>
            <div class="value"><?= $totalKategori ?></div>
            <div class="note">kelompok produk</div>
        </div>
    </div>
    <div class="stat">
        <div class="stat-icon amber"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="1"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></div>
        <div class="stat-info">
            <div class="label">Supplier</div>
            <div class="value"><?= $totalSupplier ?></div>
            <div class="note">pemasok terdaftar</div>
        </div>
    </div>
    <div class="stat">
        <div class="stat-icon red"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
        <div class="stat-info">
            <div class="label">Nilai Stok</div>
            <div class="value" style="font-size:22px"><?= rupiah($nilaiStok) ?></div>
            <div class="note">harga &times; stok</div>
        </div>
    </div>
</div>

<?php if ($stokMenipis): ?>
    <div class="alert alert-warning" style="align-items:flex-start">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-top:2px"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <span><strong><?= count($stokMenipis) ?> produk</strong> memiliki stok <?= STOK_MENIPIS ?> atau kurang. Segera lakukan restock agar stok tidak habis.</span>
    </div>
<?php endif; ?>

<div class="grid grid-2-1 mb-3">
    <div class="card">
        <div class="card-head">
            <div>
                <h2>Produk Terbaru</h2>
                <p>5 produk yang terakhir ditambahkan</p>
            </div>
            <a class="btn btn-outline btn-sm spacer" href="<?= url('produk/index.php') ?>">Lihat semua</a>
        </div>
        <div class="card-body flush">
            <?php if (!$produkTerbaru): ?>
                <div class="empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg>
                    <h3>Belum ada produk</h3>
                    <p>Tambahkan produk pertama melalui menu Produk.</p>
                </div>
            <?php else: ?>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Kategori</th>
                                <th class="num">Harga</th>
                                <th class="center">Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($produkTerbaru as $p): ?>
                            <?php $status = status_stok((int) $p['stok']); ?>
                            <tr>
                                <td>
                                    <div class="cell-main">
                                        <span class="produk-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/></svg></span>
                                        <div>
                                            <strong><?= e($p['nama_produk']) ?></strong>
                                            <small><?= e($p['nama_supplier']) ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge primary"><?= e($p['nama_kategori']) ?></span></td>
                                <td class="num harga"><?= rupiah($p['harga']) ?></td>
                                <td class="center">
                                    <span class="badge <?= $status['warna'] ?>"><?= (int) $p['stok'] ?> <?= e($p['satuan']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div>
                <h2>Produk per Kategori</h2>
                <p>Jumlah produk di setiap kategori</p>
            </div>
        </div>
        <div class="card-body">
            <?php if (!$perKategori): ?>
                <p class="text-muted small">Belum ada kategori.</p>
            <?php else: ?>
                <div class="kelas-list">
                    <?php foreach ($perKategori as $row): ?>
                        <?php $persen = $maxKategori > 0 ? ((int) $row['jumlah'] / $maxKategori) * 100 : 0; ?>
                        <div class="row">
                            <div class="row-head">
                                <strong><?= e($row['nama_kategori']) ?></strong>
                                <span><?= (int) $row['jumlah'] ?> produk &middot; <?= (int) $row['unit'] ?> unit</span>
                            </div>
                            <div class="progress"><span style="width: <?= max($persen, 3) ?>%"></span></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-head">
        <div>
            <h2>Stok Menipis &amp; Habis</h2>
            <p>Produk dengan stok <?= STOK_MENIPIS ?> atau kurang</p>
        </div>
        <a class="btn btn-outline btn-sm spacer" href="<?= url('produk/index.php?stok=menipis') ?>">Kelola stok</a>
    </div>
    <div class="card-body flush">
        <?php if (!$stokMenipis): ?>
            <div class="empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="20 6 9 17 4 12"/></svg>
                <h3>Semua stok aman</h3>
                <p>Tidak ada produk yang perlu segera direstock.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th class="hide-lg">Supplier</th>
                            <th class="center">Stok</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($stokMenipis as $p): ?>
                        <?php $status = status_stok((int) $p['stok']); ?>
                        <tr>
                            <td><strong><?= e($p['nama_produk']) ?></strong></td>
                            <td><span class="badge primary"><?= e($p['nama_kategori']) ?></span></td>
                            <td class="hide-lg"><?= e($p['nama_supplier']) ?></td>
                            <td class="center">
                                <span class="badge <?= $status['warna'] ?>"><?= (int) $p['stok'] ?> <?= e($p['satuan']) ?></span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a class="btn btn-outline btn-sm" href="<?= url('produk/form.php?id=' . $p['id']) ?>">Restock</a>
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

<?php require __DIR__ . '/includes/footer.php'; ?>

<?php
/**
 * Daftar supplier (pemasok).
 */
require __DIR__ . '/../config/config.php';

$daftarSupplier = $pdo->query(
    'SELECT s.*, COUNT(p.id) AS jumlah_produk
     FROM supplier s
     LEFT JOIN produk p ON p.supplier_id = s.id
     GROUP BY s.id
     ORDER BY s.nama_supplier ASC'
)->fetchAll();

$pageTitle    = 'Supplier';
$pageSubtitle = 'Pemasok produk frozen food';
$active       = 'supplier';
require __DIR__ . '/../includes/header.php';
?>

<div class="page-actions">
    <a class="btn btn-primary" href="<?= url('supplier/form.php') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Supplier
    </a>
</div>

<div class="card">
    <div class="card-head">
        <div>
            <h2>Daftar Supplier</h2>
            <p><?= count($daftarSupplier) ?> supplier terdaftar</p>
        </div>
    </div>
    <div class="card-body flush">
        <?php if (!$daftarSupplier): ?>
            <div class="empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="1" y="3" width="15" height="13" rx="1"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                <h3>Belum ada supplier</h3>
                <p>Tambahkan supplier agar produk punya sumber pemasok.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table sticky-actions">
                    <thead>
                        <tr>
                            <th>Supplier</th>
                            <th>Kontak</th>
                            <th class="hide-lg">Alamat</th>
                            <th class="center">Jumlah Produk</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($daftarSupplier as $s): ?>
                        <tr>
                            <td>
                                <div class="cell-main">
                                    <span class="produk-ico" style="background:var(--success-soft);color:var(--success)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="3" width="15" height="13" rx="1"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
                                    </span>
                                    <div>
                                        <strong><?= e($s['nama_supplier']) ?></strong>
                                        <small><?= e($s['kontak_person'] ?: 'Kontak belum diisi') ?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="nowrap">
                                <?php if ($s['no_hp']): ?>
                                    <a href="tel:<?= e($s['no_hp']) ?>"><?= e($s['no_hp']) ?></a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="hide-lg"><?= e($s['alamat'] ?: '-') ?></td>
                            <td class="center">
                                <a href="<?= url('produk/index.php?supplier=' . $s['id']) ?>" title="Lihat produk supplier ini">
                                    <span class="badge <?= (int) $s['jumlah_produk'] > 0 ? 'primary' : '' ?>"><?= (int) $s['jumlah_produk'] ?> produk</span>
                                </a>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a class="icon-btn" href="<?= url('produk/index.php?supplier=' . $s['id']) ?>" title="Lihat produk">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/><circle cx="12" cy="12" r="3"/></svg>
                                    </a>
                                    <a class="icon-btn primary" href="<?= url('supplier/form.php?id=' . $s['id']) ?>" title="Edit">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.83 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"/></svg>
                                    </a>
                                    <form method="post" action="<?= url('supplier/hapus.php') ?>" style="display:inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $s['id'] ?>">
                                        <button class="icon-btn danger" type="submit" title="Hapus"
                                                data-confirm="Hapus supplier <?= e($s['nama_supplier']) ?>?">
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

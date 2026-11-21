<?php
/**
 * Daftar produk + pencarian, filter kategori/supplier, pagination.
 */
require __DIR__ . '/../config/config.php';

$q          = trim((string) ($_GET['q'] ?? ''));
$kategoriId = (int) ($_GET['kategori'] ?? 0);
$supplierId = (int) ($_GET['supplier'] ?? 0);
$filterStok = (string) ($_GET['stok'] ?? '');
$halaman    = max(1, (int) ($_GET['halaman'] ?? 1));

$where  = [];
$params = [];

if ($q !== '') {
    $where[] = '(p.nama_produk LIKE :q_nama OR p.deskripsi LIKE :q_desc)';
    $params['q_nama'] = '%' . $q . '%';
    $params['q_desc'] = '%' . $q . '%';
}
if ($kategoriId > 0) {
    $where[] = 'p.kategori_id = :kategori';
    $params['kategori'] = $kategoriId;
}
if ($supplierId > 0) {
    $where[] = 'p.supplier_id = :supplier';
    $params['supplier'] = $supplierId;
}
if ($filterStok === 'menipis') {
    $where[] = 'p.stok <= ' . STOK_MENIPIS;
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = $pdo->prepare("SELECT COUNT(*) FROM produk p $whereSql");
$stmt->execute($params);
$total = (int) $stmt->fetchColumn();

$page = paginate($total, 10, $halaman);

$stmt = $pdo->prepare(
    "SELECT p.*, k.nama_kategori, s.nama_supplier
     FROM produk p
     JOIN kategori k ON k.id = p.kategori_id
     JOIN supplier s ON s.id = p.supplier_id
     $whereSql
     ORDER BY p.nama_produk ASC
     LIMIT {$page['perPage']} OFFSET {$page['offset']}"
);
$stmt->execute($params);
$daftarProduk = $stmt->fetchAll();

$daftarKategori = $pdo->query('SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori')->fetchAll();
$daftarSupplier = $pdo->query('SELECT id, nama_supplier FROM supplier ORDER BY nama_supplier')->fetchAll();

$adaFilter = $q !== '' || $kategoriId > 0 || $supplierId > 0 || $filterStok !== '';

$pageTitle    = 'Produk';
$pageSubtitle = 'Kelola produk frozen food, harga, dan stok';
$active       = 'produk';
require __DIR__ . '/../includes/header.php';
?>

<div class="page-actions">
    <form class="filter-form" method="get" action="<?= url('produk/index.php') ?>">
        <input class="form-control" type="search" name="q" value="<?= e($q) ?>" placeholder="Cari produk..." style="min-width: 190px">
        <select class="form-control" name="kategori" style="min-width: 160px">
            <option value="">Semua kategori</option>
            <?php foreach ($daftarKategori as $k): ?>
                <option value="<?= (int) $k['id'] ?>" <?= $kategoriId === (int) $k['id'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
            <?php endforeach; ?>
        </select>
        <select class="form-control" name="supplier" style="min-width: 170px">
            <option value="">Semua supplier</option>
            <?php foreach ($daftarSupplier as $s): ?>
                <option value="<?= (int) $s['id'] ?>" <?= $supplierId === (int) $s['id'] ? 'selected' : '' ?>><?= e($s['nama_supplier']) ?></option>
            <?php endforeach; ?>
        </select>
        <label class="check-inline">
            <input type="checkbox" name="stok" value="menipis" <?= $filterStok === 'menipis' ? 'checked' : '' ?>>
            Stok menipis
        </label>
        <button class="btn btn-outline" type="submit">Terapkan</button>
        <?php if ($adaFilter): ?>
            <a class="btn btn-outline" href="<?= url('produk/index.php') ?>">Reset</a>
        <?php endif; ?>
    </form>
    <a class="btn btn-primary spacer" href="<?= url('produk/form.php') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Tambah Produk
    </a>
</div>

<?php if ($kategoriId > 0 || $supplierId > 0): ?>
    <div class="info-tip">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
        <span>
            Menampilkan produk
            <?php if ($kategoriId > 0): ?>dengan kategori <strong><?= e((string) (array_column($daftarKategori, 'nama_kategori', 'id')[$kategoriId] ?? '')) ?></strong><?php endif; ?>
            <?php if ($kategoriId > 0 && $supplierId > 0): ?> dan <?php endif; ?>
            <?php if ($supplierId > 0): ?>dari supplier <strong><?= e((string) (array_column($daftarSupplier, 'nama_supplier', 'id')[$supplierId] ?? '')) ?></strong><?php endif; ?>.
        </span>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-head">
        <div>
            <h2>Daftar Produk</h2>
            <p><?= number_format($total, 0, ',', '.') ?> produk ditemukan</p>
        </div>
    </div>

    <div class="card-body flush">
        <?php if (!$daftarProduk): ?>
            <div class="empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/></svg>
                <h3>Produk tidak ditemukan</h3>
                <p>Ubah filter pencarian atau tambahkan produk baru.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th class="hide-lg">Supplier</th>
                            <th class="num">Harga</th>
                            <th class="center">Stok</th>
                            <th class="text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($daftarProduk as $p): ?>
                        <?php $status = status_stok((int) $p['stok']); ?>
                        <tr>
                            <td>
                                <div class="cell-main">
                                    <span class="produk-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/></svg></span>
                                    <div>
                                        <strong><?= e($p['nama_produk']) ?></strong>
                                        <small><?= e($p['deskripsi'] ?: $p['nama_supplier']) ?></small>
                                    </div>
                                </div>
                            </td>
                            <td class="nowrap"><span class="badge primary"><?= e($p['nama_kategori']) ?></span></td>
                            <td class="hide-lg"><?= e($p['nama_supplier']) ?></td>
                            <td class="num harga"><?= rupiah($p['harga']) ?></td>
                            <td class="center nowrap">
                                <span class="badge <?= $status['warna'] ?>" title="Status: <?= $status['label'] ?>">
                                    <?= (int) $p['stok'] ?> <?= e($p['satuan']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="row-actions">
                                    <a class="icon-btn primary" href="<?= url('produk/form.php?id=' . $p['id']) ?>" title="Edit produk">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M17 3a2.83 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5z"/></svg>
                                    </a>
                                    <form method="post" action="<?= url('produk/hapus.php') ?>" style="display:inline">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= (int) $p['id'] ?>">
                                        <button class="icon-btn danger" type="submit" title="Hapus"
                                                data-confirm="Hapus produk <?= e($p['nama_produk']) ?>? Tindakan ini tidak dapat dibatalkan.">
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

            <?php if ($page['totalPage'] > 1): ?>
                <div class="pagination">
                    <span class="info">Menampilkan <?= $page['from'] ?>–<?= $page['to'] ?> dari <?= $page['total'] ?> produk</span>
                    <a class="<?= $page['page'] <= 1 ? 'disabled' : '' ?>" href="<?= e(halaman_url(max(1, $page['page'] - 1))) ?>">&laquo;</a>
                    <?php for ($i = 1; $i <= $page['totalPage']; $i++): ?>
                        <?php if ($i === 1 || $i === $page['totalPage'] || abs($i - $page['page']) <= 1): ?>
                            <a class="<?= $i === $page['page'] ? 'active' : '' ?>" href="<?= e(halaman_url($i)) ?>"><?= $i ?></a>
                        <?php elseif (abs($i - $page['page']) === 2): ?>
                            <span class="disabled">&hellip;</span>
                        <?php endif; ?>
                    <?php endfor; ?>
                    <a class="<?= $page['page'] >= $page['totalPage'] ? 'disabled' : '' ?>" href="<?= e(halaman_url(min($page['totalPage'], $page['page'] + 1))) ?>">&raquo;</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>

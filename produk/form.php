<?php
/**
 * Form tambah / edit produk.
 */
require __DIR__ . '/../config/config.php';

$id     = (int) ($_GET['id'] ?? 0);
$isEdit = $id > 0;

$data = [
    'nama_produk' => '',
    'kategori_id' => '',
    'supplier_id' => '',
    'harga'       => '',
    'stok'        => '0',
    'satuan'      => 'pcs',
    'deskripsi'   => '',
];

if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM produk WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $produk = $stmt->fetch();

    if (!$produk) {
        set_flash('danger', 'Data produk tidak ditemukan.');
        redirect('produk/index.php');
    }
    $data = $produk;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $data['nama_produk'] = trim((string) ($_POST['nama_produk'] ?? ''));
    $data['kategori_id'] = (string) ($_POST['kategori_id'] ?? '');
    $data['supplier_id'] = (string) ($_POST['supplier_id'] ?? '');
    $data['harga']       = trim((string) ($_POST['harga'] ?? ''));
    $data['stok']        = trim((string) ($_POST['stok'] ?? ''));
    $data['satuan']      = (string) ($_POST['satuan'] ?? 'pcs');
    $data['deskripsi']   = trim((string) ($_POST['deskripsi'] ?? ''));

    // --- validasi ---
    if ($data['nama_produk'] === '') {
        $errors['nama_produk'] = 'Nama produk wajib diisi.';
    } elseif (mb_strlen($data['nama_produk']) > 100) {
        $errors['nama_produk'] = 'Nama produk maksimal 100 karakter.';
    }

    if ($data['kategori_id'] === '') {
        $errors['kategori_id'] = 'Pilih kategori produk.';
    } else {
        $cek = $pdo->prepare('SELECT id FROM kategori WHERE id = :id LIMIT 1');
        $cek->execute(['id' => (int) $data['kategori_id']]);
        if (!$cek->fetch()) {
            $errors['kategori_id'] = 'Kategori tidak ditemukan.';
        }
    }

    if ($data['supplier_id'] === '') {
        $errors['supplier_id'] = 'Pilih supplier produk.';
    } else {
        $cek = $pdo->prepare('SELECT id FROM supplier WHERE id = :id LIMIT 1');
        $cek->execute(['id' => (int) $data['supplier_id']]);
        if (!$cek->fetch()) {
            $errors['supplier_id'] = 'Supplier tidak ditemukan.';
        }
    }

    if ($data['harga'] === '' || !is_numeric($data['harga'])) {
        $errors['harga'] = 'Harga wajib berupa angka.';
    } elseif ((float) $data['harga'] < 0) {
        $errors['harga'] = 'Harga tidak boleh negatif.';
    } elseif ((float) $data['harga'] > 9999999999) {
        $errors['harga'] = 'Harga terlalu besar.';
    }

    if ($data['stok'] === '' || !preg_match('/^\d+$/', $data['stok'])) {
        $errors['stok'] = 'Stok wajib berupa bilangan bulat 0 atau lebih.';
    } elseif ((int) $data['stok'] > 1000000) {
        $errors['stok'] = 'Jumlah stok terlalu besar.';
    }

    if (!in_array($data['satuan'], ['pcs', 'pack', 'kg', 'box'], true)) {
        $errors['satuan'] = 'Satuan tidak valid.';
    }

    if (!$errors) {
        $params = [
            'nama_produk' => $data['nama_produk'],
            'kategori_id' => (int) $data['kategori_id'],
            'supplier_id' => (int) $data['supplier_id'],
            'harga'       => (float) $data['harga'],
            'stok'        => (int) $data['stok'],
            'satuan'      => $data['satuan'],
            'deskripsi'   => $data['deskripsi'] !== '' ? $data['deskripsi'] : null,
        ];

        if ($isEdit) {
            $params['id'] = $id;
            $stmt = $pdo->prepare(
                'UPDATE produk SET nama_produk = :nama_produk, kategori_id = :kategori_id,
                        supplier_id = :supplier_id, harga = :harga, stok = :stok,
                        satuan = :satuan, deskripsi = :deskripsi
                 WHERE id = :id'
            );
            $stmt->execute($params);
            set_flash('success', 'Produk "' . $data['nama_produk'] . '" berhasil diperbarui.');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO produk (nama_produk, kategori_id, supplier_id, harga, stok, satuan, deskripsi)
                 VALUES (:nama_produk, :kategori_id, :supplier_id, :harga, :stok, :satuan, :deskripsi)'
            );
            $stmt->execute($params);
            set_flash('success', 'Produk "' . $data['nama_produk'] . '" berhasil ditambahkan.');
        }

        redirect('produk/index.php');
    }
}

$daftarKategori = $pdo->query('SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori')->fetchAll();
$daftarSupplier = $pdo->query('SELECT id, nama_supplier FROM supplier ORDER BY nama_supplier')->fetchAll();

$pageTitle    = $isEdit ? 'Edit Produk' : 'Tambah Produk';
$pageSubtitle = $isEdit ? 'Perbarui data produk' : 'Masukkan produk frozen food baru';
$active       = 'produk';
require __DIR__ . '/../includes/header.php';
?>

<div class="page-actions">
    <a class="btn btn-outline" href="<?= url('produk/index.php') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Kembali
    </a>
</div>

<?php if (!$daftarKategori || !$daftarSupplier): ?>
    <div class="info-tip" style="background:var(--warning-soft);border-color:#fde68a;color:#92400e">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
        <span>
            <?php if (!$daftarKategori): ?>Belum ada kategori. <a href="<?= url('kategori/form.php') ?>">Tambah kategori</a> terlebih dahulu.<?php endif; ?>
            <?php if (!$daftarKategori && !$daftarSupplier): ?> <br><?php endif; ?>
            <?php if (!$daftarSupplier): ?>Belum ada supplier. <a href="<?= url('supplier/form.php') ?>">Tambah supplier</a> terlebih dahulu.<?php endif; ?>
        </span>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-head">
        <div>
            <h2><?= $isEdit ? 'Form Edit Produk' : 'Form Produk Baru' ?></h2>
            <p>Kolom bertanda <span style="color: var(--danger)">*</span> wajib diisi</p>
        </div>
    </div>
    <div class="card-body">
        <form method="post" action="<?= url('produk/form.php' . ($isEdit ? '?id=' . $id : '')) ?>" novalidate>
            <?= csrf_field() ?>

            <div class="form-grid">
                <div class="form-group full">
                    <label class="form-label" for="nama_produk">Nama Produk <span class="req">*</span></label>
                    <input class="form-control <?= isset($errors['nama_produk']) ? 'is-invalid' : '' ?>" type="text" id="nama_produk" name="nama_produk"
                           value="<?= e((string) $data['nama_produk']) ?>" maxlength="100" placeholder="Contoh: Nugget Ayam 500 gram" required>
                    <?php if (isset($errors['nama_produk'])): ?><span class="form-error"><?= e($errors['nama_produk']) ?></span><?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="kategori_id">Kategori <span class="req">*</span></label>
                    <select class="form-control <?= isset($errors['kategori_id']) ? 'is-invalid' : '' ?>" id="kategori_id" name="kategori_id" required>
                        <option value="">-- Pilih kategori --</option>
                        <?php foreach ($daftarKategori as $k): ?>
                            <option value="<?= (int) $k['id'] ?>" <?= (string) $data['kategori_id'] === (string) $k['id'] ? 'selected' : '' ?>><?= e($k['nama_kategori']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['kategori_id'])): ?><span class="form-error"><?= e($errors['kategori_id']) ?></span><?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="supplier_id">Supplier <span class="req">*</span></label>
                    <select class="form-control <?= isset($errors['supplier_id']) ? 'is-invalid' : '' ?>" id="supplier_id" name="supplier_id" required>
                        <option value="">-- Pilih supplier --</option>
                        <?php foreach ($daftarSupplier as $s): ?>
                            <option value="<?= (int) $s['id'] ?>" <?= (string) $data['supplier_id'] === (string) $s['id'] ? 'selected' : '' ?>><?= e($s['nama_supplier']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['supplier_id'])): ?><span class="form-error"><?= e($errors['supplier_id']) ?></span><?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="harga">Harga Satuan (Rp) <span class="req">*</span></label>
                    <input class="form-control <?= isset($errors['harga']) ? 'is-invalid' : '' ?>" type="number" id="harga" name="harga"
                           value="<?= e((string) $data['harga']) ?>" min="0" step="100" placeholder="Contoh: 32000" required>
                    <?php if (isset($errors['harga'])): ?><span class="form-error"><?= e($errors['harga']) ?></span><?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="satuan">Satuan <span class="req">*</span></label>
                    <select class="form-control <?= isset($errors['satuan']) ? 'is-invalid' : '' ?>" id="satuan" name="satuan">
                        <?php foreach (['pcs' => 'pcs (per buah)', 'pack' => 'pack (per kemasan)', 'kg' => 'kg (per kilogram)', 'box' => 'box (per kotak)'] as $nilai => $label): ?>
                            <option value="<?= $nilai ?>" <?= $data['satuan'] === $nilai ? 'selected' : '' ?>><?= $label ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['satuan'])): ?><span class="form-error"><?= e($errors['satuan']) ?></span><?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="stok">Stok <span class="req">*</span></label>
                    <input class="form-control <?= isset($errors['stok']) ? 'is-invalid' : '' ?>" type="number" id="stok" name="stok"
                           value="<?= e((string) $data['stok']) ?>" min="0" step="1" placeholder="Contoh: 20" required>
                    <?php if (isset($errors['stok'])): ?><span class="form-error"><?= e($errors['stok']) ?></span><?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label">Nilai Stok</label>
                    <div class="akhir-box">
                        <span class="label">Harga &times; Stok</span>
                        <span class="value" id="nilaiStokPreview">Rp 0</span>
                    </div>
                </div>

                <div class="form-group full">
                    <label class="form-label" for="deskripsi">Deskripsi <span class="text-muted" style="font-weight:400">(opsional)</span></label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" maxlength="255"
                              placeholder="Keterangan singkat produk, misalnya isi per kemasan atau cara penyajian"><?= e((string) $data['deskripsi']) ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-outline" href="<?= url('produk/index.php') ?>">Batal</a>
                <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan Produk' ?></button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>

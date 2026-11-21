<?php
/**
 * Form tambah / edit supplier.
 */
require __DIR__ . '/../config/config.php';

$id     = (int) ($_GET['id'] ?? 0);
$isEdit = $id > 0;

$data = [
    'nama_supplier' => '',
    'kontak_person' => '',
    'no_hp'         => '',
    'alamat'        => '',
];

if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM supplier WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $supplier = $stmt->fetch();

    if (!$supplier) {
        set_flash('danger', 'Data supplier tidak ditemukan.');
        redirect('supplier/index.php');
    }
    $data = $supplier;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $data['nama_supplier'] = trim((string) ($_POST['nama_supplier'] ?? ''));
    $data['kontak_person'] = trim((string) ($_POST['kontak_person'] ?? ''));
    $data['no_hp']         = trim((string) ($_POST['no_hp'] ?? ''));
    $data['alamat']        = trim((string) ($_POST['alamat'] ?? ''));

    if ($data['nama_supplier'] === '') {
        $errors['nama_supplier'] = 'Nama supplier wajib diisi.';
    } elseif (mb_strlen($data['nama_supplier']) > 100) {
        $errors['nama_supplier'] = 'Nama supplier maksimal 100 karakter.';
    } else {
        $cek = $pdo->prepare('SELECT id FROM supplier WHERE nama_supplier = :nama AND id <> :id LIMIT 1');
        $cek->execute(['nama' => $data['nama_supplier'], 'id' => $id]);
        if ($cek->fetch()) {
            $errors['nama_supplier'] = 'Nama supplier sudah digunakan.';
        }
    }

    if ($data['no_hp'] !== '' && !preg_match('/^[0-9+\-\s]{6,20}$/', $data['no_hp'])) {
        $errors['no_hp'] = 'Nomor HP hanya boleh angka, spasi, +, atau -.';
    }

    if (mb_strlen($data['alamat']) > 255) {
        $errors['alamat'] = 'Alamat maksimal 255 karakter.';
    }

    if (!$errors) {
        $params = [
            'nama_supplier' => $data['nama_supplier'],
            'kontak_person' => $data['kontak_person'] !== '' ? $data['kontak_person'] : null,
            'no_hp'         => $data['no_hp'] !== '' ? $data['no_hp'] : null,
            'alamat'        => $data['alamat'] !== '' ? $data['alamat'] : null,
        ];

        if ($isEdit) {
            $params['id'] = $id;
            $stmt = $pdo->prepare(
                'UPDATE supplier SET nama_supplier = :nama_supplier, kontak_person = :kontak_person,
                        no_hp = :no_hp, alamat = :alamat
                 WHERE id = :id'
            );
            $stmt->execute($params);
            set_flash('success', 'Supplier "' . $data['nama_supplier'] . '" berhasil diperbarui.');
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO supplier (nama_supplier, kontak_person, no_hp, alamat)
                 VALUES (:nama_supplier, :kontak_person, :no_hp, :alamat)'
            );
            $stmt->execute($params);
            set_flash('success', 'Supplier "' . $data['nama_supplier'] . '" berhasil ditambahkan.');
        }

        redirect('supplier/index.php');
    }
}

$pageTitle    = $isEdit ? 'Edit Supplier' : 'Tambah Supplier';
$pageSubtitle = $isEdit ? 'Perbarui data supplier' : 'Daftarkan pemasok baru';
$active       = 'supplier';
require __DIR__ . '/../includes/header.php';
?>

<div class="page-actions">
    <a class="btn btn-outline" href="<?= url('supplier/index.php') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Kembali
    </a>
</div>

<div class="card">
    <div class="card-head">
        <div>
            <h2><?= $isEdit ? 'Form Edit Supplier' : 'Form Supplier Baru' ?></h2>
            <p>Kolom bertanda <span style="color: var(--danger)">*</span> wajib diisi</p>
        </div>
    </div>
    <div class="card-body">
        <form method="post" action="<?= url('supplier/form.php' . ($isEdit ? '?id=' . $id : '')) ?>" novalidate>
            <?= csrf_field() ?>

            <div class="form-grid">
                <div class="form-group full">
                    <label class="form-label" for="nama_supplier">Nama Supplier <span class="req">*</span></label>
                    <input class="form-control <?= isset($errors['nama_supplier']) ? 'is-invalid' : '' ?>" type="text" id="nama_supplier" name="nama_supplier"
                           value="<?= e((string) $data['nama_supplier']) ?>" maxlength="100" placeholder="Contoh: PT Frozen Nusantara" required>
                    <?php if (isset($errors['nama_supplier'])): ?><span class="form-error"><?= e($errors['nama_supplier']) ?></span><?php endif; ?>
                </div>

                <div class="form-group">
                    <label class="form-label" for="kontak_person">Kontak Person</label>
                    <input class="form-control" type="text" id="kontak_person" name="kontak_person"
                           value="<?= e((string) $data['kontak_person']) ?>" maxlength="100" placeholder="Nama penanggung jawab">
                </div>

                <div class="form-group">
                    <label class="form-label" for="no_hp">No. HP</label>
                    <input class="form-control <?= isset($errors['no_hp']) ? 'is-invalid' : '' ?>" type="text" id="no_hp" name="no_hp"
                           value="<?= e((string) $data['no_hp']) ?>" maxlength="20" placeholder="Contoh: 081234567890">
                    <?php if (isset($errors['no_hp'])): ?><span class="form-error"><?= e($errors['no_hp']) ?></span><?php endif; ?>
                </div>

                <div class="form-group full">
                    <label class="form-label" for="alamat">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" maxlength="255"
                              placeholder="Alamat lengkap supplier"><?= e((string) $data['alamat']) ?></textarea>
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-outline" href="<?= url('supplier/index.php') ?>">Batal</a>
                <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan Supplier' ?></button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>

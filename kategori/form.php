<?php
/**
 * Form tambah / edit kategori.
 */
require __DIR__ . '/../config/config.php';

$id     = (int) ($_GET['id'] ?? 0);
$isEdit = $id > 0;

$data = [
    'nama_kategori' => '',
    'deskripsi'     => '',
];

if ($isEdit) {
    $stmt = $pdo->prepare('SELECT * FROM kategori WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $id]);
    $kategori = $stmt->fetch();

    if (!$kategori) {
        set_flash('danger', 'Data kategori tidak ditemukan.');
        redirect('kategori/index.php');
    }
    $data = $kategori;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();

    $data['nama_kategori'] = trim((string) ($_POST['nama_kategori'] ?? ''));
    $data['deskripsi']     = trim((string) ($_POST['deskripsi'] ?? ''));

    if ($data['nama_kategori'] === '') {
        $errors['nama_kategori'] = 'Nama kategori wajib diisi.';
    } elseif (mb_strlen($data['nama_kategori']) > 80) {
        $errors['nama_kategori'] = 'Nama kategori maksimal 80 karakter.';
    } else {
        $cek = $pdo->prepare('SELECT id FROM kategori WHERE nama_kategori = :nama AND id <> :id LIMIT 1');
        $cek->execute(['nama' => $data['nama_kategori'], 'id' => $id]);
        if ($cek->fetch()) {
            $errors['nama_kategori'] = 'Nama kategori sudah digunakan.';
        }
    }

    if (mb_strlen($data['deskripsi']) > 255) {
        $errors['deskripsi'] = 'Deskripsi maksimal 255 karakter.';
    }

    if (!$errors) {
        $params = [
            'nama_kategori' => $data['nama_kategori'],
            'deskripsi'     => $data['deskripsi'] !== '' ? $data['deskripsi'] : null,
        ];

        if ($isEdit) {
            $params['id'] = $id;
            $stmt = $pdo->prepare('UPDATE kategori SET nama_kategori = :nama_kategori, deskripsi = :deskripsi WHERE id = :id');
            $stmt->execute($params);
            set_flash('success', 'Kategori "' . $data['nama_kategori'] . '" berhasil diperbarui.');
        } else {
            $stmt = $pdo->prepare('INSERT INTO kategori (nama_kategori, deskripsi) VALUES (:nama_kategori, :deskripsi)');
            $stmt->execute($params);
            set_flash('success', 'Kategori "' . $data['nama_kategori'] . '" berhasil ditambahkan.');
        }

        redirect('kategori/index.php');
    }
}

$pageTitle    = $isEdit ? 'Edit Kategori' : 'Tambah Kategori';
$pageSubtitle = $isEdit ? 'Perbarui data kategori' : 'Buat kelompok produk baru';
$active       = 'kategori';
require __DIR__ . '/../includes/header.php';
?>

<div class="page-actions">
    <a class="btn btn-outline" href="<?= url('kategori/index.php') ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
        Kembali
    </a>
</div>

<div class="card">
    <div class="card-head">
        <div>
            <h2><?= $isEdit ? 'Form Edit Kategori' : 'Form Kategori Baru' ?></h2>
            <p>Kolom bertanda <span style="color: var(--danger)">*</span> wajib diisi</p>
        </div>
    </div>
    <div class="card-body">
        <form method="post" action="<?= url('kategori/form.php' . ($isEdit ? '?id=' . $id : '')) ?>" novalidate>
            <?= csrf_field() ?>

            <div class="form-grid">
                <div class="form-group full">
                    <label class="form-label" for="nama_kategori">Nama Kategori <span class="req">*</span></label>
                    <input class="form-control <?= isset($errors['nama_kategori']) ? 'is-invalid' : '' ?>" type="text" id="nama_kategori" name="nama_kategori"
                           value="<?= e((string) $data['nama_kategori']) ?>" maxlength="80" placeholder="Contoh: Nugget & Sosis" required>
                    <?php if (isset($errors['nama_kategori'])): ?><span class="form-error"><?= e($errors['nama_kategori']) ?></span><?php endif; ?>
                </div>

                <div class="form-group full">
                    <label class="form-label" for="deskripsi">Deskripsi</label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" maxlength="255"
                              placeholder="Keterangan singkat mengenai kategori ini"><?= e((string) $data['deskripsi']) ?></textarea>
                    <span class="form-hint">Opsional, maksimal 255 karakter.</span>
                    <?php if (isset($errors['deskripsi'])): ?><span class="form-error"><?= e($errors['deskripsi']) ?></span><?php endif; ?>
                </div>
            </div>

            <div class="form-actions">
                <a class="btn btn-outline" href="<?= url('kategori/index.php') ?>">Batal</a>
                <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Simpan Perubahan' : 'Simpan Kategori' ?></button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>

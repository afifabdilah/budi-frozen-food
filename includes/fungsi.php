<?php
/**
 * Kumpulan fungsi pembantu yang dipakai di seluruh aplikasi.
 */
declare(strict_types=1);

/** Escape teks supaya aman ditampilkan di HTML. */
function e(?string $text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

/** Membuat URL lengkap dari path relatif terhadap folder aplikasi. */
function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

/** Redirect lalu hentikan eksekusi. */
function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

/** Simpan pesan sekali tampil ke session. */
function set_flash(string $tipe, string $pesan): void
{
    $_SESSION['flash'] = ['tipe' => $tipe, 'pesan' => $pesan];
}

/** Ambil (dan hapus) pesan flash. */
function get_flash(): ?array
{
    if (empty($_SESSION['flash'])) {
        return null;
    }
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

/** Ambil token CSRF untuk session ini. */
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/** Cetak input hidden berisi token CSRF. */
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/** Hentikan request jika token CSRF tidak cocok. */
function csrf_check(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals(csrf_token(), $token)) {
        http_response_code(419);
        exit('Sesi kedaluwarsa atau token keamanan tidak valid. Silakan muat ulang halaman.');
    }
}

/** Format angka menjadi Rupiah: Rp 32.000 */
function rupiah(float|int|string|null $nilai): string
{
    return 'Rp ' . number_format((float) $nilai, 0, ',', '.');
}

/** Format tanggal database (Y-m-d) menjadi 12 Maret 2008. */
function tanggal_id(?string $tanggal): string
{
    if (!$tanggal) {
        return '-';
    }
    $bulan = [
        1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember',
    ];
    $ts = strtotime($tanggal);
    if ($ts === false) {
        return (string) $tanggal;
    }
    return date('j', $ts) . ' ' . $bulan[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

/** Status stok produk: label + warna badge. */
function status_stok(int $stok): array
{
    if ($stok <= 0) {
        return ['label' => 'Habis', 'warna' => 'danger'];
    }
    if ($stok <= STOK_MENIPIS) {
        return ['label' => 'Menipis', 'warna' => 'warning'];
    }
    return ['label' => 'Tersedia', 'warna' => 'success'];
}

/** Inisial nama untuk avatar (maks 2 huruf). */
function inisial(string $nama): string
{
    $kata  = preg_split('/\s+/', trim($nama)) ?: [];
    $huruf = '';
    foreach (array_slice($kata, 0, 2) as $k) {
        $huruf .= mb_strtoupper(mb_substr($k, 0, 1));
    }
    return $huruf ?: '?';
}

/**
 * Buat data pagination.
 * @return array{page:int, perPage:int, total:int, totalPage:int, offset:int, from:int, to:int}
 */
function paginate(int $total, int $perPage, int $page): array
{
    $perPage   = max(1, $perPage);
    $totalPage = max(1, (int) ceil($total / $perPage));
    $page      = min(max(1, $page), $totalPage);
    $offset    = ($page - 1) * $perPage;

    return [
        'page'      => $page,
        'perPage'   => $perPage,
        'total'     => $total,
        'totalPage' => $totalPage,
        'offset'    => $offset,
        'from'      => $total === 0 ? 0 : $offset + 1,
        'to'        => min($offset + $perPage, $total),
    ];
}

/** Bangun URL halaman dengan mempertahankan query string yang ada. */
function halaman_url(int $halaman): string
{
    $query = $_GET;
    $query['halaman'] = $halaman;
    return '?' . http_build_query($query);
}

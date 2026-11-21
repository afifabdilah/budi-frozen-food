<?php
/**
 * Bagian atas layout (sidebar + topbar).
 * Sebelum include: set $pageTitle, $active, opsional $pageSubtitle.
 */
if (!defined('APP_NAME')) {
    http_response_code(403);
    exit('Akses langsung tidak diizinkan.');
}

$pageTitle    = $pageTitle ?? 'Dashboard';
$pageSubtitle = $pageSubtitle ?? '';
$active       = $active ?? '';
$flash        = get_flash();

$menu = [
    ['key' => 'dashboard', 'label' => 'Dashboard',   'href' => 'index.php',           'icon' => 'grid'],
    ['key' => 'produk',    'label' => 'Produk',      'href' => 'produk/index.php',    'icon' => 'box'],
    ['key' => 'kategori',  'label' => 'Kategori',    'href' => 'kategori/index.php',  'icon' => 'tag'],
    ['key' => 'supplier',  'label' => 'Supplier',    'href' => 'supplier/index.php',  'icon' => 'truck'],
];

$notifStok = (int) $pdo->query('SELECT COUNT(*) FROM produk WHERE stok <= ' . STOK_MENIPIS)->fetchColumn();

$icons = [
    'grid'  => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
    'box'   => '<path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/>',
    'tag'   => '<path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/>',
    'truck' => '<rect x="1" y="3" width="15" height="13" rx="1"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/>',
];
?>
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> &middot; <?= APP_NAME ?></title>
    <link rel="stylesheet" href="<?= url('assets/css/style.css') ?>?v=1.1">
</head>
<body>
<div class="app">
    <aside class="sidebar" id="sidebar">
        <div class="brand">
            <div class="brand-logo">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="2" x2="12" y2="22"/><line x1="3.34" y1="7" x2="20.66" y2="17"/><line x1="20.66" y1="7" x2="3.34" y2="17"/><path d="M12 6l-2.5-2.5M12 6l2.5-2.5M12 18l-2.5 2.5M12 18l2.5 2.5"/></svg>
            </div>
            <div class="brand-text">
                <strong>BUDI FROZEN</strong>
                <span>Produk Frozen Food</span>
            </div>
        </div>

        <nav class="nav">
            <p class="nav-label">Menu Utama</p>
            <?php foreach ($menu as $item): ?>
                <a class="nav-link <?= $active === $item['key'] ? 'active' : '' ?>" href="<?= url($item['href']) ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $icons[$item['icon']] ?></svg>
                    <span><?= e($item['label']) ?></span>
                    <?php if ($item['key'] === 'produk' && $notifStok > 0): ?>
                        <span class="nav-badge"><?= $notifStok ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <div class="sidebar-foot">
            <div class="user-chip">
                <span class="avatar">BF</span>
                <span class="user-chip-text">
                    <strong><?= TOKO_NAMA ?></strong>
                    <small>Admin toko</small>
                </span>
            </div>
        </div>
    </aside>

    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <main class="main">
        <header class="topbar">
            <button class="menu-toggle" id="menuToggle" type="button" aria-label="Buka menu">
                <span></span><span></span><span></span>
            </button>
            <div class="topbar-title">
                <h1><?= e($pageTitle) ?></h1>
                <?php if ($pageSubtitle !== ''): ?><p><?= e($pageSubtitle) ?></p><?php endif; ?>
            </div>
            <div class="topbar-right">
                <span class="date-chip"><?= tanggal_id(date('Y-m-d')) ?></span>
            </div>
        </header>

        <div class="content">
            <?php if ($flash): ?>
                <div class="alert alert-<?= e($flash['tipe']) ?>" data-auto-dismiss>
                    <span><?= e($flash['pesan']) ?></span>
                    <button type="button" class="alert-close" aria-label="Tutup">&times;</button>
                </div>
            <?php endif; ?>

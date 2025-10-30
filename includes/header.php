<?php
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/paths.php';
requireLogin();

$currentPage = basename($_SERVER['PHP_SELF']);
$isAdmin = isAdmin();
$isKasir = isKasir();

// Hitung relative path ke assets berdasarkan lokasi file
$assetPath = getAssetPath('');
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle : 'Kasir SAKPORE'; ?></title>
    <link rel="stylesheet" href="<?php echo $assetPath; ?>css/style.css">
    <link rel="shortcut icon" href="<?php echo $assetPath; ?>image/kasir.png">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-left">
                <button class="menu-toggle" id="menuToggle">☰</button>
                <h1><?php echo $isAdmin ? 'ADMINE SAKPORE' : 'KASIR SAKPORE'; ?></h1>
            </div>
            <div class="header-right">
                <span><?php echo $_SESSION['username']; ?></span>
                <span>Posisi: <?php echo $_SESSION['jabatan']; ?></span>
                <a href="<?php echo getNavPath('logout.php'); ?>" class="btn-logout">LOGOUT</a>
            </div>
        </header>

        <div class="wrapper">
            <aside class="sidebar" id="sidebar">
                <nav class="nav-menu">
                    <?php if ($isAdmin): ?>
                        <a href="<?php echo getNavPath('admin/dashboard.php'); ?>" class="nav-item <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">
                            <span>🏠</span> BERANDA
                        </a>
                        <a href="<?php echo getNavPath('admin/daftar_barang/index.php'); ?>" class="nav-item <?php echo strpos($currentPage, 'daftar_barang') !== false ? 'active' : ''; ?>">
                            <span>📦</span> DAFTAR BARANG
                        </a>
                        <a href="<?php echo getNavPath('admin/kategori_barang/index.php'); ?>" class="nav-item <?php echo strpos($currentPage, 'kategori_barang') !== false ? 'active' : ''; ?>">
                            <span>🏷️</span> KATEGORI BARANG
                        </a>
                        <a href="<?php echo getNavPath('admin/laporan/keuangan.php'); ?>" class="nav-item <?php echo strpos($currentPage, 'keuangan') !== false ? 'active' : ''; ?>">
                            <span>📊</span> LAPORAN KEUANGAN
                        </a>
                        <a href="<?php echo getNavPath('admin/laporan/transaksi.php'); ?>" class="nav-item <?php echo strpos($currentPage, 'transaksi') !== false && strpos($currentPage, 'laporan') !== false ? 'active' : ''; ?>">
                            <span>📋</span> LAPORAN TRANSAKSI
                        </a>
                        <a href="<?php echo getNavPath('admin/laporan/pembelian.php'); ?>" class="nav-item <?php echo strpos($currentPage, 'pembelian') !== false && strpos($currentPage, 'laporan') !== false ? 'active' : ''; ?>">
                            <span>📑</span> LAPORAN PEMBELIAN
                        </a>
                        <a href="<?php echo getNavPath('admin/pembelian_barang/index.php'); ?>" class="nav-item <?php echo strpos($currentPage, 'pembelian_barang') !== false ? 'active' : ''; ?>">
                            <span>🛒</span> PEMBELIAN BRG
                        </a>
                        <a href="<?php echo getNavPath('admin/user/index.php'); ?>" class="nav-item <?php echo strpos($currentPage, 'user') !== false ? 'active' : ''; ?>">
                            <span>👥</span> DAFTAR USER
                        </a>
                        <a href="<?php echo getNavPath('admin/metode_pembayaran/index.php'); ?>" class="nav-item <?php echo strpos($currentPage, 'metode_pembayaran') !== false ? 'active' : ''; ?>">
                            <span>💳</span> METODE PEMBAYARAN
                        </a>
                    <?php else: ?>
                        <a href="<?php echo getNavPath('kasir/dashboard.php'); ?>" class="nav-item <?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">
                            <span>🏠</span> BERANDA
                        </a>
                        <a href="<?php echo getNavPath('kasir/transaksi.php'); ?>" class="nav-item <?php echo $currentPage == 'transaksi.php' ? 'active' : ''; ?>">
                            <span>💰</span> TRANSAKSI
                        </a>
                        <a href="<?php echo getNavPath('kasir/daftar_barang.php'); ?>" class="nav-item <?php echo $currentPage == 'daftar_barang.php' ? 'active' : ''; ?>">
                            <span>📦</span> DAFTAR BARANG
                        </a>
                    <?php endif; ?>
                </nav>
                <div class="nav-shortcut">
                    <small>[ALT + 1,2,3...) PINDAH TAP HALAMAN</small>
                </div>
            </aside>

            <main class="main-content">

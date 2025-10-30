<?php
// Start session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fungsi untuk mendapatkan relative path ke root
function getRelativePathToRoot() {
    $scriptPath = $_SERVER['SCRIPT_NAME'];
    $depth = substr_count(dirname($scriptPath), '/');
    
    if ($depth <= 1) {
        return '';
    }
    
    $path = '';
    for ($i = 1; $i < $depth; $i++) {
        $path .= '../';
    }
    return rtrim($path, '/');
}

// Fungsi untuk check login
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['username']);
}

// Fungsi untuk check role
function isAdmin() {
    return isset($_SESSION['jabatan']) && $_SESSION['jabatan'] === 'Admin';
}

function isKasir() {
    return isset($_SESSION['jabatan']) && $_SESSION['jabatan'] === 'Kasir';
}

// Fungsi untuk redirect jika belum login
function requireLogin() {
    if (!isLoggedIn()) {
        $base = getRelativePathToRoot();
        header('Location: ' . ($base ? $base . '/' : '') . 'login.php');
        exit();
    }
}

// Fungsi untuk redirect jika bukan admin
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        $base = getRelativePathToRoot();
        header('Location: ' . ($base ? $base . '/' : '') . 'kasir/dashboard.php');
        exit();
    }
}

// Fungsi untuk redirect jika bukan kasir
function requireKasir() {
    requireLogin();
    if (!isKasir()) {
        $base = getRelativePathToRoot();
        header('Location: ' . ($base ? $base . '/' : '') . 'admin/dashboard.php');
        exit();
    }
}


<?php
require_once __DIR__ . '/../config/database.php';

// Fungsi untuk format rupiah
function formatRupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Fungsi untuk sanitize input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Fungsi untuk generate ID transaksi
function generateIdTransaksi() {
    return 'TR-' . date('YmdHis');
}

// Fungsi untuk generate ID barang
function generateIdBarang() {
    $conn = getConnection();
    $sql = "SELECT COUNT(*) as total FROM daftar_barang";
    $result = queryOne($sql);
    $total = $result['total'] + 1;
    mysqli_close($conn);
    return 'brg' . str_pad($total, 3, '0', STR_PAD_LEFT);
}

// Fungsi untuk generate ID kategori
function generateIdKategori() {
    $conn = getConnection();
    $sql = "SELECT COUNT(*) as total FROM kategori_barang";
    $result = queryOne($sql);
    $total = $result['total'] + 1;
    mysqli_close($conn);
    return str_pad($total, 3, '0', STR_PAD_LEFT);
}

// Fungsi untuk validasi login
function validateLogin($username, $password) {
    $conn = getConnection();
    $username = mysqli_real_escape_string($conn, $username);
    $password = mysqli_real_escape_string($conn, $password);
    
    $sql = "SELECT * FROM user WHERE username = '$username' AND password = '$password'";
    $result = queryOne($sql);
    mysqli_close($conn);
    
    return $result;
}

// Fungsi untuk mendapatkan stok barang
function getStokBarang($id_barang) {
    $conn = getConnection();
    $id_barang = mysqli_real_escape_string($conn, $id_barang);
    $sql = "SELECT stok FROM daftar_barang WHERE id_barang = '$id_barang'";
    $result = queryOne($sql);
    mysqli_close($conn);
    return $result ? $result['stok'] : 0;
}

// Fungsi untuk update stok barang
function updateStokBarang($id_barang, $jumlah) {
    $conn = getConnection();
    $id_barang = mysqli_real_escape_string($conn, $id_barang);
    $jumlah = (int)$jumlah;
    
    $sql = "UPDATE daftar_barang SET stok = stok - $jumlah WHERE id_barang = '$id_barang'";
    $result = mysqli_query($conn, $sql);
    mysqli_close($conn);
    return $result;
}


<?php
require_once __DIR__ . '/../config/database.php';

header('Content-Type: application/json');

$metode = $_GET['metode'] ?? '';

if (!empty($metode)) {
    $conn = getConnection();
    $metode = mysqli_real_escape_string($conn, $metode);
    $akun = queryArray("SELECT * FROM akun_pembayaran WHERE metode_pembayaran = '$metode' ORDER BY nama_pembayaran");
    mysqli_close($conn);
    echo json_encode($akun);
} else {
    echo json_encode([]);
}


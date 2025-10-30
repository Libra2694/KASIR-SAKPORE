<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'kasir_sakpore');

// Koneksi Database
function getConnection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if (!$conn) {
        die("Koneksi gagal: " . mysqli_connect_error());
    }
    
    mysqli_set_charset($conn, "utf8");
    return $conn;
}

// Fungsi untuk melakukan query
function query($sql) {
    $conn = getConnection();
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        $error = mysqli_error($conn);
        mysqli_close($conn);
        die("Query error: " . $error);
    }
    
    mysqli_close($conn);
    return $result;
}

// Fungsi untuk mendapatkan data array
function queryArray($sql) {
    $conn = getConnection();
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        $error = mysqli_error($conn);
        mysqli_close($conn);
        die("Query error: " . $error);
    }
    
    $data = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    
    mysqli_close($conn);
    return $data;
}

// Fungsi untuk mendapatkan satu baris data
function queryOne($sql) {
    $conn = getConnection();
    $result = mysqli_query($conn, $sql);
    
    if (!$result) {
        $error = mysqli_error($conn);
        mysqli_close($conn);
        die("Query error: " . $error);
    }
    
    $row = mysqli_fetch_assoc($result);
    mysqli_close($conn);
    return $row;
}


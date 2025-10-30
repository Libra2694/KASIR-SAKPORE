<?php
// Fungsi untuk mendapatkan base URL
function getBaseUrl() {
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    $host = $_SERVER['HTTP_HOST'];
    $script = $_SERVER['SCRIPT_NAME'];
    
    // Hapus nama file dari path
    $path = dirname($script);
    
    // Jika berada di subfolder, ambil path tersebut
    $pathParts = explode('/', trim($path, '/'));
    
    // Cari folder random atau folder project
    $basePath = '';
    foreach ($pathParts as $part) {
        if (!empty($part) && $part !== 'admin' && $part !== 'kasir' && $part !== 'config' && $part !== 'includes' && $part !== 'assets') {
            $basePath .= '/' . $part;
        }
    }
    
    // Jika tidak ada subfolder, gunakan root
    if (empty($basePath)) {
        $basePath = '/';
    } else {
        $basePath = rtrim($basePath, '/');
    }
    
    return $protocol . $host . $basePath;
}

// Fungsi untuk mendapatkan path relatif ke assets
function getAssetPath($path) {
    // Deteksi lokasi file yang memanggil
    $backtrace = debug_backtrace();
    $caller = $backtrace[0];
    $callerPath = dirname($caller['file']);
    
    // Hitung relative path dari caller ke root
    $rootPath = realpath(__DIR__ . '/..');
    $relativePath = str_replace($rootPath, '', $callerPath);
    $depth = substr_count($relativePath, DIRECTORY_SEPARATOR);
    
    // Build path relatif
    $assetPath = '';
    for ($i = 0; $i < $depth; $i++) {
        $assetPath .= '../';
    }
    
    return $assetPath . 'assets/' . $path;
}

// Fungsi untuk mendapatkan base path relatif ke root
function getBasePath() {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $scriptDir = dirname($scriptName);
    
    // Jika di root, return kosong
    if ($scriptDir === '/') {
        return '';
    }
    
    // Hitung depth dari root
    $pathParts = explode('/', trim($scriptDir, '/'));
    
    // Cari posisi folder random
    $basePath = '';
    foreach ($pathParts as $part) {
        if (!empty($part)) {
            $basePath .= '../';
        }
    }
    
    return rtrim($basePath, '/');
}

// Fungsi helper untuk redirect dengan relative path
function redirect($path) {
    // Jika path sudah absolute, langsung gunakan
    if (strpos($path, 'http://') === 0 || strpos($path, 'https://') === 0) {
        header('Location: ' . $path);
        exit();
    }
    
    // Jika path dimulai dengan /, hapus
    if (strpos($path, '/') === 0) {
        $path = substr($path, 1);
    }
    
    header('Location: ' . $path);
    exit();
}


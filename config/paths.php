<?php
/**
 * Helper untuk path management - Versi Sederhana dan Reliable
 */

// Simpan path root project (folder yang berisi config/)
define('PROJECT_ROOT', realpath(__DIR__ . '/..'));

// Fungsi untuk mendapatkan depth dari script yang sedang diakses
function getScriptDepth() {
    $scriptName = $_SERVER['SCRIPT_NAME'];
    $scriptDir = dirname($scriptName);
    
    // Normalisasi path
    $scriptDir = trim($scriptDir, '/');
    
    if (empty($scriptDir) || $scriptDir === '.') {
        return 0;
    }
    
    // Pisahkan menjadi array
    $parts = explode('/', $scriptDir);
    $parts = array_filter($parts);
    
    // Jika bagian pertama bukan folder aplikasi (admin/kasir/assets/dll), abaikan
    // Hitung dari folder aplikasi pertama
    $projectFolders = ['admin', 'kasir', 'assets', 'config', 'includes'];
    $depth = 0;
    $found = false;
    
    foreach ($parts as $part) {
        if (in_array($part, $projectFolders)) {
            $found = true;
            $depth++;
        } elseif ($found) {
            $depth++;
        }
    }
    
    return $depth;
}

// Fungsi untuk mendapatkan path relatif dari lokasi file ke root project
function getNavPath($targetPath) {
    $depth = getScriptDepth();
    
    if ($depth == 0) {
        return ltrim($targetPath, '/');
    }
    
    $backPath = str_repeat('../', $depth);
    $targetPath = ltrim($targetPath, '/');
    
    return $backPath . $targetPath;
}

// Fungsi untuk mendapatkan path relatif ke assets
function getAssetPath($file = '') {
    $depth = getScriptDepth();
    
    if ($depth == 0) {
        return 'assets/' . $file;
    }
    
    $backPath = str_repeat('../', $depth);
    return $backPath . 'assets/' . $file;
}

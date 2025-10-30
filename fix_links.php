<?php
/**
 * Script untuk memperbaiki semua link absolute menjadi relative
 * Run sekali saja, lalu hapus file ini
 */

$files = [
    'admin/daftar_barang/index.php',
    'admin/daftar_barang/tambah_stok.php',
    'admin/kategori_barang/index.php',
    'admin/laporan/transaksi.php',
    'admin/laporan/pembelian.php',
    'admin/metode_pembayaran/index.php',
    'admin/user/index.php',
];

foreach ($files as $file) {
    if (file_exists($file)) {
        $content = file_get_contents($file);
        
        // Replace absolute paths dengan getNavPath
        $content = preg_replace(
            '/href="\/(admin\/[^"]+)"/',
            'href="<?php echo getNavPath(\'$1\'); ?>"',
            $content
        );
        
        $content = preg_replace(
            '/href="\/(kasir\/[^"]+)"/',
            'href="<?php echo getNavPath(\'$1\'); ?>"',
            $content
        );
        
        // Pastikan require paths.php ada
        if (strpos($content, 'require.*paths.php') === false && strpos($content, 'include.*header') !== false) {
            $content = preg_replace(
                '/(include.*header\.php)/',
                "require_once __DIR__ . '/../../config/paths.php';\n$1",
                $content,
                1
            );
        }
        
        file_put_contents($file, $content);
        echo "Fixed: $file\n";
    }
}

echo "Done!\n";


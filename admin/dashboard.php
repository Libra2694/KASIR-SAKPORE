<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$pageTitle = 'Dashboard Admin';

// Get statistics
$conn = getConnection();

// Total Barang
$totalBarang = queryOne("SELECT COUNT(*) as total FROM daftar_barang")['total'];

// Barang Stok Habis/Hampir Habis (stok <= 5)
$stokHampirHabis = queryOne("SELECT COUNT(*) as total FROM daftar_barang WHERE stok <= 5")['total'];

// Transaksi Hari Ini
$transaksiHariIni = queryOne("SELECT COUNT(*) as total FROM transaksi WHERE DATE(tanggal) = CURDATE()")['total'];

// Pendapatan Hari Ini
$pendapatanHariIni = queryOne("SELECT COALESCE(SUM(total_akhir), 0) as total FROM transaksi WHERE DATE(tanggal) = CURDATE()")['total'];

// Pemasukan Bulanan
$pemasukanBulanan = queryOne("SELECT COALESCE(SUM(total_akhir), 0) as total FROM transaksi WHERE MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())")['total'];

// Pengeluaran Bulanan (dari pembelian)
$pengeluaranBulanan = queryOne("SELECT COALESCE(SUM(total_harga), 0) as total FROM pembelian_barang WHERE MONTH(tanggal) = MONTH(CURDATE()) AND YEAR(tanggal) = YEAR(CURDATE())")['total'];

// Data untuk grafik transaksi 7 hari terakhir
$transaksi7Hari = queryArray("SELECT DATE(tanggal) as tanggal, COUNT(*) as jumlah 
                               FROM transaksi 
                               WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                               GROUP BY DATE(tanggal)
                               ORDER BY tanggal ASC");

// Data untuk grafik pendapatan 7 hari terakhir
$pendapatan7Hari = queryArray("SELECT DATE(tanggal) as tanggal, COALESCE(SUM(total_akhir), 0) as total 
                                FROM transaksi 
                                WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                                GROUP BY DATE(tanggal)
                                ORDER BY tanggal ASC");

// Data untuk grafik pemasukan vs pengeluaran per bulan (6 bulan terakhir)
$pemasukanBulanan6 = queryArray("SELECT DATE_FORMAT(tanggal, '%Y-%m') as bulan, 
                                         DATE_FORMAT(tanggal, '%b %Y') as label,
                                         COALESCE(SUM(total_akhir), 0) as pemasukan 
                                  FROM transaksi 
                                  WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
                                  GROUP BY DATE_FORMAT(tanggal, '%Y-%m'), DATE_FORMAT(tanggal, '%b %Y')
                                  ORDER BY bulan ASC");

$pengeluaranBulanan6 = queryArray("SELECT DATE_FORMAT(tanggal, '%Y-%m') as bulan,
                                          DATE_FORMAT(tanggal, '%b %Y') as label,
                                          COALESCE(SUM(total_harga), 0) as pengeluaran 
                                   FROM pembelian_barang 
                                   WHERE tanggal >= DATE_SUB(CURDATE(), INTERVAL 5 MONTH)
                                   GROUP BY DATE_FORMAT(tanggal, '%Y-%m'), DATE_FORMAT(tanggal, '%b %Y')
                                   ORDER BY bulan ASC");

// Data untuk grafik kategori barang (top 5)
$kategoriBarang = queryArray("SELECT k.namaKategori, COUNT(d.id_barang) as jumlah 
                              FROM kategori_barang k
                              LEFT JOIN daftar_barang d ON k.id_kategori = d.kategori
                              GROUP BY k.id_kategori, k.namaKategori
                              ORDER BY jumlah DESC
                              LIMIT 5");

mysqli_close($conn);

require_once __DIR__ . '/../config/paths.php';
include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>DASHBOARD ADMIN</h2>
    </div>
    
    <div class="dashboard-grid">
        <div class="dashboard-card">
            <h3><?php echo $totalBarang; ?> BARANG</h3>
            <p>JUMLAH BARANG TERSEDIA</p>
            <a href="<?php echo getNavPath('admin/daftar_barang/index.php'); ?>" class="btn btn-secondary">LIHAT DETAIL</a>
        </div>
        
        <div class="dashboard-card">
            <h3><?php echo $stokHampirHabis; ?> BARANG</h3>
            <p>JUMLAH STOK BRG HAMPIR HABIS</p>
            <a href="<?php echo getNavPath('admin/daftar_barang/index.php'); ?>" class="btn btn-warning">LIHAT DETAIL</a>
        </div>
        
        <div class="dashboard-card">
            <h3><?php echo $transaksiHariIni; ?> TRANSAKSI</h3>
            <p>JUMLAH TRANSAKSI HARI INI</p>
            <a href="<?php echo getNavPath('admin/laporan/transaksi.php'); ?>" class="btn btn-secondary">LIHAT DETAIL</a>
        </div>
        
        <div class="dashboard-card">
            <h3><?php echo formatRupiah($pendapatanHariIni); ?></h3>
            <p>JUMLAH PENDAPATAN HARI INI</p>
            <a href="<?php echo getNavPath('admin/laporan/keuangan.php'); ?>" class="btn btn-success">LIHAT DETAIL</a>
        </div>
        
        <div class="dashboard-card">
            <h3><?php echo formatRupiah($pemasukanBulanan); ?></h3>
            <p>JUMLAH PEMASUKAN BULANAN</p>
            <a href="<?php echo getNavPath('admin/laporan/keuangan.php'); ?>" class="btn btn-success">LIHAT DETAIL</a>
        </div>
        
        <div class="dashboard-card">
            <h3><?php echo formatRupiah($pengeluaranBulanan); ?></h3>
            <p>JUMLAH PENGELUARAN BULANAN</p>
            <a href="<?php echo getNavPath('admin/laporan/pembelian.php'); ?>" class="btn btn-danger">LIHAT DETAIL</a>
        </div>
    </div>
    
    <!-- Grafik Statistik -->
    <div style="margin-top: 30px; display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
        <div class="card">
            <div class="card-header">
                <h3>Transaksi 7 Hari Terakhir</h3>
            </div>
            <div style="padding: 20px;">
                <canvas id="chartTransaksi"></canvas>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3>Pendapatan 7 Hari Terakhir</h3>
            </div>
            <div style="padding: 20px;">
                <canvas id="chartPendapatan"></canvas>
            </div>
        </div>
        
        <div class="card" style="grid-column: 1 / -1;">
            <div class="card-header">
                <h3>Pemasukan vs Pengeluaran (6 Bulan Terakhir)</h3>
            </div>
            <div style="padding: 20px;">
                <canvas id="chartKeuangan"></canvas>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h3>Top 5 Kategori Barang</h3>
            </div>
            <div style="padding: 20px;">
                <canvas id="chartKategori"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
// Data dari PHP
const transaksi7Hari = <?php echo json_encode($transaksi7Hari); ?>;
const pendapatan7Hari = <?php echo json_encode($pendapatan7Hari); ?>;
const pemasukanBulanan6 = <?php echo json_encode($pemasukanBulanan6); ?>;
const pengeluaranBulanan6 = <?php echo json_encode($pengeluaranBulanan6); ?>;
const kategoriBarang = <?php echo json_encode($kategoriBarang); ?>;

// Generate labels untuk 7 hari terakhir
const hariLabels = [];
for (let i = 6; i >= 0; i--) {
    const date = new Date();
    date.setDate(date.getDate() - i);
    hariLabels.push(date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }));
}

// Map data transaksi ke labels
const transaksiData = hariLabels.map(label => {
    const found = transaksi7Hari.find(t => {
        const date = new Date(t.tanggal);
        return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }) === label;
    });
    return found ? parseInt(found.jumlah) : 0;
});

// Map data pendapatan ke labels
const pendapatanData = hariLabels.map(label => {
    const found = pendapatan7Hari.find(p => {
        const date = new Date(p.tanggal);
        return date.toLocaleDateString('id-ID', { day: '2-digit', month: 'short' }) === label;
    });
    return found ? parseFloat(found.total) : 0;
});

// Grafik Transaksi 7 Hari Terakhir
const ctxTransaksi = document.getElementById('chartTransaksi').getContext('2d');
new Chart(ctxTransaksi, {
    type: 'line',
    data: {
        labels: hariLabels,
        datasets: [{
            label: 'Jumlah Transaksi',
            data: transaksiData,
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.2)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    stepSize: 1
                }
            }
        }
    }
});

// Grafik Pendapatan 7 Hari Terakhir
const ctxPendapatan = document.getElementById('chartPendapatan').getContext('2d');
new Chart(ctxPendapatan, {
    type: 'line',
    data: {
        labels: hariLabels,
        datasets: [{
            label: 'Pendapatan (Rp)',
            data: pendapatanData,
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                    }
                }
            }
        }
    }
});

// Grafik Pemasukan vs Pengeluaran
const bulanLabels = [...new Set([...pemasukanBulanan6.map(p => p.label), ...pengeluaranBulanan6.map(pe => pe.label)])];
const pemasukanData = bulanLabels.map(label => {
    const found = pemasukanBulanan6.find(p => p.label === label);
    return found ? parseFloat(found.pemasukan) : 0;
});
const pengeluaranData = bulanLabels.map(label => {
    const found = pengeluaranBulanan6.find(pe => pe.label === label);
    return found ? parseFloat(found.pengeluaran) : 0;
});

const ctxKeuangan = document.getElementById('chartKeuangan').getContext('2d');
new Chart(ctxKeuangan, {
    type: 'bar',
    data: {
        labels: bulanLabels,
        datasets: [{
            label: 'Pemasukan',
            data: pemasukanData,
            backgroundColor: 'rgba(75, 192, 192, 0.8)',
            borderColor: 'rgb(75, 192, 192)',
            borderWidth: 1
        }, {
            label: 'Pengeluaran',
            data: pengeluaranData,
            backgroundColor: 'rgba(255, 99, 132, 0.8)',
            borderColor: 'rgb(255, 99, 132)',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return context.dataset.label + ': Rp ' + new Intl.NumberFormat('id-ID').format(context.parsed.y);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                    }
                }
            }
        }
    }
});

// Grafik Kategori Barang
const ctxKategori = document.getElementById('chartKategori').getContext('2d');
new Chart(ctxKategori, {
    type: 'doughnut',
    data: {
        labels: kategoriBarang.map(k => k.namaKategori),
        datasets: [{
            label: 'Jumlah Barang',
            data: kategoriBarang.map(k => parseInt(k.jumlah)),
            backgroundColor: [
                'rgba(255, 99, 132, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)'
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: true,
                position: 'bottom'
            }
        }
    }
});
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>


<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

$pageTitle = 'Laporan Keuangan';

$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$conn = getConnection();

// Total Pemasukan Bulanan
$pemasukan = queryOne("SELECT COALESCE(SUM(total_akhir), 0) as total FROM transaksi WHERE MONTH(tanggal) = $bulan AND YEAR(tanggal) = $tahun")['total'];

// Total Pengeluaran Bulanan
$pengeluaran = queryOne("SELECT COALESCE(SUM(total_harga), 0) as total FROM pembelian_barang WHERE MONTH(tanggal) = $bulan AND YEAR(tanggal) = $tahun")['total'];

// Get detail keuangan
$keuangan = [];

// Get transaksi (pemasukan)
$transaksi = queryArray("SELECT DATE(tanggal) as tanggal, SUM(total_akhir) as pemasukan 
                         FROM transaksi 
                         WHERE MONTH(tanggal) = $bulan AND YEAR(tanggal) = $tahun
                         GROUP BY DATE(tanggal)");
foreach ($transaksi as $tr) {
    $keuangan[] = [
        'tanggal' => $tr['tanggal'],
        'jenis' => 'Transaksi Harian ' . date('d-m-Y', strtotime($tr['tanggal'])),
        'pemasukan' => $tr['pemasukan'],
        'pengeluaran' => 0
    ];
}

// Get pembelian (pengeluaran)
$pembelian = queryArray("SELECT tanggal, CONCAT('Pembelian Stok ', nama_barang) as jenis, total_harga as pengeluaran
                         FROM pembelian_barang
                         WHERE MONTH(tanggal) = $bulan AND YEAR(tanggal) = $tahun");
foreach ($pembelian as $pb) {
    $keuangan[] = [
        'tanggal' => $pb['tanggal'],
        'jenis' => $pb['jenis'],
        'pemasukan' => 0,
        'pengeluaran' => $pb['pengeluaran']
    ];
}

// Sort by tanggal DESC
usort($keuangan, function($a, $b) {
    return strtotime($b['tanggal']) - strtotime($a['tanggal']);
});
mysqli_close($conn);

require_once __DIR__ . '/../../config/paths.php';
include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>LAPORAN KEUANGAN</h2>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
        <div>
            <label>TOTAL PEMASUKKAN BULAN INI:</label>
            <input type="text" class="form-control" value="<?php echo formatRupiah($pemasukan); ?>" readonly>
        </div>
        <div>
            <label>TOTAL PENGELUARAN BULAN INI:</label>
            <input type="text" class="form-control" value="<?php echo formatRupiah($pengeluaran); ?>" readonly>
        </div>
    </div>
    
    <div class="search-filter">
        <label>FILTER:</label>
        <select id="bulan">
            <?php
            $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            for ($i = 1; $i <= 12; $i++) {
                $selected = ($i == $bulan) ? 'selected' : '';
                echo "<option value='$i' $selected>{$months[$i-1]}</option>";
            }
            ?>
        </select>
        <input type="number" id="tahun" value="<?php echo $tahun; ?>" min="2020" max="2100">
        <button class="btn btn-success" onclick="applyFilter()">REFRESH</button>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>NO</th>
                    <th>TANGGAL</th>
                    <th>JENIS KEUANGAN</th>
                    <th>PEMASUKAN</th>
                    <th>PENGELUARAN</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($keuangan)): ?>
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($keuangan as $index => $kg): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo date('d-m-Y', strtotime($kg['tanggal'])); ?></td>
                            <td><?php echo htmlspecialchars($kg['jenis']); ?></td>
                            <td><?php echo formatRupiah($kg['pemasukan']); ?></td>
                            <td><?php echo formatRupiah($kg['pengeluaran']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function applyFilter() {
    const bulan = document.getElementById('bulan').value;
    const tahun = document.getElementById('tahun').value;
    window.location.href = `?bulan=${bulan}&tahun=${tahun}`;
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>


<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

$pageTitle = 'Laporan Transaksi';

$filter = $_GET['filter'] ?? 'harian';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$tanggal_awal = $_GET['tanggal_awal'] ?? date('Y-m-01');
$tanggal_akhir = $_GET['tanggal_akhir'] ?? date('Y-m-d');
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$conn = getConnection();

switch ($filter) {
    case 'harian':
        $sql = "SELECT * FROM transaksi WHERE DATE(tanggal) = ? ORDER BY tanggal DESC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "s", $tanggal);
        break;
    case 'mingguan':
        $sql = "SELECT * FROM transaksi WHERE DATE(tanggal) BETWEEN ? AND ? ORDER BY tanggal DESC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ss", $tanggal_awal, $tanggal_akhir);
        break;
    case 'bulanan':
        $sql = "SELECT * FROM transaksi WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ? ORDER BY tanggal DESC";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "ii", $bulan, $tahun);
        break;
    default:
        $sql = "SELECT * FROM transaksi WHERE DATE(tanggal) = CURDATE() ORDER BY tanggal DESC";
        $stmt = mysqli_prepare($conn, $sql);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$transaksi = [];
while ($row = mysqli_fetch_assoc($result)) {
    $transaksi[] = $row;
}
mysqli_close($conn);

require_once __DIR__ . '/../../config/paths.php';
include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>LAPORAN TRANSAKSI PENJUALAN</h2>
    </div>
    
    <div class="search-filter">
        <label>FILTER:</label>
        <select id="filter" onchange="changeFilter()">
            <option value="harian" <?php echo $filter == 'harian' ? 'selected' : ''; ?>>LAPORAN TRANSAKSI HARIAN</option>
            <option value="mingguan" <?php echo $filter == 'mingguan' ? 'selected' : ''; ?>>LAPORAN TRANSAKSI MINGGUAN</option>
            <option value="bulanan" <?php echo $filter == 'bulanan' ? 'selected' : ''; ?>>LAPORAN TRANSAKSI BULANAN</option>
        </select>
        
        <div id="filter_harian" style="display: <?php echo $filter == 'harian' ? 'inline-block' : 'none'; ?>;">
            <label>TANGGAL:</label>
            <input type="date" id="tanggal" value="<?php echo $tanggal; ?>">
        </div>
        
        <div id="filter_mingguan" style="display: <?php echo $filter == 'mingguan' ? 'inline-block' : 'none'; ?>;">
            <label>TANGGAL AWAL:</label>
            <input type="date" id="tanggal_awal" value="<?php echo $tanggal_awal; ?>">
            <label>SAMPAI TANGGAL:</label>
            <input type="date" id="tanggal_akhir" value="<?php echo $tanggal_akhir; ?>">
        </div>
        
        <div id="filter_bulanan" style="display: <?php echo $filter == 'bulanan' ? 'inline-block' : 'none'; ?>;">
            <label>BULAN:</label>
            <select id="bulan">
                <?php
                $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                for ($i = 1; $i <= 12; $i++) {
                    $selected = ($i == $bulan) ? 'selected' : '';
                    echo "<option value='$i' $selected>{$months[$i-1]}</option>";
                }
                ?>
            </select>
            <label>TAHUN:</label>
            <input type="number" id="tahun" value="<?php echo $tahun; ?>" min="2020" max="2100">
        </div>
        
        <button class="btn btn-success" onclick="applyFilter()">CARI</button>
        <button class="btn btn-success" onclick="location.reload()">REFRESH</button>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>NO.</th>
                    <th>ID TRANSAKSI</th>
                    <th>TANGGAL</th>
                    <th>KASIR</th>
                    <th>TOTAL</th>
                    <th>BAYAR</th>
                    <th>KEMBALIAN</th>
                    <th>METODE PEMBAYARAN</th>
                    <th>DETAIL</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transaksi)): ?>
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transaksi as $index => $tr): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo $tr['idtransaksi']; ?></td>
                            <td><?php echo date('d-m-Y H:i', strtotime($tr['tanggal'])); ?></td>
                            <td><?php echo $tr['kasir']; ?></td>
                            <td><?php echo formatRupiah($tr['total_akhir']); ?></td>
                            <td><?php echo formatRupiah($tr['bayar']); ?></td>
                            <td><?php echo formatRupiah($tr['kembalian']); ?></td>
                            <td><?php echo $tr['metode_pembayaran'] ?? '-'; ?></td>
                            <td>
                                <a href="<?php echo getNavPath('admin/laporan/detail_transaksi.php?id=' . $tr['idtransaksi']); ?>" 
                                   class="btn btn-secondary" style="padding: 5px 10px;">DETAIL</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function changeFilter() {
    const filter = document.getElementById('filter').value;
    document.getElementById('filter_harian').style.display = filter === 'harian' ? 'inline-block' : 'none';
    document.getElementById('filter_mingguan').style.display = filter === 'mingguan' ? 'inline-block' : 'none';
    document.getElementById('filter_bulanan').style.display = filter === 'bulanan' ? 'inline-block' : 'none';
}

function applyFilter() {
    const filter = document.getElementById('filter').value;
    let url = '?filter=' + filter;
    
    if (filter === 'harian') {
        url += '&tanggal=' + document.getElementById('tanggal').value;
    } else if (filter === 'mingguan') {
        url += '&tanggal_awal=' + document.getElementById('tanggal_awal').value;
        url += '&tanggal_akhir=' + document.getElementById('tanggal_akhir').value;
    } else if (filter === 'bulanan') {
        url += '&bulan=' + document.getElementById('bulan').value;
        url += '&tahun=' + document.getElementById('tahun').value;
    }
    
    window.location.href = url;
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>


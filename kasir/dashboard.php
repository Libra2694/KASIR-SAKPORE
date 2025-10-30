<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireKasir();

$pageTitle = 'Dashboard Kasir';

// Get transactions for current month
$bulan = $_GET['bulan'] ?? date('m');
$tahun = $_GET['tahun'] ?? date('Y');

$conn = getConnection();
$sql = "SELECT * FROM transaksi 
        WHERE MONTH(tanggal) = ? AND YEAR(tanggal) = ? 
        ORDER BY tanggal DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "ii", $bulan, $tahun);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$transaksi = [];
while ($row = mysqli_fetch_assoc($result)) {
    $transaksi[] = $row;
}
mysqli_close($conn);

require_once __DIR__ . '/../config/paths.php';
include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>BERANDA KASIR</h2>
    </div>
    
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 15px;">
        <div class="btn-group" style="display: flex; gap: 10px;">
            <a href="<?php echo getNavPath('kasir/transaksi.php'); ?>" class="btn btn-success">TRANSAKSI BARU [ENTER]</a>
            <a href="<?php echo getNavPath('kasir/transaksi.php?action=detail'); ?>" class="btn btn-secondary">LIHAT DETAIL [SHIFT + ENTER]</a>
        </div>
        
        <div class="search-filter" style="display: flex; align-items: center; gap: 10px;">
            <label style="margin: 0; font-weight: bold;">FILTER:</label>
            <select id="bulan" onchange="filterTransaksi()" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <?php
                $months = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
                for ($i = 1; $i <= 12; $i++) {
                    $selected = ($i == $bulan) ? 'selected' : '';
                    echo "<option value='$i' $selected>{$months[$i-1]}</option>";
                }
                ?>
            </select>
            <input type="number" id="tahun" value="<?php echo $tahun; ?>" onchange="filterTransaksi()" style="width: 100px; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
            <button class="btn btn-success" onclick="filterTransaksi()">REFRESH</button>
        </div>
    </div>
    
    <h3 style="margin-top: 20px;">DAFTAR TRANSAKSI BULANAN :</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>NO.</th>
                    <th>ID TRANSAKSI</th>
                    <th>NAMA KASIR</th>
                    <th>TANGGAL</th>
                    <th>TOTAL HARGA</th>
                    <th>DISKON</th>
                    <th>BAYAR</th>
                    <th>KEMBALIAN</th>
                    <th>METODE PEMBAYARAN</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transaksi)): ?>
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada transaksi</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transaksi as $index => $tr): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo $tr['idtransaksi']; ?></td>
                            <td><?php echo $tr['kasir']; ?></td>
                            <td><?php echo date('d-m-Y H:i', strtotime($tr['tanggal'])); ?></td>
                            <td><?php echo formatRupiah($tr['total_akhir']); ?></td>
                            <td><?php echo formatRupiah($tr['diskon']); ?></td>
                            <td><?php echo formatRupiah($tr['bayar']); ?></td>
                            <td><?php echo formatRupiah($tr['kembalian']); ?></td>
                            <td><?php echo $tr['metode_pembayaran'] ?? '-'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function filterTransaksi() {
    const bulan = document.getElementById('bulan').value;
    const tahun = document.getElementById('tahun').value;
    window.location.href = `?bulan=${bulan}&tahun=${tahun}`;
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>


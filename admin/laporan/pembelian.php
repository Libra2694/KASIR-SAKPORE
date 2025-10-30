<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

$pageTitle = 'Laporan Pembelian';

$search = $_GET['search'] ?? '';
$conn = getConnection();

if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $pembelian = queryArray("SELECT * FROM pembelian_barang 
                             WHERE nama_barang LIKE '%$search%' OR id_barang LIKE '%$search%' 
                             ORDER BY tanggal DESC");
} else {
    $pembelian = queryArray("SELECT * FROM pembelian_barang ORDER BY tanggal DESC");
}
mysqli_close($conn);

require_once __DIR__ . '/../../config/paths.php';
include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>LAPORAN PEMBELIAN</h2>
    </div>
    
    <div class="search-filter">
        <a href="<?php echo getNavPath('admin/pembelian_barang/index.php'); ?>" class="btn btn-secondary">TAMBAH PEMBELIAN BARANG</a>
        <input type="text" id="search" placeholder="Cari pembelian..." value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-success" onclick="searchPembelian()">CARI</button>
        <button class="btn btn-success" onclick="window.print()">CETAK</button>
        <button class="btn btn-success" onclick="exportPDF()">EKSPOR PDF</button>
    </div>
    
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>NO.</th>
                    <th>TANGGAL</th>
                    <th>KODE BARANG</th>
                    <th>NAMA BARANG</th>
                    <th>KATEGORI</th>
                    <th>SATUAN</th>
                    <th>HARGA SATUAN</th>
                    <th>JUMLAH BELI</th>
                    <th>TOTAL HARGA</th>
                    <th>SUPPLIER</th>
                    <th>PERUSAHAAN</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pembelian)): ?>
                    <tr>
                        <td colspan="11" class="text-center">Tidak ada data</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pembelian as $index => $pb): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo date('d-m-Y', strtotime($pb['tanggal'])); ?></td>
                            <td><?php echo $pb['id_barang']; ?></td>
                            <td><?php echo htmlspecialchars($pb['nama_barang']); ?></td>
                            <td><?php echo htmlspecialchars($pb['kategori']); ?></td>
                            <td><?php echo $pb['satuan']; ?></td>
                            <td><?php echo formatRupiah($pb['harga_satuan']); ?></td>
                            <td><?php echo $pb['jumlah_beli']; ?></td>
                            <td><?php echo formatRupiah($pb['total_harga']); ?></td>
                            <td><?php echo htmlspecialchars($pb['nama_supplier'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($pb['perusahaan_supplier'] ?? '-'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function searchPembelian() {
    const search = document.getElementById('search').value;
    window.location.href = '?search=' + encodeURIComponent(search);
}

function exportPDF() {
    window.print();
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>


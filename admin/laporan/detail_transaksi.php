<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

$pageTitle = 'Detail Transaksi';

$id_transaksi = $_GET['id'] ?? '';

if (empty($id_transaksi)) {
    header('Location: transaksi.php');
    exit();
}

$conn = getConnection();
$transaksi = queryOne("SELECT * FROM transaksi WHERE idtransaksi = '$id_transaksi'");
$detail = queryArray("SELECT * FROM detai_transaksi WHERE id_transaksi = '$id_transaksi'");
mysqli_close($conn);

if (!$transaksi) {
    header('Location: transaksi.php');
    exit();
}

require_once __DIR__ . '/../../config/paths.php';
include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>DETAIL TRANSAKSI: <?php echo $transaksi['idtransaksi']; ?></h2>
    </div>
    
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
        <div>
            <p><strong>ID Transaksi:</strong> <?php echo $transaksi['idtransaksi']; ?></p>
            <p><strong>Tanggal:</strong> <?php echo date('d-m-Y H:i', strtotime($transaksi['tanggal'])); ?></p>
            <p><strong>Kasir:</strong> <?php echo $transaksi['kasir']; ?></p>
        </div>
        <div>
            <p><strong>Total Akhir:</strong> <?php echo formatRupiah($transaksi['total_akhir']); ?></p>
            <p><strong>Bayar:</strong> <?php echo formatRupiah($transaksi['bayar']); ?></p>
            <p><strong>Kembalian:</strong> <?php echo formatRupiah($transaksi['kembalian']); ?></p>
        </div>
    </div>
    
    <h3>DETAIL BARANG:</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>NO.</th>
                    <th>ID BARANG</th>
                    <th>NAMA BARANG</th>
                    <th>HARGA</th>
                    <th>JUMLAH</th>
                    <th>TOTAL</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detail as $index => $dt): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo $dt['id_barang']; ?></td>
                        <td><?php echo htmlspecialchars($dt['nama_barang']); ?></td>
                        <td><?php echo formatRupiah($dt['harga_jual']); ?></td>
                        <td><?php echo $dt['jumlah_beli']; ?></td>
                        <td><?php echo formatRupiah($dt['total']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="btn-group">
        <a href="<?php echo getNavPath('admin/laporan/transaksi.php'); ?>" class="btn btn-secondary">KEMBALI</a>
        <button class="btn btn-success" onclick="window.print()">CETAK STRUK</button>
    </div>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>


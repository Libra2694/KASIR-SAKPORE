<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../includes/functions.php';

requireKasir();

$pageTitle = 'Daftar Barang';

$search = $_GET['search'] ?? '';
$conn = getConnection();
if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $barang = queryArray("SELECT db.*, kb.namaKategori FROM daftar_barang db 
                          LEFT JOIN kategori_barang kb ON db.kategori = kb.id_kategori 
                          WHERE db.nama_barang LIKE '%$search%' OR db.id_barang LIKE '%$search%' 
                          ORDER BY db.nama_barang");
} else {
    $barang = queryArray("SELECT db.*, kb.namaKategori FROM daftar_barang db 
                          LEFT JOIN kategori_barang kb ON db.kategori = kb.id_kategori 
                          ORDER BY db.nama_barang");
}
mysqli_close($conn);

require_once __DIR__ . '/../config/paths.php';
include __DIR__ . '/../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>DAFTAR BARANG</h2>
    </div>
    <div class="search-filter">
        <input type="text" id="search" placeholder="Cari barang..." value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-success" onclick="searchBarang()">CARI</button>
        <button class="btn btn-success" onclick="location.reload()">REFRESH</button>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>NO.</th>
                    <th>KODE BARANG</th>
                    <th>NAMA BARANG</th>
                    <th>KATEGORI</th>
                    <th>HARGA POKOK</th>
                    <th>PPN</th>
                    <th>HARGA JUAL</th>
                    <th>STOK</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($barang)): ?>
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($barang as $index => $brg): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo $brg['id_barang']; ?></td>
                            <td><?php echo htmlspecialchars($brg['nama_barang']); ?></td>
                            <td><?php echo htmlspecialchars($brg['namaKategori'] ?? $brg['kategori']); ?></td>
                            <td><?php echo formatRupiah($brg['harga_pokok']); ?></td>
                            <td><?php echo formatRupiah($brg['ppn']); ?></td>
                            <td><?php echo formatRupiah($brg['harga_jual']); ?></td>
                            <td><?php echo $brg['stok']; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function searchBarang() {
    const search = document.getElementById('search').value;
    window.location.href = '?search=' + encodeURIComponent(search);
}
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>


<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

$pageTitle = 'Daftar Barang';

$message = '';
$messageType = '';

// Handle success message
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'tambah') {
        $message = 'Barang berhasil ditambahkan!';
        $messageType = 'success';
    } elseif ($_GET['success'] == 'edit') {
        $message = 'Barang berhasil diupdate!';
        $messageType = 'success';
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'hapus') {
        $id_barang = sanitize($_POST['id_barang'] ?? '');
        if (!empty($id_barang)) {
            $conn = getConnection();
            $sql = "DELETE FROM daftar_barang WHERE id_barang = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $id_barang);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = 'Barang berhasil dihapus!';
                $messageType = 'success';
            } else {
                $message = 'Gagal menghapus barang!';
                $messageType = 'error';
            }
            mysqli_close($conn);
        }
    }
}

// Get barang
$search = $_GET['search'] ?? '';
$conn = getConnection();
if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $barang = queryArray("SELECT db.*, kb.namaKategori FROM daftar_barang db 
                          LEFT JOIN kategori_barang kb ON db.kategori = kb.id_kategori 
                          WHERE db.nama_barang LIKE '%$search%' OR db.id_barang LIKE '%$search%' 
                          ORDER BY db.id_barang");
} else {
    $barang = queryArray("SELECT db.*, kb.namaKategori FROM daftar_barang db 
                          LEFT JOIN kategori_barang kb ON db.kategori = kb.id_kategori 
                          ORDER BY db.id_barang");
}
mysqli_close($conn);

require_once __DIR__ . '/../../config/paths.php';
include __DIR__ . '/../../includes/header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>DAFTAR BARANG</h2>
    </div>
    <div class="search-filter">
        <input type="text" id="search" placeholder="Cari barang..." value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-success" onclick="searchBarang()">CARI</button>
        <button class="btn btn-success" onclick="location.reload()">REFRESH</button>
        <a href="<?php echo getNavPath('admin/daftar_barang/tambah_stok.php'); ?>" class="btn btn-secondary">TAMBAH STOK BARANG</a>
        <a href="<?php echo getNavPath('admin/daftar_barang/tambah.php'); ?>" class="btn btn-success">TAMBAH DAFTAR BARANG</a>
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
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($barang)): ?>
                    <tr>
                        <td colspan="9" class="text-center">Tidak ada data</td>
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
                            <td>
                                <a href="<?php echo getNavPath('admin/daftar_barang/tambah.php?edit=' . $brg['id_barang']); ?>" class="btn btn-secondary" style="padding: 5px 10px;">EDIT</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirmDelete()">
                                    <input type="hidden" name="action" value="hapus">
                                    <input type="hidden" name="id_barang" value="<?php echo $brg['id_barang']; ?>">
                                    <button type="submit" class="btn btn-danger" style="padding: 5px 10px;">DELETE</button>
                                </form>
                            </td>
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

<?php include __DIR__ . '/../../includes/footer.php'; ?>

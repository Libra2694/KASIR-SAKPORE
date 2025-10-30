<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

$pageTitle = 'Kategori Barang';

$message = '';
$messageType = '';

// Handle success message
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'tambah') {
        $message = 'Kategori berhasil ditambahkan!';
        $messageType = 'success';
    } elseif ($_GET['success'] == 'edit') {
        $message = 'Kategori berhasil diupdate!';
        $messageType = 'success';
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'hapus') {
        $idKategori = sanitize($_POST['id_kategori'] ?? '');
        if (!empty($idKategori)) {
            $conn = getConnection();
            $sql = "DELETE FROM kategori_barang WHERE id_kategori = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $idKategori);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = 'Kategori berhasil dihapus!';
                $messageType = 'success';
            } else {
                $message = 'Gagal menghapus kategori!';
                $messageType = 'error';
            }
            mysqli_close($conn);
        }
    }
}

// Get categories
$search = $_GET['search'] ?? '';
$conn = getConnection();
if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $kategori = queryArray("SELECT * FROM kategori_barang WHERE namaKategori LIKE '%$search%' ORDER BY tanggalinput DESC");
} else {
    $kategori = queryArray("SELECT * FROM kategori_barang ORDER BY tanggalinput DESC");
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
        <h2>DAFTAR KATEGORI BARANG</h2>
    </div>
    <div class="search-filter">
        <a href="<?php echo getNavPath('admin/kategori_barang/tambah.php'); ?>" class="btn btn-success">TAMBAH KATEGORI BARANG</a>
        <input type="text" id="search" placeholder="Cari kategori..." value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-success" onclick="searchKategori()">CARI</button>
        <button class="btn btn-success" onclick="location.reload()">REFRESH</button>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>NO</th>
                    <th>ID KATEGORI</th>
                    <th>NAMA KATEGORI</th>
                    <th>TANGGAL INPUT</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($kategori)): ?>
                    <tr>
                        <td colspan="5" class="text-center">Tidak ada data</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($kategori as $index => $kat): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo $kat['id_kategori']; ?></td>
                            <td><?php echo htmlspecialchars($kat['namaKategori']); ?></td>
                            <td><?php echo date('d-m-Y', strtotime($kat['tanggalinput'])); ?></td>
                            <td>
                                <a href="<?php echo getNavPath('admin/kategori_barang/tambah.php?edit=' . $kat['id_kategori']); ?>" class="btn btn-secondary" style="padding: 5px 10px;">EDIT</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirmDelete('Yakin ingin menghapus kategori ini?')">
                                    <input type="hidden" name="action" value="hapus">
                                    <input type="hidden" name="id_kategori" value="<?php echo $kat['id_kategori']; ?>">
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
function searchKategori() {
    const search = document.getElementById('search').value;
    window.location.href = '?search=' + encodeURIComponent(search);
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

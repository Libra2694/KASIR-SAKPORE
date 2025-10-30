<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

$pageTitle = 'Tambah Stok Barang';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_barang = sanitize($_POST['id_barang'] ?? '');
    $jumlah = intval($_POST['jumlah'] ?? '0');
    
    if (!empty($id_barang) && $jumlah > 0) {
        $conn = getConnection();
        $sql = "UPDATE daftar_barang SET stok = stok + ? WHERE id_barang = ?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "is", $jumlah, $id_barang);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = 'Stok berhasil ditambahkan!';
            $messageType = 'success';
        } else {
            $message = 'Gagal menambahkan stok!';
            $messageType = 'error';
        }
        mysqli_close($conn);
    }
}

$conn = getConnection();
$barang = queryArray("SELECT * FROM daftar_barang ORDER BY nama_barang");
mysqli_close($conn);

require_once __DIR__ . '/../../config/paths.php';
include __DIR__ . '/../../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h2>TAMBAH STOK BARANG</h2>
    </div>
    <a href="<?php echo getNavPath('admin/daftar_barang/index.php'); ?>" class="btn btn-secondary" style="margin-bottom: 20px;">KEMBALI</a>
    
    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
    <?php endif; ?>
    
    <form method="POST" action="">
        <div class="form-group">
            <label>PILIH BARANG:</label>
            <select name="id_barang" class="form-control" required>
                <option value="">-- Pilih Barang --</option>
                <?php foreach ($barang as $brg): ?>
                    <option value="<?php echo $brg['id_barang']; ?>">
                        <?php echo htmlspecialchars($brg['id_barang'] . ' - ' . $brg['nama_barang'] . ' (Stok: ' . $brg['stok'] . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>JUMLAH TAMBAH STOK:</label>
            <input type="number" name="jumlah" class="form-control" min="1" required>
        </div>
        <button type="submit" class="btn btn-success">SIMPAN</button>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>


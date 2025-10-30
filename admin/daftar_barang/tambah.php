<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

$pageTitle = 'Tambah/Edit Daftar Barang';

$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'tambah') {
        $id_barang = sanitize($_POST['id_barang'] ?? '');
        $nama_barang = sanitize($_POST['nama_barang'] ?? '');
        $kategori = sanitize($_POST['kategori'] ?? '');
        $satuan = sanitize($_POST['satuan'] ?? '');
        $harga_pokok = str_replace('.', '', $_POST['harga_pokok'] ?? '0');
        $ppn = str_replace('.', '', $_POST['ppn'] ?? '0');
        $harga_jual = str_replace('.', '', $_POST['harga_jual'] ?? '0');
        $stok = intval($_POST['stok'] ?? '0');
        
        if (!empty($id_barang) && !empty($nama_barang) && !empty($kategori)) {
            $conn = getConnection();
            $sql = "INSERT INTO daftar_barang (id_barang, nama_barang, kategori, satuan, harga_pokok, ppn, harga_jual, stok) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssssdddi", $id_barang, $nama_barang, $kategori, $satuan, $harga_pokok, $ppn, $harga_jual, $stok);
            
            if (mysqli_stmt_execute($stmt)) {
                require_once __DIR__ . '/../../config/paths.php';
                header('Location: ' . getNavPath('admin/daftar_barang/index.php') . '?success=tambah');
                exit();
            } else {
                $message = 'Gagal menambahkan barang!';
                $messageType = 'error';
            }
            mysqli_close($conn);
        }
    } elseif ($action == 'edit') {
        $id_barang = sanitize($_POST['id_barang'] ?? '');
        $nama_barang = sanitize($_POST['nama_barang'] ?? '');
        $kategori = sanitize($_POST['kategori'] ?? '');
        $satuan = sanitize($_POST['satuan'] ?? '');
        $harga_pokok = str_replace('.', '', $_POST['harga_pokok'] ?? '0');
        $ppn = str_replace('.', '', $_POST['ppn'] ?? '0');
        $harga_jual = str_replace('.', '', $_POST['harga_jual'] ?? '0');
        $stok = intval($_POST['stok'] ?? '0');
        
        if (!empty($id_barang)) {
            $conn = getConnection();
            $sql = "UPDATE daftar_barang SET nama_barang = ?, kategori = ?, satuan = ?, harga_pokok = ?, ppn = ?, harga_jual = ?, stok = ? 
                    WHERE id_barang = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssdddis", $nama_barang, $kategori, $satuan, $harga_pokok, $ppn, $harga_jual, $stok, $id_barang);
            
            if (mysqli_stmt_execute($stmt)) {
                require_once __DIR__ . '/../../config/paths.php';
                header('Location: ' . getNavPath('admin/daftar_barang/index.php') . '?success=edit');
                exit();
            } else {
                $message = 'Gagal mengupdate barang!';
                $messageType = 'error';
            }
            mysqli_close($conn);
        }
    }
}

// Get categories
$conn = getConnection();
$kategori = queryArray("SELECT * FROM kategori_barang ORDER BY namaKategori");
mysqli_close($conn);

// Get barang for edit
$editBarang = null;
if (isset($_GET['edit'])) {
    $editId = sanitize($_GET['edit']);
    $editBarang = queryOne("SELECT * FROM daftar_barang WHERE id_barang = '$editId'");
}

require_once __DIR__ . '/../../config/paths.php';
include __DIR__ . '/../../includes/header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2><?php echo $editBarang ? 'EDIT DAFTAR BARANG' : 'TAMBAH DAFTAR BARANG'; ?></h2>
    </div>
    
    <form method="POST" action="">
        <input type="hidden" name="action" value="<?php echo $editBarang ? 'edit' : 'tambah'; ?>">
        <div class="form-row">
            <div class="form-group">
                <label>KODE BARANG:</label>
                <input type="text" name="id_barang" class="form-control" 
                       value="<?php echo $editBarang ? htmlspecialchars($editBarang['id_barang']) : generateIdBarang(); ?>" 
                       required <?php echo $editBarang ? 'readonly' : ''; ?>>
            </div>
            <div class="form-group">
                <label>NAMA BARANG:</label>
                <input type="text" name="nama_barang" class="form-control" 
                       value="<?php echo $editBarang ? htmlspecialchars($editBarang['nama_barang']) : ''; ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>KATEGORI:</label>
                <select name="kategori" class="form-control" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($kategori as $kat): ?>
                        <option value="<?php echo $kat['id_kategori']; ?>" 
                                <?php echo ($editBarang && $editBarang['kategori'] == $kat['id_kategori']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($kat['namaKategori']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>SATUAN BARANG:</label>
                <input type="text" name="satuan" class="form-control" 
                       value="<?php echo $editBarang ? htmlspecialchars($editBarang['satuan']) : ''; ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>HARGA POKOK:</label>
                <input type="text" name="harga_pokok" class="form-control" data-currency
                       value="<?php echo $editBarang ? number_format($editBarang['harga_pokok'], 0, ',', '.') : ''; ?>" required>
            </div>
            <div class="form-group">
                <label>PPN/PAJAK/KEUNTUNGAN:</label>
                <input type="text" name="ppn" class="form-control" data-currency
                       value="<?php echo $editBarang ? number_format($editBarang['ppn'], 0, ',', '.') : ''; ?>" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>HARGA JUAL:</label>
                <input type="text" name="harga_jual" class="form-control" data-currency
                       value="<?php echo $editBarang ? number_format($editBarang['harga_jual'], 0, ',', '.') : ''; ?>" required>
            </div>
            <div class="form-group">
                <label>STOK:</label>
                <input type="number" name="stok" class="form-control" 
                       value="<?php echo $editBarang ? $editBarang['stok'] : '0'; ?>" required>
            </div>
        </div>
        <div class="btn-group">
            <button type="submit" class="btn btn-success">SIMPAN</button>
            <a href="<?php echo getNavPath('admin/daftar_barang/index.php'); ?>" class="btn btn-secondary">BATAL</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>


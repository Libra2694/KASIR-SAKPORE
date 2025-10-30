<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

$pageTitle = 'Tambah/Edit Kategori Barang';

$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'tambah') {
        $namaKategori = sanitize($_POST['namaKategori'] ?? '');
        if (!empty($namaKategori)) {
            $idKategori = generateIdKategori();
            $conn = getConnection();
            $sql = "INSERT INTO kategori_barang (id_kategori, namaKategori, tanggalinput) VALUES (?, ?, CURDATE())";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $idKategori, $namaKategori);
            
            if (mysqli_stmt_execute($stmt)) {
                header('Location: index.php?success=tambah');
                exit();
            } else {
                $message = 'Gagal menambahkan kategori!';
                $messageType = 'error';
            }
            mysqli_close($conn);
        }
    } elseif ($action == 'edit') {
        $idKategori = sanitize($_POST['id_kategori'] ?? '');
        $namaKategori = sanitize($_POST['namaKategori'] ?? '');
        if (!empty($idKategori) && !empty($namaKategori)) {
            $conn = getConnection();
            $sql = "UPDATE kategori_barang SET namaKategori = ? WHERE id_kategori = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $namaKategori, $idKategori);
            
            if (mysqli_stmt_execute($stmt)) {
                header('Location: index.php?success=edit');
                exit();
            } else {
                $message = 'Gagal mengupdate kategori!';
                $messageType = 'error';
            }
            mysqli_close($conn);
        }
    }
}

// Get category for edit
$editKategori = null;
if (isset($_GET['edit'])) {
    $editId = sanitize($_GET['edit']);
    $editKategori = queryOne("SELECT * FROM kategori_barang WHERE id_kategori = '$editId'");
}

require_once __DIR__ . '/../../config/paths.php';
include __DIR__ . '/../../includes/header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2><?php echo $editKategori ? 'EDIT KATEGORI BARANG' : 'TAMBAH KATEGORI BARANG'; ?></h2>
    </div>
    
    <form method="POST" action="">
        <input type="hidden" name="action" value="<?php echo $editKategori ? 'edit' : 'tambah'; ?>">
        <?php if ($editKategori): ?>
            <input type="hidden" name="id_kategori" value="<?php echo $editKategori['id_kategori']; ?>">
        <?php endif; ?>
        <div class="form-group">
            <label>TAMBAHKAN NAMA KATEGORI BARANG:</label>
            <input type="text" name="namaKategori" class="form-control" 
                   value="<?php echo $editKategori ? htmlspecialchars($editKategori['namaKategori']) : ''; ?>" 
                   required>
        </div>
        <div class="btn-group">
            <button type="submit" class="btn btn-success">SIMPAN</button>
            <a href="<?php echo getNavPath('admin/kategori_barang/index.php'); ?>" class="btn btn-secondary">BATAL</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>


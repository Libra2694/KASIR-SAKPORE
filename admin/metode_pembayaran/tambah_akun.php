<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

$pageTitle = 'Tambah/Edit Akun Pembayaran';

$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'tambah_akun') {
        $id_akunpembayaran = sanitize($_POST['id_akunpembayaran'] ?? '');
        $nama_pembayaran = sanitize($_POST['nama_pembayaran'] ?? '');
        $metode_pembayaran = sanitize($_POST['metode_pembayaran'] ?? '');
        $keterangan = sanitize($_POST['keterangan'] ?? '');
        
        if (!empty($id_akunpembayaran) && !empty($nama_pembayaran) && !empty($metode_pembayaran)) {
            $conn = getConnection();
            $sql = "INSERT INTO akun_pembayaran (id_akunpembayaran, metode_pembayaran, nama_pembayaran, keterangan) 
                    VALUES (?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ssss", $id_akunpembayaran, $metode_pembayaran, $nama_pembayaran, $keterangan);
            
            if (mysqli_stmt_execute($stmt)) {
                require_once __DIR__ . '/../../config/paths.php';
                header('Location: ' . getNavPath('admin/metode_pembayaran/index.php') . '?success=tambah_akun');
                exit();
            } else {
                $message = 'Gagal menambahkan akun pembayaran!';
                $messageType = 'error';
            }
            mysqli_close($conn);
        }
    } elseif ($action == 'edit_akun') {
        $id_akunpembayaran = sanitize($_POST['id_akunpembayaran'] ?? '');
        $nama_pembayaran = sanitize($_POST['nama_pembayaran'] ?? '');
        $metode_pembayaran = sanitize($_POST['metode_pembayaran'] ?? '');
        $keterangan = sanitize($_POST['keterangan'] ?? '');
        $id_lama = sanitize($_POST['id_lama'] ?? '');
        
        if (!empty($id_akunpembayaran) && !empty($nama_pembayaran)) {
            $conn = getConnection();
            $sql = "UPDATE akun_pembayaran SET id_akunpembayaran = ?, metode_pembayaran = ?, nama_pembayaran = ?, keterangan = ? 
                    WHERE id_akunpembayaran = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssss", $id_akunpembayaran, $metode_pembayaran, $nama_pembayaran, $keterangan, $id_lama);
            
            if (mysqli_stmt_execute($stmt)) {
                require_once __DIR__ . '/../../config/paths.php';
                header('Location: ' . getNavPath('admin/metode_pembayaran/index.php') . '?success=edit_akun');
                exit();
            } else {
                $message = 'Gagal mengupdate akun pembayaran!';
                $messageType = 'error';
            }
            mysqli_close($conn);
        }
    }
}

// Get metode pembayaran
$conn = getConnection();
$metode_pembayaran = queryArray("SELECT * FROM metode_pembayaran ORDER BY nama_metode");

// Get akun for edit
$editAkun = null;
if (isset($_GET['edit'])) {
    $editId = sanitize($_GET['edit']);
    $editAkun = queryOne("SELECT * FROM akun_pembayaran WHERE id_akunpembayaran = '$editId'");
}

// Generate ID
$count = queryOne("SELECT COUNT(*) as total FROM akun_pembayaran")['total'];
$nextId = str_pad($count + 1, 3, '0', STR_PAD_LEFT);

mysqli_close($conn);

require_once __DIR__ . '/../../config/paths.php';
include __DIR__ . '/../../includes/header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2><?php echo $editAkun ? 'EDIT AKUN PEMBAYARAN' : 'TAMBAH AKUN PEMBAYARAN'; ?></h2>
    </div>
    
    <form method="POST" action="">
        <input type="hidden" name="action" value="<?php echo $editAkun ? 'edit_akun' : 'tambah_akun'; ?>">
        <?php if ($editAkun): ?>
            <input type="hidden" name="id_lama" value="<?php echo $editAkun['id_akunpembayaran']; ?>">
        <?php endif; ?>
        <div class="form-group">
            <label>ID AKUN PEMBAYARAN:</label>
            <input type="text" name="id_akunpembayaran" class="form-control" 
                   value="<?php echo $editAkun ? htmlspecialchars($editAkun['id_akunpembayaran']) : $nextId; ?>" required>
        </div>
        <div class="form-group">
            <label>NAMA AKUN PEMBAYARAN:</label>
            <input type="text" name="nama_pembayaran" class="form-control" 
                   value="<?php echo $editAkun ? htmlspecialchars($editAkun['nama_pembayaran']) : ''; ?>" required>
        </div>
        <div class="form-group">
            <label>METODE PEMBAYARAN:</label>
            <select name="metode_pembayaran" class="form-control" required>
                <option value="">-- Pilih Metode --</option>
                <?php foreach ($metode_pembayaran as $metode): ?>
                    <option value="<?php echo $metode['id_metodepembayaran']; ?>" 
                            <?php echo ($editAkun && $editAkun['metode_pembayaran'] == $metode['id_metodepembayaran']) ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($metode['nama_metode']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>KETERANGAN:</label>
            <input type="text" name="keterangan" class="form-control" 
                   value="<?php echo $editAkun ? htmlspecialchars($editAkun['keterangan'] ?? '') : ''; ?>">
        </div>
        <div class="btn-group">
            <button type="submit" class="btn btn-success">SIMPAN</button>
            <a href="<?php echo getNavPath('admin/metode_pembayaran/index.php'); ?>" class="btn btn-secondary">BATAL</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>


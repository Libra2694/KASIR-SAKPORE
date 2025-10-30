<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

$pageTitle = 'Tambah Metode Pembayaran';

$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'tambah_metode') {
        $nama_metode = sanitize($_POST['nama_metode'] ?? '');
        if (!empty($nama_metode)) {
            $conn = getConnection();
            $count = queryOne("SELECT COUNT(*) as total FROM metode_pembayaran")['total'];
            $id = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
            
            $sql = "INSERT INTO metode_pembayaran (id_metodepembayaran, nama_metode) VALUES (?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "ss", $id, $nama_metode);
            
            if (mysqli_stmt_execute($stmt)) {
                require_once __DIR__ . '/../../config/paths.php';
                header('Location: ' . getNavPath('admin/metode_pembayaran/index.php') . '?success=tambah_metode');
                exit();
            } else {
                $message = 'Gagal menambahkan metode pembayaran!';
                $messageType = 'error';
            }
            mysqli_close($conn);
        }
    }
}

require_once __DIR__ . '/../../config/paths.php';
include __DIR__ . '/../../includes/header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>TAMBAH METODE PEMBAYARAN</h2>
    </div>
    
    <form method="POST" action="">
        <input type="hidden" name="action" value="tambah_metode">
        <div class="form-group">
            <label>NAMA METODE:</label>
            <input type="text" name="nama_metode" class="form-control" required>
        </div>
        <div class="btn-group">
            <button type="submit" class="btn btn-success">SIMPAN</button>
            <a href="<?php echo getNavPath('admin/metode_pembayaran/index.php'); ?>" class="btn btn-secondary">BATAL</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>


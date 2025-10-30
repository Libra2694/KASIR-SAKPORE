<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

$pageTitle = 'Tambah/Edit User';

$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'tambah') {
        $username = sanitize($_POST['username'] ?? '');
        $password = sanitize($_POST['password'] ?? '');
        $jabatan = sanitize($_POST['jabatan'] ?? '');
        $jenis_kelamin = sanitize($_POST['jenis_kelamin'] ?? '');
        $alamat = sanitize($_POST['alamat'] ?? '');
        $telepon = sanitize($_POST['telepon'] ?? '');
        
        if (!empty($username) && !empty($password) && !empty($jabatan)) {
            $conn = getConnection();
            // Generate ID
            $count = queryOne("SELECT COUNT(*) as total FROM user")['total'];
            $id = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
            
            $sql = "INSERT INTO user (id, username, password, jabatan, telepon, alamat, jenis_kelamin) 
                    VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssssss", $id, $username, $password, $jabatan, $telepon, $alamat, $jenis_kelamin);
            
            if (mysqli_stmt_execute($stmt)) {
                require_once __DIR__ . '/../../config/paths.php';
                header('Location: ' . getNavPath('admin/user/index.php') . '?success=tambah');
                exit();
            } else {
                $message = 'Gagal menambahkan user!';
                $messageType = 'error';
            }
            mysqli_close($conn);
        }
    } elseif ($action == 'edit') {
        $id = sanitize($_POST['id'] ?? '');
        $username = sanitize($_POST['username'] ?? '');
        $password = sanitize($_POST['password'] ?? '');
        $jabatan = sanitize($_POST['jabatan'] ?? '');
        $jenis_kelamin = sanitize($_POST['jenis_kelamin'] ?? '');
        $alamat = sanitize($_POST['alamat'] ?? '');
        $telepon = sanitize($_POST['telepon'] ?? '');
        
        if (!empty($id)) {
            $conn = getConnection();
            if (!empty($password)) {
                $sql = "UPDATE user SET username = ?, password = ?, jabatan = ?, telepon = ?, alamat = ?, jenis_kelamin = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sssssss", $username, $password, $jabatan, $telepon, $alamat, $jenis_kelamin, $id);
            } else {
                $sql = "UPDATE user SET username = ?, jabatan = ?, telepon = ?, alamat = ?, jenis_kelamin = ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "ssssss", $username, $jabatan, $telepon, $alamat, $jenis_kelamin, $id);
            }
            
            if (mysqli_stmt_execute($stmt)) {
                require_once __DIR__ . '/../../config/paths.php';
                header('Location: ' . getNavPath('admin/user/index.php') . '?success=edit');
                exit();
            } else {
                $message = 'Gagal mengupdate user!';
                $messageType = 'error';
            }
            mysqli_close($conn);
        }
    }
}

// Get user for edit
$editUser = null;
if (isset($_GET['edit'])) {
    $editId = sanitize($_GET['edit']);
    $editUser = queryOne("SELECT * FROM user WHERE id = '$editId'");
}

require_once __DIR__ . '/../../config/paths.php';
include __DIR__ . '/../../includes/header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2><?php echo $editUser ? 'EDIT USER' : 'TAMBAH USER'; ?></h2>
    </div>
    
    <form method="POST" action="">
        <input type="hidden" name="action" value="<?php echo $editUser ? 'edit' : 'tambah'; ?>">
        <?php if ($editUser): ?>
            <input type="hidden" name="id" value="<?php echo $editUser['id']; ?>">
        <?php endif; ?>
        <div class="form-row">
            <div class="form-group">
                <label>USERNAME:</label>
                <input type="text" name="username" class="form-control" 
                       value="<?php echo $editUser ? htmlspecialchars($editUser['username']) : ''; ?>" required>
            </div>
            <div class="form-group">
                <label>PASSWORD:</label>
                <input type="password" name="password" class="form-control" 
                       <?php echo $editUser ? '' : 'required'; ?> 
                       placeholder="<?php echo $editUser ? 'Kosongkan jika tidak ingin mengubah' : ''; ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>JENIS KELAMIN:</label>
                <select name="jenis_kelamin" class="form-control" required>
                    <option value="">-- Pilih Jenis Kelamin --</option>
                    <option value="Laki-laki" <?php echo ($editUser && $editUser['jenis_kelamin'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                    <option value="Perempuan" <?php echo ($editUser && $editUser['jenis_kelamin'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                </select>
            </div>
            <div class="form-group">
                <label>JABATAN:</label>
                <select name="jabatan" class="form-control" required>
                    <option value="">-- Pilih Jabatan --</option>
                    <option value="Admin" <?php echo ($editUser && $editUser['jabatan'] == 'Admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="Kasir" <?php echo ($editUser && $editUser['jabatan'] == 'Kasir') ? 'selected' : ''; ?>>Kasir</option>
                </select>
            </div>
        </div>
        <div class="form-group">
            <label>ALAMAT:</label>
            <textarea name="alamat" class="form-control" rows="3"><?php echo $editUser ? htmlspecialchars($editUser['alamat']) : ''; ?></textarea>
        </div>
        <div class="form-group">
            <label>NO. TELEPON:</label>
            <input type="text" name="telepon" class="form-control" 
                   value="<?php echo $editUser ? htmlspecialchars($editUser['telepon']) : ''; ?>">
        </div>
        <div class="btn-group">
            <button type="submit" class="btn btn-success">SIMPAN</button>
            <a href="<?php echo getNavPath('admin/user/index.php'); ?>" class="btn btn-secondary">BATAL</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/../../includes/footer.php'; ?>


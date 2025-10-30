<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

$pageTitle = 'Daftar User';

$message = '';
$messageType = '';

// Handle success message
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'tambah') {
        $message = 'User berhasil ditambahkan!';
        $messageType = 'success';
    } elseif ($_GET['success'] == 'edit') {
        $message = 'User berhasil diupdate!';
        $messageType = 'success';
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'hapus') {
        $id = sanitize($_POST['id'] ?? '');
        if (!empty($id) && $id != $_SESSION['id']) {
            $conn = getConnection();
            $sql = "DELETE FROM user WHERE id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = 'User berhasil dihapus!';
                $messageType = 'success';
            } else {
                $message = 'Gagal menghapus user!';
                $messageType = 'error';
            }
            mysqli_close($conn);
        } else {
            $message = 'Tidak dapat menghapus user sendiri!';
            $messageType = 'error';
        }
    }
}

// Get users
$search = $_GET['search'] ?? '';
$conn = getConnection();
if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $users = queryArray("SELECT * FROM user WHERE username LIKE '%$search%' ORDER BY id");
} else {
    $users = queryArray("SELECT * FROM user ORDER BY id");
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
        <h2>DAFTAR USER</h2>
    </div>
    <div class="search-filter">
        <a href="<?php echo getNavPath('admin/user/tambah.php'); ?>" class="btn btn-success">TAMBAH USER</a>
        <input type="text" id="search" placeholder="Cari user..." value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-success" onclick="searchUser()">CARI</button>
        <button class="btn btn-success" onclick="location.reload()">REFRESH</button>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>NO.</th>
                    <th>USERNAME</th>
                    <th>PASSWORD</th>
                    <th>JENIS KELAMIN</th>
                    <th>JABATAN</th>
                    <th>NO. TELEPON</th>
                    <th>ALAMAT</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="8" class="text-center">Tidak ada data</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $index => $user): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td>****</td>
                            <td><?php echo htmlspecialchars($user['jenis_kelamin'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($user['jabatan']); ?></td>
                            <td><?php echo htmlspecialchars($user['telepon'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($user['alamat'] ?? '-'); ?></td>
                            <td>
                                <a href="<?php echo getNavPath('admin/user/tambah.php?edit=' . $user['id']); ?>" class="btn btn-secondary" style="padding: 5px 10px;">EDIT</a>
                                <?php if ($user['id'] != $_SESSION['id']): ?>
                                    <form method="POST" style="display: inline;" onsubmit="return confirmDelete()">
                                        <input type="hidden" name="action" value="hapus">
                                        <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" class="btn btn-danger" style="padding: 5px 10px;">DELETE</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function searchUser() {
    const search = document.getElementById('search').value;
    window.location.href = '?search=' + encodeURIComponent(search);
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

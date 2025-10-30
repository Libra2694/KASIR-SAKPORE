<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

$pageTitle = 'Metode Pembayaran';

$message = '';
$messageType = '';

// Handle success message
if (isset($_GET['success'])) {
    if ($_GET['success'] == 'tambah_metode') {
        $message = 'Metode pembayaran berhasil ditambahkan!';
        $messageType = 'success';
    } elseif ($_GET['success'] == 'tambah_akun') {
        $message = 'Akun pembayaran berhasil ditambahkan!';
        $messageType = 'success';
    } elseif ($_GET['success'] == 'edit_akun') {
        $message = 'Akun pembayaran berhasil diupdate!';
        $messageType = 'success';
    }
}

// Handle delete
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'hapus_akun') {
        $id_akunpembayaran = sanitize($_POST['id_akunpembayaran'] ?? '');
        if (!empty($id_akunpembayaran)) {
            $conn = getConnection();
            $sql = "DELETE FROM akun_pembayaran WHERE id_akunpembayaran = ?";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "s", $id_akunpembayaran);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = 'Akun pembayaran berhasil dihapus!';
                $messageType = 'success';
            } else {
                $message = 'Gagal menghapus akun pembayaran!';
                $messageType = 'error';
            }
            mysqli_close($conn);
        }
    }
}

// Get akun pembayaran
$search = $_GET['search'] ?? '';
$conn = getConnection();
if (!empty($search)) {
    $search = mysqli_real_escape_string($conn, $search);
    $akun_pembayaran = queryArray("SELECT ap.*, mp.nama_metode FROM akun_pembayaran ap 
                                   LEFT JOIN metode_pembayaran mp ON ap.metode_pembayaran = mp.id_metodepembayaran 
                                   WHERE ap.nama_pembayaran LIKE '%$search%' ORDER BY ap.id_akunpembayaran");
} else {
    $akun_pembayaran = queryArray("SELECT ap.*, mp.nama_metode FROM akun_pembayaran ap 
                                   LEFT JOIN metode_pembayaran mp ON ap.metode_pembayaran = mp.id_metodepembayaran 
                                   ORDER BY ap.id_akunpembayaran");
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
        <h2>DAFTAR METODE PEMBAYARAN</h2>
    </div>
    <div class="search-filter">
        <a href="<?php echo getNavPath('admin/metode_pembayaran/tambah_metode.php'); ?>" class="btn btn-success">TAMBAH METODE PEMBAYARAN</a>
        <a href="<?php echo getNavPath('admin/metode_pembayaran/tambah_akun.php'); ?>" class="btn btn-success">TAMBAH AKUN PEMBAYARAN</a>
        <input type="text" id="search" placeholder="Cari akun pembayaran..." value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-success" onclick="searchAkun()">CARI</button>
        <button class="btn btn-success" onclick="location.reload()">REFRESH</button>
    </div>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>NO.</th>
                    <th>ID AKUN PEMBAYARAN</th>
                    <th>NAMA PEMBAYARAN</th>
                    <th>METODE PEMBAYARAN</th>
                    <th>KETERANGAN</th>
                    <th>AKSI</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($akun_pembayaran)): ?>
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada data</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($akun_pembayaran as $index => $akun): ?>
                        <tr>
                            <td><?php echo $index + 1; ?></td>
                            <td><?php echo $akun['id_akunpembayaran']; ?></td>
                            <td><?php echo htmlspecialchars($akun['nama_pembayaran']); ?></td>
                            <td><?php echo htmlspecialchars($akun['nama_metode'] ?? $akun['metode_pembayaran']); ?></td>
                            <td><?php echo htmlspecialchars($akun['keterangan'] ?? '-'); ?></td>
                            <td>
                                <a href="<?php echo getNavPath('admin/metode_pembayaran/tambah_akun.php?edit=' . $akun['id_akunpembayaran']); ?>" class="btn btn-secondary" style="padding: 5px 10px;">EDIT</a>
                                <form method="POST" style="display: inline;" onsubmit="return confirmDelete()">
                                    <input type="hidden" name="action" value="hapus_akun">
                                    <input type="hidden" name="id_akunpembayaran" value="<?php echo $akun['id_akunpembayaran']; ?>">
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
function searchAkun() {
    const search = document.getElementById('search').value;
    window.location.href = '?search=' + encodeURIComponent(search);
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>

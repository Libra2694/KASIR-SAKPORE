<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../includes/functions.php';

requireAdmin();

$pageTitle = 'Pembelian Barang';

$message = '';
$messageType = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    if ($action == 'tambah') {
        $id_pembelian = sanitize($_POST['id_pembelian'] ?? '');
        $id_barang = sanitize($_POST['id_barang'] ?? '');
        $nama_barang = sanitize($_POST['nama_barang'] ?? '');
        $kategori = sanitize($_POST['kategori'] ?? '');
        $satuan = sanitize($_POST['satuan'] ?? '');
        $harga_satuan = str_replace('.', '', $_POST['harga_satuan'] ?? '0');
        $jumlah_beli = intval($_POST['jumlah_beli'] ?? '0');
        $total_harga = str_replace('.', '', $_POST['total_harga'] ?? '0');
        $nama_supplier = sanitize($_POST['nama_supplier'] ?? '');
        $perusahaan_supplier = sanitize($_POST['perusahaan_supplier'] ?? '');
        
        if (!empty($id_pembelian) && !empty($id_barang) && !empty($nama_barang) && $jumlah_beli > 0) {
            $conn = getConnection();
            mysqli_begin_transaction($conn);
            
            try {
                // Insert pembelian
                $sql = "INSERT INTO pembelian_barang (id_pembelian, tanggal, id_barang, nama_barang, kategori, satuan, harga_satuan, jumlah_beli, total_harga, nama_supplier, perusahaan_supplier) 
                        VALUES (?, CURDATE(), ?, ?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "sssssddiss", $id_pembelian, $id_barang, $nama_barang, $kategori, $satuan, $harga_satuan, $jumlah_beli, $total_harga, $nama_supplier, $perusahaan_supplier);
                mysqli_stmt_execute($stmt);
                
                // Update stok barang
                $sql_update = "UPDATE daftar_barang SET stok = stok + ? WHERE id_barang = ?";
                $stmt_update = mysqli_prepare($conn, $sql_update);
                mysqli_stmt_bind_param($stmt_update, "is", $jumlah_beli, $id_barang);
                mysqli_stmt_execute($stmt_update);
                
                mysqli_commit($conn);
                $message = 'Pembelian berhasil ditambahkan!';
                $messageType = 'success';
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $message = 'Gagal menambahkan pembelian: ' . $e->getMessage();
                $messageType = 'error';
            }
            
            mysqli_close($conn);
        }
    }
}

// Get categories
$conn = getConnection();
$kategori = queryArray("SELECT * FROM kategori_barang ORDER BY namaKategori");

// Get barang
$barang = queryArray("SELECT * FROM daftar_barang ORDER BY nama_barang");

// Generate ID
$count = queryOne("SELECT COUNT(*) as total FROM pembelian_barang")['total'];
$nextId = 'PB-' . date('Ymd') . '-' . str_pad($count + 1, 3, '0', STR_PAD_LEFT);

mysqli_close($conn);

require_once __DIR__ . '/../../config/paths.php';
include __DIR__ . '/../../includes/header.php';
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>TAMBAH PEMBELIAN BARANG</h2>
    </div>
    <form method="POST" action="" id="formPembelian">
        <input type="hidden" name="action" value="tambah">
        <div class="form-group">
            <label>ID PEMBELIAN:</label>
            <input type="text" name="id_pembelian" class="form-control" value="<?php echo $nextId; ?>" readonly>
        </div>
        <div class="form-group">
            <label>PILIH BARANG:</label>
            <select name="id_barang" id="id_barang" class="form-control" onchange="loadBarang()" required>
                <option value="">-- Pilih Barang --</option>
                <?php foreach ($barang as $brg): ?>
                    <option value="<?php echo $brg['id_barang']; ?>" 
                            data-nama="<?php echo htmlspecialchars($brg['nama_barang']); ?>"
                            data-kategori="<?php echo $brg['kategori']; ?>"
                            data-satuan="<?php echo htmlspecialchars($brg['satuan']); ?>"
                            data-harga-pokok="<?php echo $brg['harga_pokok']; ?>">
                        <?php echo htmlspecialchars($brg['id_barang'] . ' - ' . $brg['nama_barang']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>NAMA BARANG:</label>
                <input type="text" name="nama_barang" id="nama_barang" class="form-control" required>
            </div>
            <div class="form-group">
                <label>KATEGORI:</label>
                <select name="kategori" id="kategori" class="form-control" required>
                    <option value="">-- Pilih Kategori --</option>
                    <?php foreach ($kategori as $kat): ?>
                        <option value="<?php echo $kat['id_kategori']; ?>">
                            <?php echo htmlspecialchars($kat['namaKategori']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>SATUAN:</label>
                <input type="text" name="satuan" id="satuan" class="form-control" required>
            </div>
            <div class="form-group">
                <label>HARGA SATUAN:</label>
                <input type="text" name="harga_satuan" id="harga_satuan" class="form-control" data-currency 
                       onkeyup="calculateTotal()" required>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>JUMLAH BELI:</label>
                <input type="number" name="jumlah_beli" id="jumlah_beli" class="form-control" 
                       min="1" onkeyup="calculateTotal()" required>
            </div>
            <div class="form-group">
                <label>TOTAL HARGA:</label>
                <input type="hidden" name="total_harga" id="total_harga_value">
                <input type="text" id="total_harga" class="form-control" readonly>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>NAMA SUPPLIER:</label>
                <input type="text" name="nama_supplier" class="form-control">
            </div>
            <div class="form-group">
                <label>PERUSAHAAN SUPPLIER:</label>
                <input type="text" name="perusahaan_supplier" class="form-control">
            </div>
        </div>
        <button type="submit" class="btn btn-success">SIMPAN</button>
    </form>
</div>

<script>
function loadBarang() {
    const select = document.getElementById('id_barang');
    const option = select.options[select.selectedIndex];
    
    if (option.value) {
        document.getElementById('nama_barang').value = option.getAttribute('data-nama');
        document.getElementById('kategori').value = option.getAttribute('data-kategori');
        document.getElementById('satuan').value = option.getAttribute('data-satuan');
        
        // Auto-fill harga satuan dari harga pokok barang
        const hargaPokok = parseFloat(option.getAttribute('data-harga-pokok') || 0);
        document.getElementById('harga_satuan').value = formatCurrency(hargaPokok);
        
        // Recalculate total jika sudah ada jumlah beli
        const jumlahBeli = parseInt(document.getElementById('jumlah_beli').value || 0);
        if (jumlahBeli > 0) {
            calculateTotal();
        }
    } else {
        // Clear fields if no item selected
        document.getElementById('nama_barang').value = '';
        document.getElementById('kategori').value = '';
        document.getElementById('satuan').value = '';
        document.getElementById('harga_satuan').value = '';
        document.getElementById('total_harga').value = '';
    }
}

function calculateTotal() {
    const harga = parseFloat(document.getElementById('harga_satuan').value.replace(/[^\d]/g, '') || 0);
    const jumlah = parseInt(document.getElementById('jumlah_beli').value || 0);
    const total = harga * jumlah;
    
    // Update display field
    document.getElementById('total_harga').value = formatCurrency(total);
    // Update hidden field for form submission
    document.getElementById('total_harga_value').value = total;
}

function formatCurrency(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}
</script>

<?php include __DIR__ . '/../../includes/footer.php'; ?>


<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/paths.php';
require_once __DIR__ . '/../includes/functions.php';

requireKasir();

$pageTitle = 'Transaksi Kasir';

$message = '';
$messageType = '';

// Handle save transaction
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'save_transaksi') {
    $idtransaksi = generateIdTransaksi();
    $kasir = $_SESSION['username'];
    $idkasir = $_SESSION['id'];
    $subtotal = str_replace('.', '', $_POST['subtotal'] ?? '0');
    $diskon = str_replace('.', '', $_POST['diskon'] ?? '0');
    $total_akhir = str_replace('.', '', $_POST['total_akhir'] ?? '0');
    $bayar = str_replace('.', '', $_POST['bayar'] ?? '0');
    $kembalian = str_replace('.', '', $_POST['kembalian'] ?? '0');
    $metode_pembayaran = sanitize($_POST['metode_pembayaran'] ?? '');
    $akun_pembayaran = sanitize($_POST['akun_pembayaran'] ?? '');
    
    $items = json_decode($_POST['items'] ?? '[]', true);
    $jumlah_beli = count($items);
    
    $conn = getConnection();
    mysqli_begin_transaction($conn);
    
    try {
        // Insert transaksi
        $sql = "INSERT INTO transaksi (idtransaksi, tanggal, kasir, idkasir, jumlah_beli, subtotal, diskon, total_akhir, bayar, kembalian, metode_pembayaran, akun_pembayaran) 
                VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sssiiddddss", $idtransaksi, $kasir, $idkasir, $jumlah_beli, $subtotal, $diskon, $total_akhir, $bayar, $kembalian, $metode_pembayaran, $akun_pembayaran);
        mysqli_stmt_execute($stmt);
        
        // Insert detail transaksi dan update stok
        foreach ($items as $item) {
            $id_detail = $idtransaksi . '-' . $item['id_barang'];
            $sql_detail = "INSERT INTO detai_transaksi (id_detail, id_transaksi, id_barang, nama_barang, harga_jual, jumlah_beli, total) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt_detail = mysqli_prepare($conn, $sql_detail);
            mysqli_stmt_bind_param($stmt_detail, "ssssiid", $id_detail, $idtransaksi, $item['id_barang'], $item['nama_barang'], $item['harga_jual'], $item['jumlah_beli'], $item['total']);
            mysqli_stmt_execute($stmt_detail);
            
            // Update stok menggunakan koneksi yang sama dalam transaction
            $id_barang = mysqli_real_escape_string($conn, $item['id_barang']);
            $jumlah = (int)$item['jumlah_beli'];
            $sql_update = "UPDATE daftar_barang SET stok = stok - ? WHERE id_barang = ?";
            $stmt_update = mysqli_prepare($conn, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "is", $jumlah, $id_barang);
            mysqli_stmt_execute($stmt_update);
            mysqli_stmt_close($stmt_update);
        }
        
        mysqli_commit($conn);
        mysqli_close($conn);
        
        // Clear cart dan redirect
        header('Location: ' . getNavPath('kasir/transaksi.php') . '?success=1');
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conn);
        mysqli_close($conn);
        $message = 'Gagal menyimpan transaksi: ' . $e->getMessage();
        $messageType = 'error';
    }
}

// Handle add item to cart (AJAX)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action']) && $_POST['action'] == 'add_item') {
                header('Content-Type: application/json');
    $id_barang = sanitize($_POST['id_barang'] ?? '');
    $jumlah = intval($_POST['jumlah'] ?? 1);
    
    $barang = queryOne("SELECT * FROM daftar_barang WHERE id_barang = '$id_barang'");
    
    if ($barang && $barang['stok'] >= $jumlah) {
        echo json_encode([
            'success' => true,
            'barang' => [
                'id_barang' => $barang['id_barang'],
                'nama_barang' => $barang['nama_barang'],
                'harga_jual' => $barang['harga_jual'],
                'jumlah_beli' => $jumlah,
                'total' => $barang['harga_jual'] * $jumlah
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Stok tidak cukup atau barang tidak ditemukan']);
    }
    exit();
}

// Get available barang
$conn = getConnection();
$search_barang = $_GET['search_barang'] ?? '';
if (!empty($search_barang)) {
    $search_barang = mysqli_real_escape_string($conn, $search_barang);
    $barang_list = queryArray("SELECT * FROM daftar_barang WHERE nama_barang LIKE '%$search_barang%' OR id_barang LIKE '%$search_barang%' ORDER BY nama_barang LIMIT 20");
} else {
    $barang_list = queryArray("SELECT * FROM daftar_barang ORDER BY nama_barang LIMIT 20");
}

// Get metode pembayaran
$metode_pembayaran = queryArray("SELECT * FROM metode_pembayaran ORDER BY nama_metode");
mysqli_close($conn);

include __DIR__ . '/../includes/header.php';

// Path untuk AJAX call
$akunPembayaranPath = getNavPath('kasir/get_akun_pembayaran.php');
?>

<?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>"><?php echo $message; ?></div>
<?php endif; ?>

<?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
    <div class="alert alert-success">Transaksi berhasil disimpan!</div>
    <script>
        localStorage.removeItem('cart');
        setTimeout(function() {
            window.location.href = '<?php echo getNavPath("kasir/dashboard.php"); ?>';
        }, 2000);
    </script>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h2>TRANSAKSI BARU</h2>
    </div>
    <p><strong>KASIR:</strong> <?php echo $_SESSION['username']; ?></p>
    <p><strong>TANGGAL:</strong> <?php echo date('d-m-Y H:i'); ?></p>
    <p><strong>ID TRANSAKSI:</strong> <span id="id_transaksi"><?php echo generateIdTransaksi(); ?></span></p>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
    <!-- Left: Form Input -->
    <div class="card">
        <div class="card-header">
            <h3>INPUT BARANG</h3>
        </div>
        <div class="form-group">
            <label>ID BARANG / CARI BARANG:</label>
            <div style="display: flex; gap: 10px;">
                <input type="text" id="search_barang" class="form-control" placeholder="Cari barang..." 
                       onkeyup="searchBarang()" onkeypress="if(event.key==='Enter') event.preventDefault();">
                <button class="btn btn-success" onclick="searchBarang()">CARI</button>
            </div>
        </div>
        
        <div class="form-group">
            <label>DAFTAR BARANG TERSEDIA:</label>
            <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px;">
                <table style="width: 100%; font-size: 12px;">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NAMA</th>
                            <th>HARGA</th>
                            <th>STOK</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="barang_list">
                        <?php foreach ($barang_list as $brg): ?>
                            <tr>
                                <td><?php echo $brg['id_barang']; ?></td>
                                <td><?php echo htmlspecialchars($brg['nama_barang']); ?></td>
                                <td><?php echo formatRupiah($brg['harga_jual']); ?></td>
                                <td><?php echo $brg['stok']; ?></td>
                                <td>
                                    <input type="number" id="qty_<?php echo $brg['id_barang']; ?>" value="1" min="1" max="<?php echo $brg['stok']; ?>" style="width: 60px;">
                                    <button class="btn btn-success" style="padding: 5px 10px; font-size: 11px;" 
                                            onclick="addToCart('<?php echo $brg['id_barang']; ?>', '<?php echo htmlspecialchars($brg['nama_barang']); ?>', <?php echo $brg['harga_jual']; ?>, <?php echo $brg['stok']; ?>)">
                                        TAMBAH
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="form-group">
            <label>KERANJANG BELANJA:</label>
            <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ddd;">
                <table style="width: 100%;" id="cart_table">
                    <thead>
                        <tr>
                            <th>NO</th>
                            <th>ID BARANG</th>
                            <th>NAMA BARANG</th>
                            <th>HARGA</th>
                            <th>JUMLAH</th>
                            <th>TOTAL</th>
                            <th>AKSI</th>
                        </tr>
                    </thead>
                    <tbody id="cart_body">
                        <tr>
                            <td colspan="7" class="text-center">Keranjang kosong</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="btn-group">
            <button class="btn btn-danger" onclick="clearCart()">CLEAR KERANJANG [F4]</button>
        </div>
    </div>
    
    <!-- Right: Payment -->
    <div class="card">
        <div class="card-header">
            <h3>PEMBAYARAN</h3>
        </div>
        <div class="form-group">
            <label>SUB TOTAL:</label>
            <input type="text" id="subtotal" class="form-control" readonly value="0">
        </div>
        <div class="form-group">
            <label>DISKON:</label>
            <input type="text" id="diskon" class="form-control" value="0" onkeyup="calculatePayment()" data-currency>
        </div>
        <div class="form-group">
            <label>TOTAL AKHIR:</label>
            <input type="text" id="total_akhir" class="form-control" readonly value="0">
        </div>
        <div class="form-group">
            <label>METODE PEMBAYARAN:</label>
            <select id="metode_pembayaran" class="form-control" onchange="loadAkunPembayaran()">
                <option value="">-- Pilih Metode --</option>
                <?php foreach ($metode_pembayaran as $metode): ?>
                    <option value="<?php echo $metode['id_metodepembayaran']; ?>">
                        <?php echo htmlspecialchars($metode['nama_metode']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>AKUN PEMBAYARAN:</label>
            <select id="akun_pembayaran" class="form-control">
                <option value="">-- Pilih Akun --</option>
            </select>
        </div>
        <div class="form-group">
            <label>BAYAR:</label>
            <input type="text" id="bayar" class="form-control" value="0" onkeyup="calculatePayment()" data-currency>
        </div>
        <div class="form-group">
            <label>KEMBALIAN:</label>
            <input type="text" id="kembalian" class="form-control" readonly value="0">
        </div>
        <button class="btn btn-success" style="width: 100%; margin-top: 20px;" onclick="saveTransaction()">
            SAVE TRANSAKSI [SHIFT + ENTER]
        </button>
    </div>
</div>

<script>
let cart = JSON.parse(localStorage.getItem('cart') || '[]');

function loadCart() {
    const tbody = document.getElementById('cart_body');
    if (cart.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">Keranjang kosong</td></tr>';
        calculatePayment();
        return;
    }
    
    let html = '';
    cart.forEach((item, index) => {
        html += `
            <tr>
                <td>${index + 1}</td>
                <td>${item.id_barang}</td>
                <td>${item.nama_barang}</td>
                <td>${formatCurrency(item.harga_jual)}</td>
                <td><input type="number" value="${item.jumlah_beli}" min="1" 
                           onchange="updateCart(${index}, this.value)" style="width: 60px;"></td>
                <td>${formatCurrency(item.total)}</td>
                <td><button class="btn btn-danger" style="padding: 5px 10px;" onclick="removeFromCart(${index})">HAPUS</button></td>
            </tr>
        `;
    });
    tbody.innerHTML = html;
    calculatePayment();
}

function addToCart(id_barang, nama_barang, harga_jual, stok) {
    const qty = parseInt(document.getElementById('qty_' + id_barang)?.value || 1);
    
    if (qty > stok) {
        alert('Stok tidak cukup!');
        return;
    }
    
    const existingIndex = cart.findIndex(item => item.id_barang === id_barang);
    if (existingIndex >= 0) {
        const newQty = cart[existingIndex].jumlah_beli + qty;
        if (newQty > stok) {
            alert('Stok tidak cukup!');
            return;
        }
        cart[existingIndex].jumlah_beli = newQty;
        cart[existingIndex].total = cart[existingIndex].harga_jual * newQty;
    } else {
        cart.push({
            id_barang: id_barang,
            nama_barang: nama_barang,
            harga_jual: harga_jual,
            jumlah_beli: qty,
            total: harga_jual * qty
        });
    }
    
    localStorage.setItem('cart', JSON.stringify(cart));
    loadCart();
}

function removeFromCart(index) {
    cart.splice(index, 1);
    localStorage.setItem('cart', JSON.stringify(cart));
    loadCart();
}

function updateCart(index, qty) {
    qty = parseInt(qty);
    if (qty < 1) {
        removeFromCart(index);
        return;
    }
    cart[index].jumlah_beli = qty;
    cart[index].total = cart[index].harga_jual * qty;
    localStorage.setItem('cart', JSON.stringify(cart));
    loadCart();
}

function clearCart() {
    if (confirm('Yakin ingin mengosongkan keranjang?')) {
        cart = [];
        localStorage.removeItem('cart');
        loadCart();
    }
}

function calculatePayment() {
    const subtotal = cart.reduce((sum, item) => sum + item.total, 0);
    const diskon = parseFloat(document.getElementById('diskon').value.replace(/[^\d]/g, '') || 0);
    const total_akhir = subtotal - diskon;
    const bayar = parseFloat(document.getElementById('bayar').value.replace(/[^\d]/g, '') || 0);
    const kembalian = bayar - total_akhir;
    
    document.getElementById('subtotal').value = formatCurrency(subtotal);
    document.getElementById('total_akhir').value = formatCurrency(total_akhir);
    document.getElementById('kembalian').value = formatCurrency(kembalian > 0 ? kembalian : 0);
}

function loadAkunPembayaran() {
    const metode = document.getElementById('metode_pembayaran').value;
    const select = document.getElementById('akun_pembayaran');
    
    if (!metode) {
        select.innerHTML = '<option value="">-- Pilih Akun --</option>';
        return;
    }
    
    fetch('<?php echo $akunPembayaranPath; ?>?metode=' + metode)
        .then(response => response.json())
        .then(data => {
            let html = '<option value="">-- Pilih Akun --</option>';
            data.forEach(akun => {
                html += `<option value="${akun.id_akunpembayaran}">${akun.nama_pembayaran}</option>`;
            });
            select.innerHTML = html;
        });
}

function saveTransaction() {
    if (cart.length === 0) {
        alert('Keranjang kosong!');
        return;
    }
    
    const subtotal = cart.reduce((sum, item) => sum + item.total, 0);
    const diskon = parseFloat(document.getElementById('diskon').value.replace(/[^\d]/g, '') || 0);
    const total_akhir = subtotal - diskon;
    const bayar = parseFloat(document.getElementById('bayar').value.replace(/[^\d]/g, '') || 0);
    const kembalian = bayar - total_akhir;
    
    if (bayar < total_akhir) {
        alert('Jumlah bayar kurang!');
        return;
    }
    
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="action" value="save_transaksi">
        <input type="hidden" name="subtotal" value="${subtotal}">
        <input type="hidden" name="diskon" value="${diskon}">
        <input type="hidden" name="total_akhir" value="${total_akhir}">
        <input type="hidden" name="bayar" value="${bayar}">
        <input type="hidden" name="kembalian" value="${kembalian}">
        <input type="hidden" name="metode_pembayaran" value="${document.getElementById('metode_pembayaran').value}">
        <input type="hidden" name="akun_pembayaran" value="${document.getElementById('akun_pembayaran').value}">
        <input type="hidden" name="items" value='${JSON.stringify(cart)}'>
    `;
    document.body.appendChild(form);
    form.submit();
}

function searchBarang() {
    const search = document.getElementById('search_barang').value;
    window.location.href = '?search_barang=' + encodeURIComponent(search);
}

function formatCurrency(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

// Load cart on page load
loadCart();
</script>

<?php include __DIR__ . '/../includes/footer.php'; ?>


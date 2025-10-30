-- Database untuk Aplikasi Kasir SAKPORE
CREATE DATABASE IF NOT EXISTS kasir_sakpore CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE kasir_sakpore;

-- Tabel User
CREATE TABLE IF NOT EXISTS user (
    userID INT AUTO_INCREMENT PRIMARY KEY,
    id VARCHAR(50) UNIQUE,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    jabatan ENUM('Admin', 'Kasir') NOT NULL,
    telepon VARCHAR(20),
    alamat TEXT,
    jenis_kelamin ENUM('Laki-laki', 'Perempuan') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Metode Pembayaran
CREATE TABLE IF NOT EXISTS metode_pembayaran (
    pembayaranID INT AUTO_INCREMENT PRIMARY KEY,
    id_metodepembayaran VARCHAR(50) UNIQUE NOT NULL,
    nama_metode VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Akun Pembayaran
CREATE TABLE IF NOT EXISTS akun_pembayaran (
    akunID INT AUTO_INCREMENT PRIMARY KEY,
    id_akunpembayaran VARCHAR(50) UNIQUE NOT NULL,
    metode_pembayaran VARCHAR(50) NOT NULL,
    nama_pembayaran VARCHAR(100) NOT NULL,
    keterangan TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Kategori Barang
CREATE TABLE IF NOT EXISTS kategori_barang (
    kategoriID INT AUTO_INCREMENT PRIMARY KEY,
    id_kategori VARCHAR(50) UNIQUE NOT NULL,
    namaKategori VARCHAR(100) NOT NULL,
    tanggalinput DATE NOT NULL DEFAULT (CURRENT_DATE)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Daftar Barang
CREATE TABLE IF NOT EXISTS daftar_barang (
    barangID INT AUTO_INCREMENT PRIMARY KEY,
    id_barang VARCHAR(50) UNIQUE NOT NULL,
    nama_barang VARCHAR(255) NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    satuan VARCHAR(20) NOT NULL,
    harga_pokok DECIMAL(15,2) NOT NULL DEFAULT 0,
    ppn DECIMAL(15,2) NOT NULL DEFAULT 0,
    harga_jual DECIMAL(15,2) NOT NULL DEFAULT 0,
    stok INT NOT NULL DEFAULT 0,
    FOREIGN KEY (kategori) REFERENCES kategori_barang(id_kategori) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Transaksi
CREATE TABLE IF NOT EXISTS transaksi (
    transaksiID INT AUTO_INCREMENT PRIMARY KEY,
    idtransaksi VARCHAR(50) UNIQUE NOT NULL,
    tanggal DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    kasir VARCHAR(50) NOT NULL,
    idkasir VARCHAR(50) NOT NULL,
    jumlah_beli INT NOT NULL DEFAULT 0,
    subtotal DECIMAL(15,2) NOT NULL DEFAULT 0,
    diskon DECIMAL(15,2) NOT NULL DEFAULT 0,
    total_akhir DECIMAL(15,2) NOT NULL DEFAULT 0,
    bayar DECIMAL(15,2) NOT NULL DEFAULT 0,
    kembalian DECIMAL(15,2) NOT NULL DEFAULT 0,
    metode_pembayaran VARCHAR(50),
    akun_pembayaran VARCHAR(50),
    FOREIGN KEY (idkasir) REFERENCES user(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Detail Transaksi
CREATE TABLE IF NOT EXISTS detai_transaksi (
    detailID INT AUTO_INCREMENT PRIMARY KEY,
    id_detail VARCHAR(50) UNIQUE NOT NULL,
    id_transaksi VARCHAR(50) NOT NULL,
    id_barang VARCHAR(50) NOT NULL,
    nama_barang VARCHAR(255) NOT NULL,
    harga_jual DECIMAL(15,2) NOT NULL,
    jumlah_beli INT NOT NULL,
    total DECIMAL(15,2) NOT NULL,
    FOREIGN KEY (id_transaksi) REFERENCES transaksi(idtransaksi) ON DELETE CASCADE,
    FOREIGN KEY (id_barang) REFERENCES daftar_barang(id_barang) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabel Pembelian Barang
CREATE TABLE IF NOT EXISTS pembelian_barang (
    pembelianID INT AUTO_INCREMENT PRIMARY KEY,
    id_pembelian VARCHAR(50) UNIQUE NOT NULL,
    tanggal DATE NOT NULL DEFAULT (CURRENT_DATE),
    id_barang VARCHAR(50) NOT NULL,
    nama_barang VARCHAR(255) NOT NULL,
    kategori VARCHAR(50) NOT NULL,
    satuan VARCHAR(20) NOT NULL,
    harga_satuan DECIMAL(15,2) NOT NULL,
    jumlah_beli INT NOT NULL,
    total_harga DECIMAL(15,2) NOT NULL,
    nama_supplier VARCHAR(100),
    perusahaan_supplier VARCHAR(255),
    FOREIGN KEY (id_barang) REFERENCES daftar_barang(id_barang) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Data Default
-- User Admin
INSERT INTO user (id, username, password, jabatan, telepon, alamat, jenis_kelamin) VALUES
('001', 'admin', 'admin', 'Admin', '081234567890', 'Alamat Admin', 'Laki-laki'),
('002', 'kasir', 'kasir', 'Kasir', '081234567891', 'Alamat Kasir', 'Laki-laki');

-- Metode Pembayaran
INSERT INTO metode_pembayaran (id_metodepembayaran, nama_metode) VALUES
('001', 'TUNAI'),
('002', 'E - WALLET'),
('003', 'KREDIT / DEBIT');

-- Akun Pembayaran
INSERT INTO akun_pembayaran (id_akunpembayaran, metode_pembayaran, nama_pembayaran, keterangan) VALUES
('002', 'TUNAI', 'UANG TUNAI', 'UANG ASLI'),
('003', 'E - WALLET', 'DANA', ''),
('004', 'KREDIT / DEBIT', 'BANK SAKPORE', '');

-- Kategori Barang
INSERT INTO kategori_barang (id_kategori, namaKategori, tanggalinput) VALUES
('001', 'snack', CURDATE()),
('002', 'minuman_botol', CURDATE()),
('003', 'mie cup instan', CURDATE()),
('004', 'mie instan', CURDATE());

-- Daftar Barang
INSERT INTO daftar_barang (id_barang, nama_barang, kategori, satuan, harga_pokok, ppn, harga_jual, stok) VALUES
('brg001', 'coca cola', '002', 'botol', 4000, 1000, 5000, 12),
('brg002', 'pop mie', '003', 'pcs', 4000, 1000, 5000, 0),
('brg003', 'indomie aceh', '004', 'pcs', 3500, 500, 4000, 10),
('brg004', 'mie sedap ayam bawang', '003', 'pcs', 4000, 1000, 5000, 0),
('brg005', 'wafer nabati keju', '001', 'pcs', 1500, 500, 2000, 45),
('brg006', 'permen', '001', 'pcs', 300, 200, 500, 43);


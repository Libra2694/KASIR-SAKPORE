## Aplikasi Kasir SAKPORE (PHP Native)

![Made with PHP](https://img.shields.io/badge/Made%20with-PHP-777BB4?logo=php&logoColor=white)
![Database MySQL](https://img.shields.io/badge/Database-MySQL-00758F?logo=mysql&logoColor=white)
![License](https://img.shields.io/badge/license-MIT-green)
![Status](https://img.shields.io/badge/status-Active-success)

> Aplikasi kasir berbasis web menggunakan PHP Native dan MySQLi, dengan role Admin dan Kasir. Fokus pada struktur kode rapi, keamanan dasar (prepared statements), dan kemudahan deploy di Laragon/XAMPP. Sudah dilengkapi grafik statistik pada dashboard Admin (Chart.js).

### 🔗 Daftar Isi
- [Fitur Utama](#-fitur-utama)
- [Teknologi](#-teknologi)
- [Struktur Proyek](#-struktur-proyek)
- [Persiapan Database](#-persiapan-database)
- [Kredensial Login](#-kredensial-login-default)
- [Cara Menjalankan](#-cara-menjalankan-laragonxampp)
- [Konfigurasi Penting](#-konfigurasi-penting)
- [Screenshot](#-screenshot)
- [Catatan Implementasi](#-catatan-implementasi)
- [Lisensi](#-lisensi)

### ✨ Fitur Utama
- ✅ **Autentikasi & Autorisasi**: Login, logout, session, role Admin/Kasir
- ✅ **Master Data**:
  - Kategori Barang (CRUD, form tambah/edit terpisah)
  - Daftar Barang (CRUD, form tambah/edit terpisah, tambah stok)
  - User Management (CRUD, form terpisah)
  - Metode & Akun Pembayaran (CRUD, form terpisah)
- ✅ **Transaksi**:
  - Kasir: Simpan transaksi + detail, update stok atomic (transaction)
  - Pembelian Barang: Harga satuan auto dari harga pokok, total auto
- ✅ **Laporan**: Transaksi, Pembelian, Keuangan, Detail Transaksi
- ✅ **Dashboard Admin**: 4 grafik (Chart.js) — transaksi 7 hari, pendapatan 7 hari, income vs expense 6 bulan, top 5 kategori
- ✅ **Path Helper**: `config/paths.php` memastikan link/asset aman saat di subfolder

### 🧰 Teknologi
- PHP Native (MySQLi, prepared statements)
- MySQL
- HTML, CSS, JavaScript (vanilla)
- Chart.js untuk visualisasi

### 🗂️ Struktur Proyek
```
.
├── index.php
├── login.php
├── logout.php
├── .htaccess
├── database.sql
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── main.js
├── config/
│   ├── database.php
│   ├── paths.php
│   └── session.php
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── functions.php
├── admin/
│   ├── dashboard.php
│   ├── daftar_barang/
│   │   ├── index.php
│   │   ├── tambah.php
│   │   └── tambah_stok.php
│   ├── kategori_barang/
│   │   ├── index.php
│   │   └── tambah.php
│   ├── metode_pembayaran/
│   │   ├── index.php
│   │   ├── tambah_metode.php
│   │   └── tambah_akun.php
│   ├── pembelian_barang/
│   │   └── index.php
│   └── laporan/
│       ├── transaksi.php
│       ├── pembelian.php
│       ├── keuangan.php
│       └── detail_transaksi.php
└── kasir/
    ├── dashboard.php
    ├── transaksi.php
    └── daftar_barang.php
```

### 🗄️ Persiapan Database
1. Buat database MySQL baru atau gunakan yang sudah ada.
2. Import file `database.sql` ke database tersebut (berisi schema + sample data).
3. Sesuaikan kredensial koneksi di `config/database.php` bila diperlukan.

### 🔐 Kredensial Login (Default)
- Username: `admin` / Password: `admin` (Admin)
- Username: `kasir` / Password: `kasir` (Kasir)

### 🚀 Cara Menjalankan (Laragon/XAMPP)
1. Clone atau salin project ini ke folder web server (misalnya Laragon: `C:\laragon\www\KASIR-SAKPORE
`).
2. Pastikan `Apache` dan `MySQL` berjalan.
3. Import `database.sql`.
4. Akses lewat browser ke alamat subfolder Anda, contoh: `http://localhost/KASIR-SAKPORE
/`.

Catatan: Proyek ini memakai helper path relatif `config/paths.php` dengan fungsi `getNavPath()` dan `getAssetPath()` agar semua link dan asset tetap benar meskipun berada di subfolder.

### ⚙️ Konfigurasi Penting
- `config/database.php`: fungsi `getConnection()` dan helper `query`, `queryArray`, `queryOne`.
- `config/session.php`: helper login/logout, role guard (`requireAdmin`, `requireKasir`).
- `config/paths.php`: `getNavPath($targetPath)` dan `getAssetPath($file)` untuk path relatif.
- `includes/functions.php`: utilitas (format rupiah, sanitize, generator ID, stok, dll). Fungsi `updateStokBarang` didesain agar aman dipakai dalam transaksi aktif.

### 🖼️ Screenshot
<div align="center">

| Login | Dashboard Admin | Dashboard Kasir |
|---|---|---|
| ![Login](assets/screenshot/screenshot-login.png) | ![Dashboard Admin](assets/screenshot/screenshot-admin-dashboard.png) | ![Dashboard Kasir](assets/screenshot/screenshot-kasir-dashboard.png) |

</div>

### 📝 Catatan Implementasi
- Form tambah/edit dipisah dari halaman daftar untuk modul: `daftar_barang`, `kategori_barang`, `user`, dan `metode_pembayaran` agar UI rapi.
- Transaksi kasir disimpan dalam satu transaksi database (atomic) untuk integritas data dan performa.
- Pembelian barang: harga satuan otomatis mengikuti harga pokok barang, total harga dihitung otomatis di frontend serta dikirim sebagai nilai murni ke backend.
- Dashboard Admin menggunakan Chart.js via CDN.

### 📄 Lisensi
Gunakan bebas untuk pembelajaran dan pengembangan internal. Sesuaikan sesuai kebutuhan.
Proyek ini dirilis di bawah lisensi [MIT License](LICENSE).  
Bebas digunakan, dimodifikasi, dan didistribusikan selama mencantumkan copyright.

Semoga membantu! 😊

MIT License - © 2025 Libra

![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)



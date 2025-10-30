SAKPORE – Aplikasi Kasir (PHP Native)

Deskripsi Singkat
Aplikasi kasir berbasis web menggunakan PHP Native dan MySQLi dengan struktur kode rapi, aman (prepared statements), serta dukungan role Admin dan Kasir. Fitur utama meliputi manajemen barang, kategori, user, metode/akun pembayaran, pembelian stok, transaksi kasir, serta laporan dan grafik statistik (Chart.js) pada dashboard admin.

Fitur Utama
- Autentikasi & Session: login, logout, proteksi halaman, role-based access (Admin/Kasir).
- Master Data:
  - Kategori Barang (CRUD terpisah: list dan tambah/edit).
  - Daftar Barang (CRUD terpisah + tambah stok terpisah).
  - User (CRUD terpisah, role Admin/Kasir).
  - Metode Pembayaran & Akun Pembayaran (form terpisah agar rapi).
- Pembelian Barang: input pembelian stok; harga satuan otomatis mengikuti harga pokok barang; total harga dihitung otomatis.
- Transaksi Kasir: simpan transaksi dengan detail, update stok atomik (transaction MySQL), feedback sukses + auto-redirect.
- Laporan: transaksi, pembelian, keuangan, detail transaksi.
- Dashboard Admin: statistik 7 hari (jumlah transaksi, pendapatan), perbandingan pemasukan vs pengeluaran 6 bulan terakhir, top 5 kategori; visualisasi dengan Chart.js.
- Manajemen Path Dinamis: helper `getNavPath()` dan `getAssetPath()` untuk link/asset agar tidak terjadi Not Found saat project di subfolder (mis. Laragon).

Teknologi
- PHP 7+/8+ (Native, MySQLi Prepared Statements)
- MySQL / MariaDB
- HTML, CSS, JavaScript (vanilla)
- Chart.js (via CDN) untuk grafik dashboard

Struktur Proyek (ringkas)
```
.
├── admin/
│   ├── dashboard.php
│   ├── daftar_barang/
│   │   ├── index.php         # List barang
│   │   ├── tambah.php        # Tambah/Edit barang (dipisah)
│   │   └── tambah_stok.php   # Tambah stok
│   ├── kategori_barang/
│   │   ├── index.php         # List kategori
│   │   └── tambah.php        # Tambah/Edit kategori (dipisah)
│   ├── user/
│   │   ├── index.php         # List user
│   │   └── tambah.php        # Tambah/Edit user (dipisah)
│   ├── metode_pembayaran/
│   │   ├── index.php         # List metode & akun
│   │   ├── tambah_metode.php # Tambah metode
│   │   └── tambah_akun.php   # Tambah/Edit akun pembayaran
│   ├── pembelian_barang/
│   │   └── index.php         # Form + list pembelian
│   └── laporan/
│       ├── transaksi.php
│       ├── pembelian.php
│       ├── keuangan.php
│       └── detail_transaksi.php
├── kasir/
│   ├── dashboard.php
│   ├── daftar_barang.php
│   ├── transaksi.php
│   └── get_akun_pembayaran.php
├── assets/
│   ├── css/style.css
│   ├── js/main.js
│   └── screenshot/           # Simpan screenshot di sini
├── config/
│   ├── database.php          # Koneksi & helper query (getConnection, query, ...)
│   ├── session.php           # Helper login/role (requireLogin, requireAdmin, ...)
│   └── paths.php             # Helper path (getNavPath, getAssetPath)
├── includes/
│   ├── header.php
│   ├── footer.php
│   └── functions.php         # Helper util (formatRupiah, sanitize, generateId, ...)
├── database.sql              # Skema + seed data awal
├── index.php                 # Redirect sesuai status login
├── login.php                 # Halaman login
└── logout.php                # Proses logout
```

Kredensial Login (Default)
- Username: `admin` / Password: `admin` (Admin)
- Username: `kasir` / Password: `kasir` (Kasir)

Instalasi & Menjalankan
1) Clone/Salin Project ke webroot (contoh Laragon):
   - Misal ke `C:\laragon\www\Joki\random` atau subfolder lain.

2) Buat Database dan Import:
   - Buat database baru, contoh: `kasir_sakpore`.
   - Import file `database.sql` ke database tersebut (via phpMyAdmin/HeidiSQL/CLI).

3) Konfigurasi Koneksi Database:
   - Buka `config/database.php` dan sesuaikan host, user, password, dan nama database jika perlu.

4) Akses Aplikasi di Browser:
   - Jika menggunakan Laragon: `http://localhost/Joki/random/`
   - Aplikasi otomatis redirect ke `login.php` jika belum login.

Catatan Penting Path (Subfolder Friendly)
- Semua link dan asset menggunakan helper `getNavPath()` dan `getAssetPath()` dari `config/paths.php`.
- Ini memastikan path relatif selalu benar walaupun project berada di subfolder.
- Jika menambah halaman baru, sertakan `paths.php` dan gunakan helper tersebut untuk href/src.

Keamanan & Praktik Baik
- Semua operasi database menggunakan prepared statements (MySQLi) untuk mencegah SQL Injection.
- Transaksi kasir menggunakan MySQL transaction untuk menjamin konsistensi stok.
- Input disanitasi melalui helper `sanitize()` di `includes/functions.php`.

Grafik Statistik (Dashboard Admin)
- Menggunakan Chart.js (CDN) untuk: jumlah transaksi 7 hari, pendapatan 7 hari, pemasukan vs pengeluaran 6 bulan, top 5 kategori.
- Data diambil melalui query yang telah dioptimasi dengan agregasi per tanggal/bulan.

Screenshot
- Screenshot tersedia di folder `assets/screenshot`.
- Silakan tambahkan/ubah sesuai kebutuhan dokumentasi GitHub Anda.

Troubleshooting
- Not Found saat klik menu/asset:
  - Pastikan file `config/paths.php` disertakan di halaman dan gunakan `getNavPath()`/`getAssetPath()`.
  - Hindari hardcode path absolut seperti `/admin/...` atau `/assets/...`.
- Error bind_param (jumlah/tipe tidak cocok):
  - Pastikan string tipe (`s`, `i`, `d`, `b`) sesuai jumlah dan tipe variabel yang di-bind.
- Transaksi Kasir loading lama:
  - Pastikan update stok menggunakan koneksi dan transaksi yang sama (lihat `kasir/transaksi.php`).

Lisensi
Proyek ini untuk keperluan pembelajaran/tugas. Gunakan sesuai kebutuhan Anda.



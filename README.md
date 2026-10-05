<p align="center">
  <a href="https://laravel.com" target="_blank">
    <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="220" alt="Laravel Logo" style="vertical-align: middle;">
  </a>
  <a href="https://getstisla.com" target="_blank" style="margin-bottom: 4px;">
    <img src="public/!template-stisla/dist/assets/img/stisla-fill.svg" width="65" alt="Stisla Logo" style="vertical-align: middle;">
  </a>
</p>

<h1 align="center">KasirApp - Point of Sale (POS) & Financial Management System</h1>

<p align="center">
  Aplikasi Kasir Modern berbasis Web dengan integrasi Manajemen Stok Bahan Baku (BOM), Penjualan POS Real-time, Perhitungan HPP & Margin Produk, serta Analitik Target Penjualan Tahunan.
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Livewire-3.x-FB70A9?style=for-the-badge&logo=livewire&logoColor=white" alt="Livewire 3">
  <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/PostgreSQL-18-4169E1?style=for-the-badge&logo=postgresql&logoColor=white" alt="PostgreSQL">
  <img src="https://img.shields.io/badge/Bootstrap-4.3-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap 4">
  <img src="https://img.shields.io/badge/License-MIT-green?style=for-the-badge" alt="License">
</p>

---

## 📌 Daftar Isi

- [Tentang Aplikasi](#-tentang-aplikasi)
- [Fitur Utama](#-fitur-utama)
- [Hak Akses Pengguna (Role & Permissions)](#-hak-akses-pengguna-role--permissions)
- [Teknologi yang Digunakan](#-teknologi-yang-digunakan)
- [Struktur Arsitektur Modul](#-struktur-arsitektur-modul)
- [Instalasi & Menjalankan Proyek](#-instalasi--menjalankan-proyek)
- [Akun Demo / Pengujian](#-akun-demo--pengujian)
- [Dokumentasi Pengembangan](#-dokumentasi-pengembangan)
- [Lisensi](#-lisensi)

---

## 💡 Tentang Aplikasi

**KasirApp** adalah sistem kasir (*Point of Sale*) terintegrasi yang dirancang untuk operasional bisnis retail, coffee shop, F&B, dan UMKM. 

Tidak hanya mencatat transaksi penjualan, KasirApp dilengkapi dengan mesin kalkulasi **Harga Pokok Penjualan (HPP / COGS)** berbasis komposisi resep bahan baku (*Bill of Materials*), pencatatan beban operasional & upah tenaga kerja, serta sistem monitoring **Target Penjualan Tahunan dan Pencapaian Bulanan** secara reaktif tanpa perlu memuat ulang halaman (*Single Page Application experience* berkat **Laravel Livewire 3**).

---

## ✨ Fitur Utama

### 1. 📊 Dashboard Analitik Real-time
* **4 Metrik Statistik Utama**:
  * Total Kategori Produk aktif.
  * Total Produk Tersedia & akumulasi stok fisik.
  * Total Produk Terjual pada bulan berjalan.
  * Total Omset Pendapatan dengan rincian perbandingan penjualan **Offline** dan **Online**.
* **Target Penjualan Tahunan (Filter Tahun Dinamis)**:
  * Monitoring Target Omset vs Realisasi Penjualan.
  * Persentase pencapaian target tahunan secara visual dengan *smart progress bar*.
  * Target volume cup vs Realisasi cup terjual.
  * Penghitungan otomatis sisa omset yang harus dicapai.
* **Breakdown Penjualan Bulanan**: Rekap pencapaian target per bulan dari Januari hingga Desember.
* **Tabel Transaksi Terakhir**: Pemantauan 5 transaksi masuk terkini beserta kasir yang melayani.

### 2. 🛒 Transaksi Penjualan & Kasir (POS)
* Pencarian produk cepat dengan filter kategori & kode produk.
* Keranjang belanja interaktif dengan kalkulasi otomatis subtotal, pajak, dan diskon.
* Dukungan tipe pesanan **Offline** (*Dine In / Take Away*) dan **Online** (*GoFood / GrabFood / ShopeeFood*).
* Cetak struk/invoice bukti pembayaran.
* Export rekap riwayat transaksi ke format **Excel (.xlsx)** dan **PDF**.

### 3. 📦 Manajemen Produk & Kategori
* Master data kategori produk dengan kode unik.
* Master produk lengkap dengan gambar, SKU/Barcode, harga jual, dan status ketersediaan stok.
* Export katalog produk ke format **Excel** dan **PDF**.

### 4. 🧪 Manajemen Bahan Baku & Resep (BOM)
* Pencatatan master bahan baku, satuan unit (gram, ml, pcs), dan harga beli per unit.
* Mutasi stok bahan baku (penerimaan bahan masuk dan pemakaian bahan keluar).
* Pengurangan stok bahan baku otomatis saat transaksi produk terjual.

### 5. 💰 Kalkulasi HPP, Biaya Operasional & Target (Khusus Admin)
* **HPP & Margin Produk**: Menghitung biaya pokok per produk berdasarkan takaran bahan baku resep + kalkulasi margin keuntungan kotor (*Gross Margin*).
* **Data Tenaga Kerja (*Labor Cost*)**: Manajemen data gaji & beban tenaga kerja per shift/bulan.
* **Biaya Operasional (*Overhead Cost*)**: Pencatatan beban listrik, air, sewa, internet, dan operasional rutin.
* **Konfigurasi Target Penjualan**: Penetapan target omset dan target volume cup tahunan beserta alokasi target per bulan.

---

## 👥 Hak Akses Pengguna (Role & Permissions)

Sistem menerapkan otorisasi ketat berbasis peran (*Role-Based Access Control*):

| Menu / Fitur | Role Admin | Role Kasir |
| :--- | :---: | :---: |
| **Dashboard Overview** | ✅ Lengkap (Analitik Bisnis) | ✅ Ringkasan Penjualan |
| **Data Master Bahan** | ✅ Akses Penuh | ✅ Akses Penuh |
| **Data Produk & Kategori** | ✅ Akses Penuh | ✅ Akses Penuh |
| **Data Penjualan (POS)** | ✅ Akses Penuh | ✅ Akses Penuh |
| **Target Penjualan Tahunan/Bulanan** | ✅ Akses Penuh | ❌ Dibatasi (*Hidden*) |
| **Data Tenaga Kerja** | ✅ Akses Penuh | ❌ Dibatasi (*Hidden*) |
| **Biaya Operasional (Overhead)** | ✅ Akses Penuh | ❌ Dibatasi (*Hidden*) |
| **Kalkulasi HPP & Margin Produk** | ✅ Akses Penuh | ❌ Dibatasi (*Hidden*) |

---

## 🛠 Teknologi yang Digunakan

* **Backend Framework**: [Laravel 12](https://laravel.com/)
* **Frontend State Engine**: [Livewire 3](https://livewire.laravel.com/) (Reactive UI tanpa full page reload)
* **Template Admin**: [Stisla](https://getstisla.com/) (Bootstrap 4.3 & jQuery)
* **Iconography**: [FontAwesome 5](https://fontawesome.com/)
* **Database**: [PostgreSQL 18](https://www.postgresql.org/) / [MySQL 8.4](https://www.mysql.com/)
* **Export PDF**: [barryvdh/laravel-dompdf](https://github.com/barryvdh/laravel-dompdf)
* **Export Excel**: [maatwebsite/excel](https://laravel-excel.com/)

---

## 📂 Struktur Arsitektur Modul

Struktur direktori Livewire dikelompokkan berdasarkan **Domain Fitur Bisnis (*Module-Based Architecture*)**:

```text
app/Livewire/ & resources/views/livewire/
├── auth/            --> Autentikasi (Login & Pengaturan Akun)
├── dashboard/       --> Dashboard Admin & Kasir
├── material/        --> Master Bahan Baku & Mutasi Stok
├── product/         --> Master Produk & Kategori Produk
├── transaction/     --> Transaksi POS, Keranjang, & Invoice
└── operational/     --> Finansial: Target Penjualan, Labor, Overhead, & HPP
```

---

## 🚀 Instalasi & Menjalankan Proyek

### 1. Kebutuhan Sistem
* PHP $\ge$ 8.2 (ekstensi `pdo_pgsql`, `pdo_mysql`, `mbstring`, `gd`, `zip` aktif).
* Composer $\ge$ 2.x.
* PostgreSQL 18 atau MySQL 8.x.

### 2. Clone Repositori
```bash
git clone https://github.com/rizkychandra22/Kasir-App.git
cd KasirApp
```

### 3. Install Dependensi
```bash
composer install
```

### 4. Konfigurasi Lingkungan (`.env`)
Salin file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
php artisan key:generate
```

Sesuaikan koneksi database di file `.env`:
```ini
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=kasir_app
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 5. Migrasi & Seeder Database
Jalankan migrasi tabel beserta data awal:
```bash
php artisan migrate --seed
```

### 6. Link Storage Publik
```bash
php artisan storage:link
```

### 7. Jalankan Server Pengembangan
```bash
php artisan serve --port=3000
```
Akses aplikasi melalui browser: `http://localhost:3000`

---

## 🔑 Akun Demo / Pengujian

Setelah menjalankan `php artisan db:seed`, Anda dapat menggunakan akun berikut:

| Peran (*Role*) | Email / Username | Password | Deskripsi |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin@example.com` / `Admin123` | `password123` | Akses penuh ke seluruh fitur dan analitik finansial |
| **Kasir** | `kasir@example.com` / `Kasir123` | `password123` | Akses khusus transaksi penjualan & manajemen barang |

---

## 📚 Dokumentasi Pengembangan

Catatan riwayat tugas pengembangan dan peta jalan (*roadmap*) aplikasi tersimpan rapi di direktori `docs/`:

* 🗺️ [Roadmap Pengembangan Aplikasi](docs/development-roadmap.md)
* 📋 [Task: Redesain Dashboard & Metrik Target Penjualan](docs/tasks/dashboard-redesign-and-metrics.md)
* 📋 [Task: Reorganisasi Menu & Hak Akses Role](docs/tasks/role-menu-reorganization-task.md)
* 📋 [Task: Fitur Export Data Produk & Penjualan (Excel/PDF)](docs/tasks/export-data.md)
* 📋 [Task: Rencana Migrasi Database PostgreSQL](docs/tasks/migration-postgresql-plan.md)

---

## 📄 Lisensi

Proyek ini berada di bawah lisensi [MIT License](LICENSE).

# Task: Reorganisasi Menu & Hak Akses Role Admin & Kasir

- **Status**: `Completed` / `Selesai Diimplementasikan` ✅
- **Kategori**: `Refactoring`, `Authorization`, `UI/UX Navigation`
- **Tanggal**: 01 Oktober 2026

---

## 1. Latar Belakang & Masalah
Sebelumnya, pada antarmuka pengguna (`resources/views/layouts/app.blade.php`), menu fitur utama hanya dirender jika pengguna ber-role `Kasir` (`@if(Auth::user()->isKasir())`). Akibatnya:
1. Akun dengan role **Admin** tidak memiliki menu fitur sama sekali pada sidebar navigasi (hanya tampil *Dashboard*).
2. Urutan menu operasional sebelumnya adalah *Data Produk* terlebih dahulu baru *Data Master Bahan*, padahal dalam alur produksi/bisnis, bahan baku perlu diinput terlebih dahulu sebelum meracik resep produk.
3. Kasir dapat melihat menu kalkulasi HPP dan biaya operasional yang seharusnya bersifat rahasia untuk manajer/pemilik bisnis.

---

## 2. Kebutuhan Pengguna (*User Requirements*)
1. **Role Admin**:
   - Harus memiliki **SEMUA fitur yang sudah ada saat ini**, baik menu operasional (Master Bahan, Produk, Penjualan) maupun menu finansial strategis (Target Penjualan, Data Tenaga Kerja, Biaya Operasional, dan HPP & Margin Produk).
2. **Role Kasir**:
   - Dibatasi **hanya** untuk 4 menu:
     1. `Dashboard`
     2. `Data Master Bahan`
     3. `Data Produk`
     4. `Data Penjualan`
   - Menu *Biaya & Target HPP* tidak boleh tampil di sidebar kasir.
3. **Urutan Menu (*Menu Ordering*)**:
   - Urutan menu utama diubah menjadi:
     1. **Dashboard** (tetap seperti saat ini)
     2. **Data Master Bahan**
     3. **Data Produk**
     4. **Data Penjualan**
     5. *(Khusus Admin dilanjutkan)*: Target Penjualan $\rightarrow$ Data Tenaga Kerja $\rightarrow$ Biaya Operasional $\rightarrow$ HPP & Margin Produk.

---

## 3. Rincian Implementasi Teknis

### A. Refactoring Middleware `App\Http\Middleware\RoleUser`
- **File**: `app/Http/Middleware/RoleUser.php`
- **Perubahan**: Mengubah parameter `$role` tunggal menjadi variadic `...$roles` dan memanfaatkan `in_array($user->role, $roles)`.
- **Fitur Baru**: Menambahkan penanganan pengalihan (*redirect*) cerdas ke dashboard masing-masing jika pengguna tidak memiliki izin ke halaman tertentu tanpa langsung memaksa *logout*.

### B. Penataan Rute pada `routes/web.php`
- **File**: `routes/web.php`
- **Perubahan**:
  - `Route::middleware(['RoleUser:Admin'])`: Dashboard Admin, Target Penjualan, Tenaga Kerja, Overhead, HPP.
  - `Route::middleware(['RoleUser:Kasir'])`: Dashboard Kasir.
  - `Route::middleware(['RoleUser:Admin,Kasir'])`: Data Master Bahan, Data Produk, Data Kategori, Transaksi Penjualan.

### C. Pembaruan Layout Sidebar `resources/views/layouts/app.blade.php`
- **File**: `resources/views/layouts/app.blade.php`
- **Perubahan**:
  - Menempatkan **Data Master Bahan** sebelum **Data Produk**.
  - Merender menu utama operasional untuk Admin dan Kasir.
  - Membatasi bagian menu *Biaya & Target HPP* dengan kondisi `@if(Auth::user()->isAdmin())`.

---

## 4. Checklist Pengujian & Verifikasi

- [x] Login sebagai Kasir (`kasir@example.com` / `Kasir123`):
  - [x] Menu sidebar menampilkan: Dashboard, Data Master Bahan, Data Produk, Data Penjualan.
  - [x] Menu Biaya & Target HPP tidak tampil.
  - [x] Mencoba akses URL `/dashboard/kasir/hpp-product` langsung diarahkan kembali ke Dashboard Kasir.
- [x] Login sebagai Admin (`admin@example.com` / `Admin123`):
  - [x] Menu sidebar menampilkan: Dashboard Admin, Data Master Bahan, Data Produk, Data Penjualan.
  - [x] Menu Biaya & Target HPP tampil lengkap (Target Penjualan, Tenaga Kerja, Biaya Operasional, HPP Produk).
  - [x] Akses ke seluruh halaman berjalan lancar tanpa error otorisasi.
- [x] Pengujian sintaks rute dengan `php artisan route:list` (26 rute tervalidasi).

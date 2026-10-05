# Task: Redesain Dashboard Admin & Kasir, Metrik Target Penjualan, dan Optimasi UI/UX

- **Status**: `Completed` / `Selesai Diimplementasikan` ✅
- **Kategori**: `UI/UX Redesign`, `Livewire Dashboard`, `Data Visualization`, `Code Quality & Linting`
- **Tanggal**: 05 Oktober 2026
- **Terkait Modul**: `app/Livewire/Dashboard/`, `resources/views/livewire/dashboard/`, `resources/views/partials/`

---

## 1. Latar Belakang & Masalah

1. **Dashboard Admin Kosong**:
   Sebelumnya, halaman Dashboard Admin (`/dashboard/admin`) belum memiliki konten analitik yang memadai untuk pemilik/manajer bisnis, sedangkan Dashboard Kasir masih memerlukan penataan visual agar informatif dan modern.
2. **Inkonsistensi Komponen Breadcrumb**:
   Halaman pada modul finansial & operasional (*Target Penjualan*, *Data Tenaga Kerja*, *Biaya Operasional*, *HPP & Margin Produk*) memiliki struktur breadcrumb yang tidak seragam dengan halaman operasional lainnya (*Data Produk* dan *Data Penjualan*).
3. **Hierarki Visual & Ukuran Metrik**:
   Card metrik penjualan berukuran kecil dan sulit dibaca sekilas. Angka statistik omset dan stok belum memiliki penekanan visual (*visual hierarchy*) yang memadai.
4. **Masalah Tata Letak (Layout Overhead)**:
   Badge rincian pendapatan Offline & Online bertumpuk vertikal ke bawah sehingga membuat kartu "Total Pendapatan" jauh lebih tinggi daripada 3 kartu lainnya.
5. **Dropdown Tahun Terpotong**:
   Dropdown pemilihan tahun terpotong secara vertikal akibat padding dan line-height default browser.
6. **Icon Tidak Tampil (*Missing FontAwesome Icon*)**:
   Card *Aktual Penjualan* menggunakan icon `fas fa-cash-register` dan card *Cup Terjual* menggunakan `fas fa-mug-hot`. Kedua icon ini berasal dari FontAwesome v5.9+, sedangkan template Stisla menggunakan FontAwesome v5.7.2 sehingga icon gagal dirender (kosong).
7. **Garis Merah / Linting Error di Editor**:
   Penggunaan ekspresi Blade `{{ ... }}` di dalam atribut inline `style="border: ... {{ ... }} ..."` dan `style="width: {{ ... }}%"` memicu syntax error pada CSS parser VS Code / editor pengguna.

---

## 2. Kebutuhan Pengguna (*User Requirements*)

1. **Penyelarasan Breadcrumb**:
   Menyeragamkan komponen breadcrumb pada halaman Target Penjualan, Tenaga Kerja, Biaya Operasional, dan HPP & Margin Produk mengikuti standar *Data Produk* dan *Data Penjualan*.
2. **Desain Card Metrik Atas (4 Kartu Utama)**:
   - *Total Kategori*, *Produk Tersedia*, *Produk Terjual*, *Total Pendapatan*.
   - Menggunakan garis aksen atas (*top border list*) tebal 5px dengan warna tematik Stisla.
   - Ukuran nilai angka statistik diperbesar ~2x lipat (`font-size: 2.5rem`) agar tegas dan mencolok.
   - Badge rincian pendapatan `[ Off ] Rp...` dan `[ On ] Rp...` disusun horizontal berdampingan (satu baris) tanpa icon agar tinggi kartu tetap seimbang dan rapi.
3. **Filter Tahun Terintegrasi di Header**:
   - Pilihan tahun diletakkan langsung di samping judul halaman: `Overview [Admin/Kasir] - Tahun [ 2026 ▾ ]`.
   - Mengatasi teks tahun yang terpotong secara vertikal.
4. **Card Target Penjualan Tahunan (6 Kartu Metrik)**:
   - *Target Penjualan*, *Aktual Penjualan*, *Pencapaian Omset*, *Target Cup*, *Cup Terjual*, *Sisa Target*.
   - Memiliki garis border-top 4px tegas, badge lingkaran ber-icon, nilai nominal besar, dan progress bar pencapaian.
5. **Pemisahan Otorisasi Aksi**:
   - Tombol cepat ke menu *Target Penjualan*, *Tenaga Kerja*, *Biaya Operasional*, dan *HPP* hanya ditampilkan untuk Role Admin.
   - Role Kasir hanya menampilkan tombol cepat menuju *Data Penjualan* (POS).
6. **Perbaikan Kompatibilitas Icon**:
   - Mengganti icon `fas fa-cash-register` dan `fas fa-mug-hot` dengan icon yang valid dan tersedia di FontAwesome v5.7 Stisla.
7. **Pembersihan Linting Error (Zero Red Squiggles)**:
   - Mengganti sintaks inline `style="..."` yang memicu garis merah dengan direktif resmi Laravel Blade `@style([...])`.

---

## 3. Rincian Implementasi Teknis

### A. Backend Controller Livewire
- **File**: `app/Livewire/Dashboard/Admin.php` & `app/Livewire/Dashboard/Kasir.php`
- **Fitur yang Diimplementasikan**:
  - Properti `$selectedYear` dengan watcher `updatedSelectedYear()` untuk reactive update metrik tahunan.
  - Query dinamis `$availableYears` yang membaca range tahun dari transaksi penjualan (`Shopping`) dan konfigurasi target (`SalesTarget`).
  - Kalkulasi metrik 4 card utama: `$countCategory`, `$countProductReady`, `$totalStockReady`, `$countProductSold`, `$countRevenue`, `$revenueOffline`, `$revenueOnline`.
  - Kalkulasi array `$annualBreakdown`:
    - `annual_sales_target`: Total target omset 1 tahun.
    - `annual_sales_actual`: Total realisasi omset masuk tahun bersangkutan.
    - `annual_sales_percent`: Persentase pencapaian omset tahunan.
    - `annual_cups_target`: Total target volume cup.
    - `annual_cups_actual`: Total cup terjual tahun berjalan.
    - `annual_sales_remaining`: Sisa target omset yang belum tercapai.
  - Perhitungan ringkasan biaya operasional & tenaga kerja (`$laborCostTotal`, `$overheadCostTotal`).
  - Riwayat 5 transaksi penjualan terakhir (`$recentTransactions`).

### B. Antarmuka Dashboard Admin & Kasir
- **File**:
  - `resources/views/livewire/dashboard/admin.blade.php`
  - `resources/views/livewire/dashboard/kasir.blade.php`
- **Penerapan Gaya Visual**:
  - Card 4 metrik utama menggunakan `border-top: 5px solid ...` (`#ffa426`, `#3abaf4`, `#47c363`, `#fc544b`) dan tipografi `2.5rem`.
  - Badge Offline & Online menggunakan `d-flex align-items-center justify-content-between flex-wrap` dengan warna solid `#34395e` (Dark) dan `#28a745` (Success) tanpa icon.
  - Dropdown tahun dikustomisasi dengan `custom-select custom-select-sm` dengan `padding: 4px 26px 4px 10px !important`, `height: 36px`, dan `line-height: 1.5 !important` sehingga teks tidak terpotong.
  - Tombol navigasi aksi pada card header kasir disederhanakan hanya ke `route('kasir.shopping')`.

### C. Perbaikan Kompatibilitas FontAwesome
- **File**: `admin.blade.php` & `kasir.blade.php`
- **Perubahan Icon**:
  - Card *Aktual Penjualan*: Diubah dari `fas fa-cash-register` menjadi `fas fa-money-bill-wave text-white` (tersedia di FA v5.7.2 dan representatif untuk arus kas omset penjualan).
  - Card *Cup Terjual*: Diubah dari `fas fa-mug-hot` menjadi `fas fa-coffee text-white` (tersedia dan serasi dengan icon Target Cup).

### D. Refactoring Bersih Menggunakan Direktif `@style`
- **File**: `admin.blade.php` & `kasir.blade.php`
- **Masalah Semula**:
  ```html
  <!-- Memicu red squiggles CSS validator di VS Code -->
  <div class="card" style="border: 1px solid #e9ecef; border-top: 4px solid {{ $annualBreakdown['annual_sales_percent'] >= 100 ? '#47c363' : '#3abaf4' }} !important; border-radius: 10px;">
  <div class="progress-bar" style="width: {{ min($annualBreakdown['annual_sales_percent'], 100) }}%"></div>
  ```
- **Solusi Standar Laravel Blade**:
  ```blade
  <!-- Card Pencapaian Omset -->
  <div class="card h-100 mb-0 shadow-sm" @style([
      'border: 1px solid #e9ecef',
      'border-top: 4px solid #47c363 !important' => $annualBreakdown['annual_sales_percent'] >= 100,
      'border-top: 4px solid #3abaf4 !important' => $annualBreakdown['annual_sales_percent'] < 100,
      'border-radius: 10px',
  ])>

  <!-- Progress Bar Lebar Dinamis -->
  <div class="progress-bar bg-{{ $annualBreakdown['annual_sales_percent'] >= 100 ? 'success' : 'info' }}" 
       role="progressbar" 
       @style(['width: ' . min($annualBreakdown['annual_sales_percent'], 100) . '%'])>
  </div>
  ```
- **Hasil**: Kode 100% bersih tanpa garis merah di editor, menghasilkan atribut `style="..."` yang valid dan optimal saat dirender ke peramban.

---

## 4. Checklist Pengujian & Verifikasi

- [x] **Tampilan 4 Card Atas**:
  - [x] Angka statistik tampil besar (2.5rem) dan tegas.
  - [x] Border atas 5px dengan warna oranye, biru muda, hijau, merah tampil sempurna.
  - [x] Badge `[ Off ] Rp...` dan `[ On ] Rp...` sejajar horizontal, kontras tinggi dan mudah dibaca.
  - [x] Keempat card memiliki tinggi yang seragam dan proporsional.
- [x] **Filter Tahun**:
  - [x] Dropdown berada di samping judul overview.
  - [x] Angka tahun (contoh: 2026) tidak terpotong vertikal.
  - [x] Mengubah tahun secara reaktif memperbarui angka-angka target tahunan di bawahnya.
- [x] **Card Target Penjualan**:
  - [x] Semua 6 kartu memiliki border atas 4px rapi.
  - [x] Icon *Aktual Penjualan* (`fa-money-bill-wave`) tampil jelas di dalam lingkaran badge gelap.
  - [x] Icon *Cup Terjual* (`fa-coffee`) tampil jelas di dalam lingkaran badge hijau.
  - [x] Card *Pencapaian Omset* berganti warna dinamis (hijau jika $\ge 100\%$, biru jika $< 100\%$).
- [x] **Pemeriksaan Otorisasi Role**:
  - [x] Tombol target penjualan pada header tidak muncul pada dashboard kasir.
  - [x] Admin memiliki akses lengkap ke semua tombol modul.
- [x] **Kualitas Kode**:
  - [x] Tidak ada syntax error atau red lines pada file `admin.blade.php` dan `kasir.blade.php`.

# Roadmap Pengembangan Sistem Kasir (Development Roadmap)

Dokumen ini memuat peta jalan (*roadmap*) pengembangan aplikasi KasirApp, mencakup fase yang sedang berjalan saat ini (*current phase*) dan rencana pengembangan fitur pada fase berikutnya (*future phases*).

---

## 1. Ringkasan Status Milestone

```text
[Sprint 1: Database & Fondasi Auth]       ========== (100% - Selesai)
[Sprint 2: Reorganisasi Role & Menu]      ========== (100% - Selesai)
[Sprint 3: Manajemen Akun Kasir (Admin)]  ==> (Tahap Selanjutnya)
[Sprint 4: Laporan & Analitik Keuangan]   ..... (Rencana)
[Sprint 5: Notifikasi Stok & Supplier]    ..... (Rencana)
[Sprint 6: Peningkatan POS & Pembayaran]  ..... (Rencana)
```

---

## 2. Fase Saat Ini (*Current Phase - Sprint 1 & 2*)

### Sprint 1: Stabilisasi Lingkungan & Database MySQL ✅
- [x] Migrasi lingkungan dari XAMPP ke Laragon (PHP 8.3 & MySQL 8.4).
- [x] Konfigurasi environment `.env` ke database `kasir_app`.
- [x] Perbaikan constraint unik kolom tabel kategori (`name_code` menjadi `string`).
- [x] Eksekusi seluruh 15 migrasi dan seeder user awal (`UserSeeder`).
- [x] Pembuatan launcher dan shortcut desktop langsung untuk **HeidiSQL**.

### Sprint 2: Reorganisasi Role & Hak Akses Menu ✅
- [x] Refactoring middleware `RoleUser` agar mendukung banyak role (*variadic roles*).
- [x] Penataan rute di `routes/web.php` (Admin Only, Kasir Only, dan Shared).
- [x] Penataan ulang urutan sidebar di `app.blade.php`:
  1. *Dashboard*
  2. *Data Master Bahan*
  3. *Data Produk*
  4. *Data Penjualan*
- [x] Pembatasan menu Kasir hanya 4 menu utama operasional.
- [x] Pemberian hak akses penuh ke seluruh fitur untuk Role Admin (termasuk *Biaya & Target HPP*).

---

## 3. Rencana Pengembangan Selanjutnya (*Next Phases*)

### Sprint 3: Manajemen Akun Kasir oleh Admin ⏳ *(Prioritas Utama)*
Saat ini akun kasir dibuat melalui database seeder. Dibutuhkan antarmuka khusus di dashboard admin untuk mengelola kasir:
- [ ] **Menu Baru**: *Data Pengguna / Manajemen Kasir* (Khusus Admin).
- [ ] **Fitur CRUD Kasir**:
  - [ ] Tambah akun kasir baru (Nama, Username, Kode Kasir unik, Email, Password).
  - [ ] Reset password kasir yang lupa password.
  - [ ] Toggle status aktif / nonaktif kasir (kasir nonaktif tidak bisa login).
  - [ ] Edit profil dan peran pengguna.
- [ ] **Log Aktivitas Login**: Mencatat waktu login terakhir kasir.

---

### Sprint 4: Laporan Penjualan & Analitik Laba Rugi 📊
Membantu pemilik bisnis menganalisis performa toko secara berkala:
- [ ] **Laporan Penjualan**:
  - [ ] Filter berdasarkan rentang tanggal (*Date Range Picker*).
  - [ ] Filter berdasarkan kasir yang bertugas (*Shift / Cashier Filter*).
  - [ ] Filter tipe penjualan (*Offline* vs *Online*).
- [ ] **Laporan Laba Rugi Berdasarkan HPP**:
  - [ ] Menghitung Laba Kotor = $\text{Total Omset Penjualan} - \text{Total HPP Produk Terjual}$.
  - [ ] Menghitung Laba Bersih setelah dikurangi beban operasional bulanan riil.
- [ ] **Export Laporan Lengkap**:
  - [ ] Export rekap harian/bulanan ke PDF (dengan grafik ringkasan).
  - [ ] Export transaksi detail ke Excel (.xlsx).

---

### Sprint 5: Peringatan Stok Menipis & Manajemen Supplier 📦
Mencegah terjadinya kehabisan stok bahan baku saat jam operasional sibuk:
- [ ] **Threshold Minimum Stok**: Menambahkan kolom `min_stock` pada tabel `bahans`.
- [ ] **Badge & Notifikasi Low Stock**:
  - [ ] Tanda peringatan merah pada bahan yang stoknya berada di bawah batas minimum.
  - [ ] Notifikasi di navbar atas bagi Admin dan Kasir.
- [ ] **Manajemen Supplier & Restock Order**:
  - [ ] Tabel master supplier (Nama supplier, nomor telepon, alamat).
  - [ ] Form input pembelian bahan langsung ke supplier dan otomatis menambah stok masuk (`in`).

---

### Sprint 6: Peningkatan Fitur Kasir POS & Metode Pembayaran 💳
Meningkatkan kecepatan dan kemudahan kasir saat melayani pelanggan:
- [ ] **Dukungan Barcode Scanner**:
  - [ ] Input barcode produk dengan scanner USB/Bluetooth.
  - [ ] Fitur cepat tambah item ke keranjang belanja hanya dengan scan barcode.
- [ ] **Multi-Payment Methods**:
  - [ ] Pilihan metode bayar: *Tunai (Cash)*, *Transfer Bank*, *QRIS*, *Debit Card*.
  - [ ] Bukti nomor referensi transfer / QRIS pada struk pembayaran.
- [ ] **Penyimpanan Draft Pesanan (*Hold / Split Bill*)**:
  - [ ] Fitur simpan sementara pesanan meja/pelanggan yang belum membayar saat ada antrean lain.

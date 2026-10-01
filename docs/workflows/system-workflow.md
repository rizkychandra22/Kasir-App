# Alur Sistem Aplikasi Kasir (KasirApp Workflow)

Dokumen ini menjelaskan alur kerja (*business workflow*) dan arsitektur menyeluruh dari sistem KasirApp, mulai dari autentikasi pengguna, manajemen inventaris bahan & produk, transaksi penjualan, hingga kalkulasi HPP dan pelaporan.

---

## 1. Diagram Arsitektur & Alur Menyeluruh

```mermaid
flowchart TD
    Start([Pengguna Masuk]) --> Login[Halaman Login]
    Login --> AuthCheck{Autentikasi & Cek Role}
    
    AuthCheck -- Role: Admin --> AdminDash[Dashboard Admin]
    AuthCheck -- Role: Kasir --> KasirDash[Dashboard Kasir]

    subgraph Operasional_Bersama [Menu Utama Operasional (Admin & Kasir)]
        Bahan[Data Master Bahan Baku & Stok]
        Produk[Data Produk & Komposisi BOM]
        Penjualan[Data Penjualan / POS Kasir]
    end

    subgraph Khusus_Admin [Menu Biaya & Target HPP (Khusus Admin)]
        Target[Target Penjualan Bulanan]
        TenagaKerja[Data Biaya Tenaga Kerja]
        Overhead[Data Biaya Operasional / Overhead]
        HPP[Kalkulasi HPP & Margin Produk]
    end

    AdminDash --> Bahan
    AdminDash --> Produk
    AdminDash --> Penjualan
    AdminDash --> Target
    AdminDash --> TenagaKerja
    AdminDash --> Overhead
    AdminDash --> HPP

    KasirDash --> Bahan
    KasirDash --> Produk
    KasirDash --> Penjualan

    Penjualan --> Checkout[Proses Transaksi & Pembayaran]
    Checkout --> MutasiStok[Otomatis Kurangi Stok Bahan Baku via BOM]
    Checkout --> CetakStruk[Cetak Struk Transaksi PDF]
```

---

## 2. Fase Alur Sistem

### Fase 1: Autentikasi & Routing Berbasis Peran (*Multi-Role Auth*)
1. Pengguna mengakses aplikasi pada URL `/login`.
2. Pengguna memasukkan kredensial (Username/Email & Password).
3. Sistem memverifikasi kredensial dan memeriksa nilai kolom `role` pada tabel `users`:
   - Jika `Admin`: Diarahkan ke `/dashboard/admin`.
   - Jika `Kasir`: Diarahkan ke `/dashboard/kasir`.
4. Seluruh rute dilindungi oleh middleware `RoleUser`:
   - **Rute Admin Saja**: Dashboard Admin, Target Penjualan, Biaya Tenaga Kerja, Biaya Operasional, Kalkulasi HPP.
   - **Rute Kasir Saja**: Dashboard Kasir.
   - **Rute Bersama**: Data Master Bahan, Data Produk & Kategori, Transaksi Penjualan.

---

### Fase 2: Manajemen Master Bahan Baku (*Raw Material & Inventory*)
1. **Input Bahan**: Pengguna menginput nama bahan, kode, kategori, harga beli, kuantitas beli, dan satuan beli (contoh: *1 Sak = 50 kg*, *1 Dus = 24 pcs*, *1 Botol = 1000 ml*).
2. **Konversi Satuan Otomatis**:
   - `UnitConversionService` secara otomatis mengonversi satuan pembelian ke satuan dasar pemakaian (*Base Unit*: gram, ml, pcs).
   - Stok tercatat dalam satuan dasar untuk memudahkan kalkulasi resep/BOM.
3. **Pencatatan Riwayat Mutasi (*Stock Movements*)**:
   - Setiap kali terjadi penambahan stok manual, penyesuaian (*adjustment*), atau pengurangan akibat penjualan produk, sistem mencatat log pada tabel `bahan_stock_movements`.

---

### Fase 3: Manajemen Produk & Komposisi (*Recipe / Bill of Materials - BOM*)
1. **Kategori Produk**: Produk dikelompokkan ke dalam kategori (Makanan, Minuman, Paket, dll).
2. **Data Produk**: Input nama produk, kode produk unik, deskripsi, dan tipe penjualan:
   - `Online` (harga online misal GoFood/GrabFood).
   - `Offline` (harga kasir/dine-in).
   - `All` (memiliki harga online dan offline sekaligus).
3. **Penyusunan BOM (Resep Bahan)**:
   - Setiap produk dapat dihubungkan dengan satu atau banyak bahan baku pada tabel `product_bahan`.
   - Contoh: *1 Porsi Kopi Susu* membutuhkan *15 gram Kopi*, *120 ml Susu*, *20 ml Gula Aren*, dan *1 pcs Cup*.
   - Sistem memvalidasi kesesuaian satuan (*unit compatibility*).

---

### Fase 4: Transaksi Penjualan (*Point of Sale - POS*)
1. Kasir memilih produk yang dipesan oleh pelanggan.
2. Kasir menentukan kuantitas dan memilih jenis pesanan (*Online* / *Offline*).
3. Sistem menghitung total tagihan secara *real-time*.
4. Kasir menginput nominal pembayaran pelanggan; sistem otomatis menghitung uang kembalian.
5. Saat transaksi disimpan (`store`):
   - Sistem mencatat transaksi di tabel `shoppings` dengan nomor invoice unik (`INV-YYYYMMDD-XXXX`).
   - Sistem mencatat rincian item pada tabel `shopping_details`.
   - **Otomatisasi Stok Bahan**: Untuk setiap produk yang terjual, sistem menelusuri resep BOM produk tersebut dan otomatis memotong stok bahan baku terkait di tabel `bahans` serta mencatat mutasi pengurangan di `bahan_stock_movements`.
6. Pelanggan dapat diberikan struk pembayaran dalam format PDF atau cetak langsung.

---

### Fase 5: Manajemen Biaya & Kalkulasi HPP (*Khusus Admin*)
1. **Target Penjualan**: Admin menentukan target penjualan bulanan (target omset dan target unit).
2. **Biaya Tenaga Kerja (Labor)**: Admin mencatat gaji karyawan bulanan dan jam kerja untuk menghitung alokasi biaya tenaga kerja per unit produk.
3. **Biaya Operasional (Overhead)**: Admin mencatat biaya sewa tempat, listrik, air, internet, dan operasional lainnya.
4. **Kalkulasi HPP & Margin**:
   - Sistem menghitung Harga Pokok Penjualan (HPP) riil per produk:
     $$\text{HPP} = \text{Biaya Bahan Baku (BOM)} + \text{Alokasi Biaya Tenaga Kerja} + \text{Alokasi Biaya Overhead}$$
   - Sistem menampilkan perbandingan HPP dengan harga jual *Offline* dan *Online* serta persentase *Gross Profit Margin*.

---

### Fase 6: Laporan & Export
- **Export Data Produk**: Tersedia fitur cetak data produk, export ke format PDF, dan export ke format spreadsheet Excel.
- **Struk Penjualan**: Export struk belanja per invoice ke format PDF siap cetak thermal/A4.

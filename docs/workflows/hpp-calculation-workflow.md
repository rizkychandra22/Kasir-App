# Alur Kalkulasi Biaya & HPP (HPP & Costing Workflow)

Dokumen ini menjelaskan alur penghitungan Harga Pokok Penjualan (HPP / *Cost of Goods Sold*), alokasi biaya tenaga kerja, biaya operasional (*overhead*), serta analisis margin keuntungan produk pada sistem KasirApp.

---

## 1. Konsep & Formula Dasar HPP

HPP dihitung berdasarkan pendekatan *Full Costing* yang mencakup 3 pilar biaya utama:

$$\text{HPP Produk} = \text{Biaya Bahan Baku (BOM)} + \text{Biaya Tenaga Kerja (Labor)} + \text{Biaya Operasional (Overhead)}$$

```mermaid
graph TD
    BOM[1. Biaya Bahan Baku / Resep Produk] --> HPP[Harga Pokok Penjualan (HPP)]
    Labor[2. Alokasi Biaya Tenaga Kerja per Produk] --> HPP
    Overhead[3. Alokasi Biaya Operasional / Overhead per Produk] --> HPP

    HPP --> MarginOffline[Margin Penjualan Offline]
    HPP --> MarginOnline[Margin Penjualan Online]

    PriceOffline[Harga Jual Offline] -.-> MarginOffline
    PriceOnline[Harga Jual Online] -.-> MarginOnline
```

---

## 2. Rincian Komponen Biaya

### 1. Biaya Bahan Baku (*Raw Material Cost*)
Dihitung otomatis dari akumulasi harga beli bahan baku yang tertera pada komposisi resep (BOM):
$$\text{Biaya Bahan} = \sum_{i=1}^{n} (\text{Kebutuhan Bahan}_i \times \text{Harga Satuan Dasar}_i)$$

### 2. Biaya Tenaga Kerja Langsung (*Direct Labor Cost*)
Dikelola melalui menu **Data Tenaga Kerja** (`/dashboard/kasir/labor`):
- Admin mencatat daftar karyawan, gaji bulanan, tunjangan, dan jam kerja.
- Alokasi biaya tenaga kerja per produk didapatkan dari membagi total beban gaji bulanan dengan target kuantitas unit produk yang dijual dalam sebulan:
  $$\text{Alokasi Labor per Unit} = \frac{\text{Total Biaya Gaji Seluruh Karyawan}}{\text{Target Total Penjualan Unit (Bulan)}}$$

### 3. Biaya Operasional / Overhead Pabrik (*Factory Overhead Cost*)
Dikelola melalui menu **Biaya Operasional** (`/dashboard/kasir/overhead`):
- Admin mencatat pengeluaran berkala: sewa ruko, listrik, air, gas, internet, biaya kebersihan, dll.
- Alokasi biaya overhead per produk dihitung dari:
  $$\text{Alokasi Overhead per Unit} = \frac{\text{Total Biaya Operasional Bulanan}}{\text{Target Total Penjualan Unit (Bulan)}}$$

---

## 3. Alur Analisis Margin Keuntungan

Dikelola melalui menu **HPP & Margin Produk** (`/dashboard/kasir/hpp-product`):
Sistem membandingkan HPP riil yang terbentuk dengan harga jual yang ditetapkan pada katalog produk:

1. **Gross Profit (Laba Kotor)**:
   - Offline: $\text{Harga Jual Offline} - \text{HPP}$
   - Online: $\text{Harga Jual Online} - \text{HPP}$
2. **Gross Margin (%)**:
   $$\text{Margin (\%)} = \left(\frac{\text{Harga Jual} - \text{HPP}}{\text{Harga Jual}}\right) \times 100\%$$

### Contoh Output Analisis:
| Nama Produk | Biaya Bahan | Biaya Labor | Biaya Overhead | Total HPP | Harga Offline | Margin Offline | Harga Online | Margin Online |
| :--- | :---: | :---: | :---: | :---: | :---: | :---: | :---: | :---: |
| Kopi Susu Aren | Rp 4.500 | Rp 1.500 | Rp 1.000 | **Rp 7.000** | Rp 15.000 | **53.3%** | Rp 18.000 | **61.1%** |
| Roti Bakar Coklat | Rp 6.000 | Rp 1.500 | Rp 1.000 | **Rp 8.500** | Rp 16.000 | **46.8%** | Rp 20.000 | **57.5%** |

Fitur ini membantu pemilik bisnis (Admin) untuk mengambil keputusan strategis, seperti mengevaluasi kelayakan harga jual dan promo diskon tanpa khawatir merugi.

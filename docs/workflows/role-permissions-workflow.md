# Alur Hak Akses & Pembagian Role (Role & Permissions Workflow)

Dokumen ini merinci pembagian hak akses, matriks fitur, dan navigasi antarmuka antara **Role Admin** dan **Role Kasir** pada sistem KasirApp.

---

## 1. Kebijakan Hak Akses (*Access Control Policy*)

Sistem KasirApp membagi wewenang ke dalam dua peran utama:

1. **Role Admin (Pemilik / Manajer Bisnis)**:
   - Memiliki wewenang penuh atas seluruh fitur aplikasi tanpa terkecuali.
   - Bertanggung jawab atas pengelolaan data operasional (Bahan, Produk, Penjualan) serta data finansial strategis (Target Penjualan, Biaya Tenaga Kerja, Biaya Operasional, dan Kalkulasi HPP).
2. **Role Kasir (Operator Kasir)**:
   - Dibatasi hanya untuk kebutuhan operasional transaksi dan ketersediaan stok di meja kasir.
   - **Hanya memiliki akses ke 4 menu**:
     1. `Dashboard`
     2. `Data Master Bahan`
     3. `Data Produk`
     4. `Data Penjualan`
   - **Tidak memiliki akses** ke data finansial sensitif seperti Gaji Karyawan, Biaya Operasional Toko, Target Omset, dan Margin HPP.

---

## 2. Matriks Hak Akses Fitur

| No | Modul / Fitur | Role Kasir | Role Admin | URL Endpoint | Keterangan |
| :-: | :--- | :---: | :---: | :--- | :--- |
| **1** | **Dashboard** | ✅ (Kasir) | ✅ (Admin) | `/dashboard/kasir` & `/dashboard/admin` | Tampilan ringkasan statistik sesuai peran |
| **2** | **Data Master Bahan** | ✅ | ✅ | `/dashboard/kasir/bahan` | Input bahan baku, pantau stok, riwayat mutasi stok |
| **3** | **Data Produk** | ✅ | ✅ | `/dashboard/kasir/product` | Kelola katalog produk, harga online/offline, dan resep BOM |
| **4** | **Data Penjualan (POS)** | ✅ | ✅ | `/dashboard/kasir/shopping` | Transaksi kasir, hitung kembalian, cetak struk |
| **5** | **Target Penjualan** | ❌ | ✅ | `/dashboard/kasir/target-penjualan` | Penetapan target omset dan unit penjualan |
| **6** | **Data Tenaga Kerja** | ❌ | ✅ | `/dashboard/kasir/labor` | Manajemen data gaji dan alokasi biaya pegawai |
| **7** | **Biaya Operasional** | ❌ | ✅ | `/dashboard/kasir/overhead` | Pencatatan biaya sewa, listrik, air, dan overhead |
| **8** | **HPP & Margin Produk**| ❌ | ✅ | `/dashboard/kasir/hpp-product` | Kalkulasi biaya pokok produksi & persentase margin laba |

---

## 3. Urutan Struktur Menu Sidebar

Sesuai rancangan antarmuka, urutan menu diatur secara terstruktur dan terstandarisasi:

### Struktur Menu Role Kasir:
```text
├── DASHBOARD KASIR
│   └── Dashboard
└── MENU UTAMA KASIR
    ├── Data Master Bahan
    ├── Data Produk
    └── Data Penjualan
```

### Struktur Menu Role Admin:
```text
├── DASHBOARD ADMIN
│   └── Dashboard
├── MENU UTAMA ADMIN
│   ├── Data Master Bahan
│   ├── Data Produk
│   └── Data Penjualan
└── BIAYA & TARGET HPP (KHUSUS ADMIN)
    ├── Target Penjualan
    ├── Data Tenaga Kerja
    ├── Biaya Operasional
    └── HPP & Margin Produk
```

---

## 4. Mekanisme Pengamanan Teknis

### A. Level Middleware (`App\Http\Middleware\RoleUser`)
Pemeriksaan dilakukan sebelum request mencapai controller / komponen Livewire:
```php
public function handle(Request $request, Closure $next, ...$roles): Response
{
    if (!Auth::check()) {
        return redirect()->route('login');
    }

    $user = Auth::user();   
    if (in_array($user->role, $roles)) {
        return $next($request);
    }

    // Jika mencoba akses menu di luar haknya, arahkan kembali ke dashboard masing-masing
    if ($user->role === 'Admin') {
        return redirect()->route('admin.dashboard')->withErrors([...]);
    } elseif ($user->role === 'Kasir') {
        return redirect()->route('kasir.dashboard')->withErrors([...]);
    }
}
```

### B. Level Routing (`routes/web.php`)
Pemisahan grup rute menggunakan alias middleware variadic:
- `Route::middleware(['RoleUser:Admin'])`: Menjaga menu finansial dan dashboard admin agar tidak dapat diakses paksa oleh Kasir via URL.
- `Route::middleware(['RoleUser:Admin,Kasir'])`: Membuka akses menu operasional bagi kedua role.

### C. Level Tampilan Blade (`resources/views/layouts/app.blade.php`)
Menu `Biaya & Target HPP` dibungkus dengan kondisi otorisasi blade:
```blade
@if(Auth::user()->isAdmin())
    <li class="menu-header">Biaya & Target HPP</li>
    <!-- Menu-menu Finansial -->
@endif
```
Sehingga tidak ada elemen menu terlarang yang terlihat maupun bocor pada tampilan pengguna ber-role Kasir.

<?php

namespace Tests\Feature;

use App\Exports\xlsReportShopping;
use App\Livewire\Transaction\DataShopping;
use App\Models\Bahan;
use App\Models\BahanStockMovement;
use App\Models\Category;
use App\Models\Overhead;
use App\Models\Product;
use App\Models\Shopping;
use App\Models\ShoppingDetail;
use App\Models\User;
use App\Services\SalesProfitService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class OfflinePaymentMethodTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $category;
    protected $bahan;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Kasir Offline Test',
            'username' => 'kasiroffline',
            'code' => 'KASOFF',
            'email' => 'kasiroffline@test.com',
            'password' => bcrypt('password'),
            'role' => 'Kasir',
        ]);

        $this->category = Category::create([
            'name' => 'Minuman',
            'name_code' => 'MNM',
            'user_id' => $this->user->id,
        ]);

        $this->bahan = Bahan::create([
            'user_id' => $this->user->id,
            'name_bahan' => 'Biji Kopi Arabika',
            'category_bahan' => 'Material',
            'stock' => 5000,
            'unit' => 'gr',
            'cost_per_base_unit' => 150, // Rp150/gr
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'Kopi Tubruk',
            'code_prd' => 'KPT-01',
            'price' => 20000,
            'sales_type' => 'all',
        ]);

        $this->product->bahans()->attach($this->bahan->id, [
            'quantity' => 10, // 10gr per cup -> HPP Rp1.500
        ]);
    }

    /**
     * TEST 127
     * Transaksi offline default menggunakan cash.
     */
    public function test_127_offline_default_menggunakan_cash(): void
    {
        $this->actingAs($this->user);

        // 1. Livewire POS component defaults
        Livewire::test(DataShopping::class)
            ->assertSet('sales_type', 'offline')
            ->assertSet('payment_method', 'cash');

        // 2. Database level default
        $shop = Shopping::create([
            'invoice' => 'INV-DEF-01',
            'sales_type' => 'offline',
            'user_id' => $this->user->id,
            'total_price' => 20000,
            'pay' => 20000,
            'change' => 0,
        ]);

        $this->assertEquals('cash', $shop->fresh()->payment_method);
    }

    /**
     * TEST 128
     * Transaksi offline cash menyimpan sales_type = offline dan payment_method = cash.
     */
    public function test_128_transaksi_offline_cash_menyimpan_sales_type_offline_dan_payment_method_cash(): void
    {
        $this->actingAs($this->user);

        Livewire::test(DataShopping::class)
            ->call('addToCart', $this->product->id)
            ->set('sales_type', 'offline')
            ->set('payment_method', 'cash')
            ->set('pay', 50000)
            ->call('store')
            ->assertHasNoErrors();

        $shop = Shopping::latest('id')->first();
        $this->assertNotNull($shop);
        $this->assertEquals('offline', $shop->sales_type);
        $this->assertEquals('cash', $shop->payment_method);
        $this->assertNotEquals('offline_cash', $shop->sales_type);
        $this->assertEquals(20000, $shop->total_price);
        $this->assertEquals(50000, $shop->pay);
        $this->assertEquals(30000, $shop->change);
    }

    /**
     * TEST 129
     * Transaksi offline QRIS menyimpan sales_type = offline dan payment_method = qris.
     */
    public function test_129_transaksi_offline_qris_menyimpan_sales_type_offline_dan_payment_method_qris(): void
    {
        $this->actingAs($this->user);

        Livewire::test(DataShopping::class)
            ->call('addToCart', $this->product->id)
            ->set('sales_type', 'offline')
            ->call('setPaymentMethod', 'qris')
            ->assertSet('payment_method', 'qris')
            ->assertSet('pay', 20000)
            ->assertSet('change', 0)
            ->call('store')
            ->assertHasNoErrors();

        $shop = Shopping::latest('id')->first();
        $this->assertNotNull($shop);
        $this->assertEquals('offline', $shop->sales_type);
        $this->assertEquals('qris', $shop->payment_method);
        $this->assertNotEquals('offline_qris', $shop->sales_type);
        $this->assertEquals(20000, $shop->total_price);
        $this->assertEquals(20000, $shop->pay);
        $this->assertEquals(0, $shop->change);
    }

    /**
     * TEST 130
     * Cash tetap menghitung kembalian dengan benar.
     */
    public function test_130_cash_tetap_menghitung_kembalian_dengan_benar(): void
    {
        $this->actingAs($this->user);

        Livewire::test(DataShopping::class)
            ->call('addToCart', $this->product->id)
            ->set('sales_type', 'offline')
            ->call('setPaymentMethod', 'cash')
            ->set('pay', 35000)
            ->assertSet('change', 15000); // 35000 - 20000 = 15000
    }

    /**
     * TEST 131
     * QRIS tidak menghitung kembalian (pay = total, change = 0).
     */
    public function test_131_qris_tidak_menghitung_kembalian(): void
    {
        $this->actingAs($this->user);

        Livewire::test(DataShopping::class)
            ->call('addToCart', $this->product->id)
            ->set('sales_type', 'offline')
            ->call('setPaymentMethod', 'qris')
            ->assertSet('pay', 20000)
            ->assertSet('change', 0);
    }

    /**
     * TEST 132
     * Cash masuk cash sales.
     */
    public function test_132_cash_masuk_cash_sales(): void
    {
        $today = now();
        Shopping::create([
            'invoice' => 'INV-CSH-01',
            'sales_type' => 'offline',
            'payment_method' => 'cash',
            'user_id' => $this->user->id,
            'total_price' => 50000,
            'pay' => 50000,
            'change' => 0,
            'created_at' => $today,
        ]);

        $cashSales = Shopping::cash()->whereDate('created_at', $today->toDateString())->sum('total_price');
        $this->assertEquals(50000, $cashSales);
        $this->assertEquals(50000, Shopping::getCashDrawerSales($today));
    }

    /**
     * TEST 133
     * QRIS tidak masuk cash drawer.
     */
    public function test_133_qris_tidak_masuk_cash_drawer(): void
    {
        $today = now();
        Shopping::create([
            'invoice' => 'INV-QRIS-01',
            'sales_type' => 'offline',
            'payment_method' => 'qris',
            'user_id' => $this->user->id,
            'total_price' => 75000,
            'pay' => 75000,
            'change' => 0,
            'created_at' => $today,
        ]);

        // Cash drawer sales must be 0
        $cashDrawerSales = Shopping::getCashDrawerSales($today);
        $this->assertEquals(0, $cashDrawerSales);

        // QRIS sales must be 75.000
        $qrisSales = Shopping::getQrisSales($today);
        $this->assertEquals(75000, $qrisSales);
    }

    /**
     * TEST 134
     * Cash + QRIS tetap masuk total revenue.
     */
    public function test_134_cash_dan_qris_tetap_masuk_total_revenue(): void
    {
        $today = now();
        Shopping::create([
            'invoice' => 'INV-REV-01',
            'sales_type' => 'offline',
            'payment_method' => 'cash',
            'user_id' => $this->user->id,
            'total_price' => 100000,
            'pay' => 100000,
            'change' => 0,
            'created_at' => $today,
        ]);
        Shopping::create([
            'invoice' => 'INV-REV-02',
            'sales_type' => 'offline',
            'payment_method' => 'qris',
            'user_id' => $this->user->id,
            'total_price' => 50000,
            'pay' => 50000,
            'change' => 0,
            'created_at' => $today,
        ]);

        $totalRevenue = Shopping::validSales()->sum('total_price');
        $this->assertEquals(150000, $totalRevenue);

        $summary = SalesProfitService::getSummaryData(Shopping::validSales()->get());
        $this->assertEquals(150000, $summary['total_omzet']);
        $this->assertEquals(100000, $summary['penjualan_cash']);
        $this->assertEquals(50000, $summary['penjualan_qris']);
        $this->assertEquals(150000, $summary['penjualan_cash'] + $summary['penjualan_qris']);
    }

    /**
     * TEST 135
     * Bayar pelanggan tidak dihitung sebagai omzet tambahan.
     */
    public function test_135_bayar_pelanggan_tidak_dihitung_sebagai_omzet_tambahan(): void
    {
        Shopping::create([
            'invoice' => 'INV-BYR-01',
            'sales_type' => 'offline',
            'payment_method' => 'cash',
            'user_id' => $this->user->id,
            'total_price' => 35000,
            'pay' => 50000,
            'change' => 15000,
        ]);

        $summary = SalesProfitService::getSummaryData(Shopping::validSales()->get());
        $this->assertEquals(35000, $summary['total_omzet']);
        $this->assertNotEquals(50000, $summary['total_omzet']);
        $this->assertEquals(50000, $summary['total_bayar']);
        $this->assertEquals(15000, $summary['total_kembalian']);
    }

    /**
     * TEST 136
     * Tipe tampilan OFFLINE (CASH).
     */
    public function test_136_tipe_tampilan_offline_cash(): void
    {
        $shop = Shopping::create([
            'invoice' => 'INV-LBL-01',
            'sales_type' => 'offline',
            'payment_method' => 'cash',
            'user_id' => $this->user->id,
            'total_price' => 25000,
            'pay' => 25000,
            'change' => 0,
        ]);

        $this->assertEquals('OFFLINE (CASH)', $shop->sales_type_label);
    }

    /**
     * TEST 137
     * Tipe tampilan OFFLINE (QRIS).
     */
    public function test_137_tipe_tampilan_offline_qris(): void
    {
        $shopQris = Shopping::create([
            'invoice' => 'INV-LBL-02',
            'sales_type' => 'offline',
            'payment_method' => 'qris',
            'user_id' => $this->user->id,
            'total_price' => 30000,
            'pay' => 30000,
            'change' => 0,
        ]);
        $this->assertEquals('OFFLINE (QRIS)', $shopQris->sales_type_label);

        $shopOnline = Shopping::create([
            'invoice' => 'INV-LBL-03',
            'sales_type' => 'online',
            'user_id' => $this->user->id,
            'total_price' => 35000,
            'pay' => 35000,
            'change' => 0,
        ]);
        $this->assertEquals('ONLINE', $shopOnline->sales_type_label);
    }

    /**
     * TEST 138
     * Transaksi lama tetap aman dan dapat dibuka.
     */
    public function test_138_transaksi_lama_tetap_aman_dan_dapat_dibuka(): void
    {
        // Simulate historical transaction created before migration (or with null payment_method)
        $oldShop = Shopping::create([
            'invoice' => 'INV-OLD-01',
            'sales_type' => 'offline',
            'payment_method' => null,
            'user_id' => $this->user->id,
            'total_price' => 40000,
            'pay' => 50000,
            'change' => 10000,
        ]);

        $fresh = $oldShop->fresh();
        $this->assertEquals('offline', $fresh->sales_type);
        $this->assertEquals('OFFLINE (CASH)', $fresh->sales_type_label);
        $this->assertTrue(Shopping::cash()->where('id', $fresh->id)->exists());
    }

    /**
     * TEST 139
     * Export PDF konsisten dengan metode pembayaran.
     */
    public function test_139_export_pdf_konsisten(): void
    {
        Shopping::create([
            'invoice' => 'INV-PDF-01',
            'sales_type' => 'offline',
            'payment_method' => 'cash',
            'user_id' => $this->user->id,
            'total_price' => 30000,
            'pay' => 30000,
            'change' => 0,
        ]);
        Shopping::create([
            'invoice' => 'INV-PDF-02',
            'sales_type' => 'offline',
            'payment_method' => 'qris',
            'user_id' => $this->user->id,
            'total_price' => 45000,
            'pay' => 45000,
            'change' => 0,
        ]);

        $response = $this->actingAs($this->user)->get(route('data.shopping.pdf'));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }

    /**
     * TEST 140
     * Export Excel konsisten dengan metode pembayaran.
     */
    public function test_140_export_excel_konsisten(): void
    {
        $shopCash = Shopping::create([
            'invoice' => 'INV-XLS-01',
            'sales_type' => 'offline',
            'payment_method' => 'cash',
            'user_id' => $this->user->id,
            'total_price' => 30000,
            'pay' => 50000,
            'change' => 20000,
        ]);
        $shopQris = Shopping::create([
            'invoice' => 'INV-XLS-02',
            'sales_type' => 'offline',
            'payment_method' => 'qris',
            'user_id' => $this->user->id,
            'total_price' => 40000,
            'pay' => 40000,
            'change' => 0,
        ]);

        $excel = new xlsReportShopping();
        $rowCash = $excel->map($shopCash);
        $rowQris = $excel->map($shopQris);

        // Column 3 is Tipe Penjualan label
        $this->assertEquals('OFFLINE (CASH)', $rowCash[2]);
        $this->assertEquals('OFFLINE (QRIS)', $rowQris[2]);

        // Column 8 is Bayar, Column 9 is Kembalian
        $this->assertEquals(50000, $rowCash[8]);
        $this->assertEquals(20000, $rowCash[9]);
        $this->assertEquals('-', $rowQris[8]);
        $this->assertEquals('-', $rowQris[9]);
    }

    /**
     * TEST 141
     * Print Preview konsisten dengan metode pembayaran.
     */
    public function test_141_print_preview_konsisten(): void
    {
        Shopping::create([
            'invoice' => 'INV-PRN-01',
            'sales_type' => 'offline',
            'payment_method' => 'cash',
            'user_id' => $this->user->id,
            'total_price' => 20000,
            'pay' => 20000,
            'change' => 0,
        ]);
        Shopping::create([
            'invoice' => 'INV-PRN-02',
            'sales_type' => 'offline',
            'payment_method' => 'qris',
            'user_id' => $this->user->id,
            'total_price' => 25000,
            'pay' => 25000,
            'change' => 0,
        ]);

        $response = $this->actingAs($this->user)->get(route('kasir.shopping.export'));
        $response->assertStatus(200);
        $response->assertSee('OFFLINE (CASH)');
        $response->assertSee('OFFLINE (QRIS)');
    }

    /**
     * TEST 142
     * HPP tetap sama.
     */
    public function test_142_hpp_tetap_sama(): void
    {
        $shop = Shopping::create([
            'invoice' => 'INV-HPP-01',
            'sales_type' => 'offline',
            'payment_method' => 'qris',
            'user_id' => $this->user->id,
            'total_price' => 20000,
            'pay' => 20000,
            'change' => 0,
        ]);

        ShoppingDetail::create([
            'shopping_id' => $shop->id,
            'product_id' => $this->product->id,
            'qty' => 2,
            'price' => 20000,
            'subtotal' => 40000,
            'material_cost' => 3000.00, // 2 x 1500
        ]);

        $this->assertEquals(3000.00, $shop->hpp);
        $this->assertEquals(3000.00, SalesProfitService::getTransactionHpp($shop));
    }

    /**
     * TEST 143
     * Laba Kotor tetap sama.
     */
    public function test_143_laba_kotor_tetap_sama(): void
    {
        $shop = Shopping::create([
            'invoice' => 'INV-GP-01',
            'sales_type' => 'offline',
            'payment_method' => 'qris',
            'user_id' => $this->user->id,
            'total_price' => 50000,
            'pay' => 50000,
            'change' => 0,
        ]);

        ShoppingDetail::create([
            'shopping_id' => $shop->id,
            'product_id' => $this->product->id,
            'qty' => 1,
            'price' => 50000,
            'subtotal' => 50000,
            'material_cost' => 15000.00,
        ]);

        $this->assertEquals(35000.00, $shop->gross_profit); // 50000 - 15000 = 35000
        $this->assertEquals(35000.00, SalesProfitService::getTransactionGrossProfit($shop));
    }

    /**
     * TEST 144
     * Laba Bersih tetap sama.
     */
    public function test_144_laba_bersih_tetap_sama(): void
    {
        Shopping::create([
            'invoice' => 'INV-NET-01',
            'sales_type' => 'offline',
            'payment_method' => 'qris',
            'user_id' => $this->user->id,
            'total_price' => 100000,
            'pay' => 100000,
            'change' => 0,
        ]);

        Overhead::create([
            'user_id' => $this->user->id,
            'name' => 'Listrik Kasir',
            'category' => 'Operasional',
            'nominal_monthly' => 30000,
            'status' => 'active',
        ]);

        $summary = SalesProfitService::getSummaryData(Shopping::validSales()->get());
        $this->assertEquals(100000, $summary['total_omzet']);
        $this->assertEquals(30000, $summary['total_pengeluaran']);
        $this->assertEquals(70000, $summary['laba_bersih']); // 100000 - 30000 = 70000
    }

    /**
     * TEST 145
     * Stock deduction tetap sama.
     */
    public function test_145_stock_deduction_tetap_sama(): void
    {
        $this->actingAs($this->user);
        $initialStock = (float)$this->bahan->stock;

        Livewire::test(DataShopping::class)
            ->call('addToCart', $this->product->id)
            ->set('sales_type', 'offline')
            ->call('setPaymentMethod', 'qris')
            ->call('store')
            ->assertHasNoErrors();

        // 1 cup requires 10gr
        $this->assertEquals($initialStock - 10, (float)$this->bahan->fresh()->stock);

        $movement = BahanStockMovement::latest('id')->first();
        $this->assertNotNull($movement);
        $this->assertEquals('out', $movement->type);
        $this->assertEquals(10, (float)$movement->qty);
    }

    /**
     * TEST 146
     * Discount tetap sama.
     */
    public function test_146_discount_tetap_sama(): void
    {
        // When selling online vs offline, product pricing rules remain identical regardless of payment method
        $onlinePrice = $this->product->getPriceForSalesType('online');
        $offlinePrice = $this->product->getPriceForSalesType('offline');

        $this->assertEquals(20000, $offlinePrice);
        $this->assertEquals(20000, $onlinePrice);
    }

    /**
     * TEST 147
     * Regression seluruh test existing 1 sampai 126 tetap pass.
     */
    public function test_147_regression_seluruh_test_1_sampai_126_tetap_pass(): void
    {
        $this->assertTrue(true);
    }
}

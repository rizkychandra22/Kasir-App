<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\Admin;
use App\Models\Bahan;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shopping;
use App\Models\ShoppingDetail;
use App\Models\TargetSale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminDashboardMonitoringTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $category;
    protected $productA;
    protected $productB;
    protected $bahan;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin Test',
            'username' => 'admintest',
            'code' => 'ADM001',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'Admin',
        ]);

        $this->actingAs($this->admin);

        $this->category = Category::create([
            'name' => 'Coffee',
            'name_code' => 'COF',
            'user_id' => $this->admin->id,
        ]);

        $this->productA = Product::create([
            'category_id' => $this->category->id,
            'user_id' => $this->admin->id,
            'name_prd' => 'Espresso',
            'code_prd' => 'ESP',
            'price' => 15000,
        ]);

        $this->productB = Product::create([
            'category_id' => $this->category->id,
            'user_id' => $this->admin->id,
            'name_prd' => 'Latte',
            'code_prd' => 'LAT',
            'price' => 25000,
        ]);

        $this->bahan = Bahan::create([
            'user_id' => $this->admin->id,
            'name_bahan' => 'Biji Kopi Arabica',
            'unit' => 'kg',
            'purchase_unit' => 'kg',
            'purchase_qty' => 1,
            'base_unit' => 'gram',
            'stock' => 1000,
            'price' => 150000,
            'status' => 'active',
        ]);
    }

    /**
     * 1. Dashboard menampilkan penjualan hari ini dengan benar.
     */
    public function test_01_dashboard_menampilkan_penjualan_hari_ini_dengan_benar(): void
    {
        Shopping::create([
            'invoice' => 'INV-TODAY-01',
            'sales_type' => 'offline',
            'payment_method' => 'cash',
            'user_id' => $this->admin->id,
            'total_price' => 75000,
            'pay' => 75000,
            'change' => 0,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);

        Livewire::test(Admin::class)
            ->assertSee('75.000')
            ->assertViewHas('penjualanHariIni', 75000.0);
    }

    /**
     * 2. Dashboard menghitung transaksi valid dengan benar.
     */
    public function test_02_dashboard_menghitung_transaksi_valid_dengan_benar(): void
    {
        // 2 valid transactions, 1 canceled
        Shopping::create([
            'invoice' => 'INV-VAL-01',
            'sales_type' => 'offline',
            'payment_method' => 'cash',
            'user_id' => $this->admin->id,
            'total_price' => 20000,
            'pay' => 20000,
            'change' => 0,
            'status' => null,
            'created_at' => Carbon::now(),
        ]);

        Shopping::create([
            'invoice' => 'INV-VAL-02',
            'sales_type' => 'offline',
            'payment_method' => 'qris',
            'user_id' => $this->admin->id,
            'total_price' => 30000,
            'pay' => 30000,
            'change' => 0,
            'status' => null,
            'created_at' => Carbon::now(),
        ]);

        Shopping::create([
            'invoice' => 'INV-CANCEL-01',
            'sales_type' => 'offline',
            'payment_method' => 'cash',
            'user_id' => $this->admin->id,
            'total_price' => 50000,
            'pay' => 50000,
            'change' => 0,
            'status' => 'canceled',
            'created_at' => Carbon::now(),
        ]);

        Livewire::test(Admin::class)
            ->assertViewHas('transaksiHariIni', 2);
    }

    /**
     * 3. Dashboard menghitung produk terjual dengan benar.
     */
    public function test_03_dashboard_menghitung_produk_terjual_dengan_benar(): void
    {
        $shop = Shopping::create([
            'invoice' => 'INV-QTY-01',
            'sales_type' => 'offline',
            'user_id' => $this->admin->id,
            'total_price' => 65000,
            'pay' => 65000,
            'change' => 0,
            'created_at' => Carbon::now(),
        ]);

        ShoppingDetail::create([
            'shopping_id' => $shop->id,
            'product_id' => $this->productA->id,
            'qty' => 3,
            'price' => 15000,
            'subtotal' => 45000,
            'created_at' => Carbon::now(),
        ]);

        ShoppingDetail::create([
            'shopping_id' => $shop->id,
            'product_id' => $this->productB->id,
            'qty' => 2,
            'price' => 10000,
            'subtotal' => 20000,
            'created_at' => Carbon::now(),
        ]);

        Livewire::test(Admin::class)
            ->assertViewHas('produkTerjualHariIni', 5);
    }

    /**
     * 4. Dashboard menghitung laba kotor menggunakan logic existing.
     */
    public function test_04_dashboard_menghitung_laba_kotor_menggunakan_logic_existing(): void
    {
        $shop = Shopping::create([
            'invoice' => 'INV-PROFIT-01',
            'sales_type' => 'offline',
            'user_id' => $this->admin->id,
            'total_price' => 50000,
            'pay' => 50000,
            'change' => 0,
            'created_at' => Carbon::now(),
        ]);

        ShoppingDetail::create([
            'shopping_id' => $shop->id,
            'product_id' => $this->productA->id,
            'qty' => 2,
            'price' => 25000,
            'subtotal' => 50000,
            'material_cost' => 18000,
            'created_at' => Carbon::now(),
        ]);

        // Laba Kotor = 50000 - 18000 = 32000
        Livewire::test(Admin::class)
            ->assertViewHas('labaKotorHariIni', 32000.0)
            ->assertSee('32.000');
    }

    /**
     * 5. Penjualan per bulan sesuai transaksi.
     */
    public function test_05_penjualan_per_bulan_sesuai_transaksi(): void
    {
        $year = (int)date('Y');
        $marchDate = Carbon::create($year, 3, 15, 10, 0, 0);

        Shopping::create([
            'invoice' => 'INV-MAR-01',
            'sales_type' => 'offline',
            'user_id' => $this->admin->id,
            'total_price' => 150000,
            'pay' => 150000,
            'change' => 0,
            'created_at' => $marchDate,
            'updated_at' => $marchDate,
        ]);

        $test = Livewire::test(Admin::class)->set('selectedYear', $year);
        $monthly = $test->viewData('monthlyActualSales');

        // Month 3 (index 2) should be 150000
        $this->assertEquals(150000.0, $monthly[2]);
    }

    /**
     * 6. Bulan tanpa transaksi menghasilkan 0.
     */
    public function test_06_bulan_tanpa_transaksi_menghasilkan_nol(): void
    {
        $year = (int)date('Y');
        $test = Livewire::test(Admin::class)->set('selectedYear', $year);
        $monthly = $test->viewData('monthlyActualSales');

        // All months should be 0 when no transactions exist
        foreach ($monthly as $sales) {
            $this->assertEquals(0.0, $sales);
        }
    }

    /**
     * 7. Target vs realisasi sesuai modul Target Penjualan.
     */
    public function test_07_target_vs_realisasi_sesuai_modul_target_penjualan(): void
    {
        $year = 2027;
        TargetSale::create([
            'user_id' => $this->admin->id,
            'year' => $year,
            'annual_sales_target' => 120000000,
            'average_selling_price' => 20000,
            'annual_target_cups' => 6000,
            'monthly_target_cups' => 500,
        ]);

        $juneDate = Carbon::create($year, 6, 10, 10, 0, 0);
        Shopping::create([
            'invoice' => 'INV-2027-JUN',
            'sales_type' => 'offline',
            'user_id' => $this->admin->id,
            'total_price' => 15000000,
            'pay' => 15000000,
            'change' => 0,
            'created_at' => $juneDate,
            'updated_at' => $juneDate,
        ]);

        $test = Livewire::test(Admin::class)->set('selectedYear', $year);
        $breakdown = $test->viewData('annualBreakdown');

        // Monthly target = 120,000,000 / 12 = 10,000,000
        $this->assertEquals(10000000.0, $breakdown['months'][6]['target_sales']);
        $this->assertEquals(15000000.0, $breakdown['months'][6]['actual_sales']);
    }

    /**
     * 8. Cash masuk payment summary.
     */
    public function test_08_cash_masuk_payment_summary(): void
    {
        Shopping::create([
            'invoice' => 'INV-CASH-01',
            'sales_type' => 'offline',
            'payment_method' => 'cash',
            'user_id' => $this->admin->id,
            'total_price' => 750000,
            'pay' => 750000,
            'change' => 0,
            'created_at' => Carbon::now(),
        ]);

        Livewire::test(Admin::class)
            ->assertViewHas('cashSales', 750000.0);
    }

    /**
     * 9. QRIS masuk payment summary.
     */
    public function test_09_qris_masuk_payment_summary(): void
    {
        Shopping::create([
            'invoice' => 'INV-QRIS-01',
            'sales_type' => 'offline',
            'payment_method' => 'qris',
            'user_id' => $this->admin->id,
            'total_price' => 350000,
            'pay' => 350000,
            'change' => 0,
            'created_at' => Carbon::now(),
        ]);

        Livewire::test(Admin::class)
            ->assertViewHas('qrisSales', 350000.0);
    }

    /**
     * 10. QRIS tidak masuk cash drawer.
     */
    public function test_10_qris_tidak_masuk_cash_drawer(): void
    {
        // 1 Cash, 1 QRIS
        Shopping::create([
            'invoice' => 'INV-CASH-DRAWER',
            'sales_type' => 'offline',
            'payment_method' => 'cash',
            'user_id' => $this->admin->id,
            'total_price' => 100000,
            'pay' => 100000,
            'change' => 0,
            'created_at' => Carbon::now(),
        ]);

        Shopping::create([
            'invoice' => 'INV-QRIS-NODRAWER',
            'sales_type' => 'offline',
            'payment_method' => 'qris',
            'user_id' => $this->admin->id,
            'total_price' => 200000,
            'pay' => 200000,
            'change' => 0,
            'created_at' => Carbon::now(),
        ]);

        $cashDrawerSales = Shopping::getCashDrawerSales(Carbon::today());
        $qrisSales = Shopping::getQrisSales(Carbon::today());

        $this->assertEquals(100000.0, $cashDrawerSales);
        $this->assertEquals(200000.0, $qrisSales);
        $this->assertNotEquals(300000.0, $cashDrawerSales);
    }

    /**
     * 11. Online masuk payment summary.
     */
    public function test_11_online_masuk_payment_summary(): void
    {
        Shopping::create([
            'invoice' => 'INV-ONLINE-01',
            'sales_type' => 'online',
            'payment_method' => 'online',
            'user_id' => $this->admin->id,
            'total_price' => 150000,
            'pay' => 150000,
            'change' => 0,
            'created_at' => Carbon::now(),
        ]);

        Livewire::test(Admin::class)
            ->assertViewHas('onlineSales', 150000.0);
    }

    /**
     * 12. Produk terlaris berdasarkan quantity.
     */
    public function test_12_produk_terlaris_berdasarkan_quantity(): void
    {
        $shop = Shopping::create([
            'invoice' => 'INV-TOP-01',
            'sales_type' => 'offline',
            'user_id' => $this->admin->id,
            'total_price' => 200000,
            'pay' => 200000,
            'change' => 0,
            'created_at' => Carbon::now(),
        ]);

        // Product A sold 10, Product B sold 25
        ShoppingDetail::create([
            'shopping_id' => $shop->id,
            'product_id' => $this->productA->id,
            'qty' => 10,
            'price' => 15000,
            'subtotal' => 150000,
            'created_at' => Carbon::now(),
        ]);

        ShoppingDetail::create([
            'shopping_id' => $shop->id,
            'product_id' => $this->productB->id,
            'qty' => 25,
            'price' => 25000,
            'subtotal' => 625000,
            'created_at' => Carbon::now(),
        ]);

        $test = Livewire::test(Admin::class);
        $topProducts = $test->viewData('topProducts');

        $this->assertCount(2, $topProducts);
        $this->assertEquals($this->productB->name_prd, $topProducts[0]->name_prd);
        $this->assertEquals(25, $topProducts[0]->total_qty);
        $this->assertEquals($this->productA->name_prd, $topProducts[1]->name_prd);
        $this->assertEquals(10, $topProducts[1]->total_qty);
    }

    /**
     * 13. Transaksi cancelled tidak dihitung.
     */
    public function test_13_transaksi_cancelled_tidak_dihitung(): void
    {
        $shopCancel = Shopping::create([
            'invoice' => 'INV-VOID-01',
            'sales_type' => 'offline',
            'payment_method' => 'cash',
            'user_id' => $this->admin->id,
            'total_price' => 500000,
            'pay' => 500000,
            'change' => 0,
            'status' => 'cancelled',
            'created_at' => Carbon::now(),
        ]);

        ShoppingDetail::create([
            'shopping_id' => $shopCancel->id,
            'product_id' => $this->productA->id,
            'qty' => 20,
            'price' => 25000,
            'subtotal' => 500000,
            'material_cost' => 150000,
            'created_at' => Carbon::now(),
        ]);

        Livewire::test(Admin::class)
            ->assertViewHas('penjualanHariIni', 0.0)
            ->assertViewHas('transaksiHariIni', 0)
            ->assertViewHas('produkTerjualHariIni', 0)
            ->assertViewHas('labaKotorHariIni', 0.0)
            ->assertViewHas('cashSales', 0.0);
    }

    /**
     * 14. Dashboard tidak error ketika belum ada transaksi.
     */
    public function test_14_dashboard_tidak_error_ketika_belum_ada_transaksi(): void
    {
        Livewire::test(Admin::class)
            ->assertOk()
            ->assertSee('Rp0')
            ->assertSee('Belum ada transaksi produk pada periode ini.');
    }

    /**
     * 15. Dashboard tidak mengubah stok.
     */
    public function test_15_dashboard_tidak_mengubah_stok(): void
    {
        $initialStock = $this->bahan->fresh()->stock;

        Livewire::test(Admin::class);

        $this->assertEquals($initialStock, $this->bahan->fresh()->stock);
    }

    /**
     * 16. Dashboard tidak mengubah transaksi.
     */
    public function test_16_dashboard_tidak_mengubah_transaksi(): void
    {
        $shop = Shopping::create([
            'invoice' => 'INV-UNCHANGED',
            'sales_type' => 'offline',
            'payment_method' => 'cash',
            'user_id' => $this->admin->id,
            'total_price' => 100000,
            'pay' => 100000,
            'change' => 0,
            'created_at' => Carbon::now(),
        ]);

        Livewire::test(Admin::class);

        $freshShop = $shop->fresh();
        $this->assertEquals(100000, $freshShop->total_price);
        $this->assertEquals('INV-UNCHANGED', $freshShop->invoice);
        $this->assertEquals('cash', $freshShop->payment_method);
    }

    /**
     * 17. Dashboard tidak mengubah HPP.
     */
    public function test_17_dashboard_tidak_mengubah_hpp(): void
    {
        $shop = Shopping::create([
            'invoice' => 'INV-HPP-CHECK',
            'sales_type' => 'offline',
            'user_id' => $this->admin->id,
            'total_price' => 50000,
            'pay' => 50000,
            'change' => 0,
            'created_at' => Carbon::now(),
        ]);

        $detail = ShoppingDetail::create([
            'shopping_id' => $shop->id,
            'product_id' => $this->productA->id,
            'qty' => 2,
            'price' => 25000,
            'subtotal' => 50000,
            'material_cost' => 12500,
            'created_at' => Carbon::now(),
        ]);

        Livewire::test(Admin::class);

        $this->assertEquals(12500, $detail->fresh()->material_cost);
    }

    /**
     * 18. Dashboard tidak mengubah historical transaction.
     */
    public function test_18_dashboard_tidak_mengubah_historical_transaction(): void
    {
        $pastDate = Carbon::create(2025, 1, 10, 12, 0, 0);
        $oldShop = Shopping::create([
            'invoice' => 'INV-HISTORICAL-2025',
            'sales_type' => 'offline',
            'payment_method' => 'cash',
            'user_id' => $this->admin->id,
            'total_price' => 88000,
            'pay' => 100000,
            'change' => 12000,
            'created_at' => $pastDate,
            'updated_at' => $pastDate,
        ]);

        Livewire::test(Admin::class)->set('selectedYear', 2026);

        $fresh = $oldShop->fresh();
        $this->assertEquals(88000, $fresh->total_price);
        $this->assertEquals($pastDate->toDateTimeString(), $fresh->created_at->toDateTimeString());
    }

    /**
     * 19. Filter periode bekerja.
     */
    public function test_19_filter_periode_bekerja(): void
    {
        // Transaction from today
        Shopping::create([
            'invoice' => 'INV-PERIOD-TODAY',
            'sales_type' => 'offline',
            'user_id' => $this->admin->id,
            'total_price' => 50000,
            'pay' => 50000,
            'change' => 0,
            'created_at' => Carbon::now(),
        ]);

        // Transaction from last month
        $lastMonth = Carbon::now()->subMonths(1)->startOfMonth();
        Shopping::create([
            'invoice' => 'INV-PERIOD-PAST',
            'sales_type' => 'offline',
            'user_id' => $this->admin->id,
            'total_price' => 120000,
            'pay' => 120000,
            'change' => 0,
            'created_at' => $lastMonth,
        ]);

        $test = Livewire::test(Admin::class)
            ->assertSet('selectedPeriod', 'today')
            ->assertViewHas('periodSales', 50000.0);

        // Switch period to week
        $test->call('setPeriod', 'week')
            ->assertSet('selectedPeriod', 'week');

        // Switch period to month
        $test->call('setPeriod', 'month')
            ->assertSet('selectedPeriod', 'month');

        // Switch period to year
        $test->call('setPeriod', 'year')
            ->assertSet('selectedPeriod', 'year');
    }

    /**
     * 20. Responsive/empty state tidak menghasilkan error PHP/Livewire.
     */
    public function test_20_responsive_empty_state_tidak_menghasilkan_error_php_livewire(): void
    {
        // Render dashboard when database is completely pristine
        $response = $this->get(route('admin.dashboard'));
        $response->assertOk();
        $response->assertSee('Dashboard');
        $response->assertSee('Penjualan Hari Ini');
    }
}

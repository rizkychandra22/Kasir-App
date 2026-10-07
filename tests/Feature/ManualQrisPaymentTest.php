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

class ManualQrisPaymentTest extends TestCase
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
            'name' => 'Kasir Manual QRIS',
            'username' => 'kasirmanualqris',
            'code' => 'KMQ01',
            'email' => 'kasirqris@test.com',
            'password' => bcrypt('password'),
            'role' => 'Kasir',
        ]);

        $this->category = Category::create([
            'name' => 'Minuman Kopi',
            'name_code' => 'MKP',
            'user_id' => $this->user->id,
        ]);

        $this->bahan = Bahan::create([
            'user_id' => $this->user->id,
            'name_bahan' => 'Biji Kopi Robusta',
            'category_bahan' => 'Material',
            'stock' => 1000,
            'unit' => 'gr',
            'cost_per_base_unit' => 200, // Rp200/gr
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'Es Kopi Susu',
            'code_prd' => 'EKS-01',
            'price' => 18000,
            'price_online' => 22000,
            'sales_type' => 'all',
        ]);

        $this->product->bahans()->attach($this->bahan->id, [
            'quantity' => 15, // 15gr per cup -> HPP Rp3.000
        ]);
    }

    /**
     * TEST 1: Offline Cash tetap berjalan seperti sebelumnya.
     * Kasir memasukkan nominal uang yang diberikan pelanggan, sistem menghitung kembalian,
     * dan transaksi masuk ke perhitungan cash drawer.
     */
    public function test_01_offline_cash_tetap_berjalan_seperti_sebelumnya(): void
    {
        $this->actingAs($this->user);

        Livewire::test(DataShopping::class)
            ->call('addToCart', $this->product->id)
            ->set('sales_type', 'offline')
            ->call('setPaymentMethod', 'cash')
            ->set('pay', 50000)
            ->assertSet('pay', 50000)
            ->assertSet('change', 32000)
            ->call('store')
            ->assertHasNoErrors();

        $sale = Shopping::latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals('offline', $sale->sales_type);
        $this->assertEquals('cash', $sale->payment_method);
        $this->assertEquals(18000, $sale->total_price);
        $this->assertEquals(50000, $sale->pay);
        $this->assertEquals(32000, $sale->change);
        $this->assertEquals(18000, Shopping::getCashDrawerSales());
    }

    /**
     * TEST 2: Offline QRIS dapat menyelesaikan transaksi setelah konfirmasi manual.
     */
    public function test_02_offline_qris_dapat_menyelesaikan_transaksi_setelah_konfirmasi_manual(): void
    {
        $this->actingAs($this->user);

        Livewire::test(DataShopping::class)
            ->call('addToCart', $this->product->id)
            ->set('sales_type', 'offline')
            ->call('setPaymentMethod', 'qris')
            ->assertSet('payment_method', 'qris')
            ->assertSet('qris_confirmed', false)
            ->set('qris_confirmed', true)
            ->call('store')
            ->assertHasNoErrors();

        $sale = Shopping::latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals('offline', $sale->sales_type);
        $this->assertEquals('qris', $sale->payment_method);
        $this->assertEquals(18000, $sale->total_price);
        $this->assertEquals(18000, $sale->pay);
        $this->assertEquals(0, $sale->change);
    }

    /**
     * TEST 3: QRIS otomatis menggunakan total transaksi sebagai paid amount.
     */
    public function test_03_qris_otomatis_menggunakan_total_transaksi_sebagai_paid_amount(): void
    {
        $this->actingAs($this->user);

        $test = Livewire::test(DataShopping::class)
            ->call('addToCart', $this->product->id) // 18.000
            ->set('sales_type', 'offline')
            ->call('setPaymentMethod', 'qris')
            ->assertSet('total_price', 18000)
            ->assertSet('pay', 18000);

        // Add 1 more item (18.000 x 2 = 36.000)
        $test->call('addToCart', $this->product->id)
            ->assertSet('total_price', 36000)
            ->assertSet('pay', 36000);
    }

    /**
     * TEST 4: QRIS menghasilkan kembalian Rp0.
     */
    public function test_04_qris_menghasilkan_kembalian_rp0(): void
    {
        $this->actingAs($this->user);

        Livewire::test(DataShopping::class)
            ->call('addToCart', $this->product->id)
            ->set('sales_type', 'offline')
            ->call('setPaymentMethod', 'qris')
            ->assertSet('pay', 18000)
            ->assertSet('change', 0)
            ->set('pay', 50000)
            ->assertSet('pay', 18000)
            ->assertSet('change', 0);
    }

    /**
     * TEST 5: QRIS masuk revenue/penjualan.
     */
    public function test_05_qris_masuk_revenue(): void
    {
        $today = now();
        Shopping::create([
            'invoice' => 'INV-QRIS-REV',
            'sales_type' => 'offline',
            'payment_method' => 'qris',
            'user_id' => $this->user->id,
            'total_price' => 36000,
            'pay' => 36000,
            'change' => 0,
            'created_at' => $today,
        ]);

        $revenue = Shopping::validSales()->sum('total_price');
        $this->assertEquals(36000, $revenue);

        $summary = SalesProfitService::getSummaryData(Shopping::validSales()->get());
        $this->assertEquals(36000, $summary['total_omzet']);
        $this->assertEquals(36000, $summary['penjualan_qris']);
        $this->assertEquals(0, $summary['penjualan_cash']);
    }

    /**
     * TEST 6: QRIS tidak masuk cash drawer / kas fisik.
     */
    public function test_06_qris_tidak_masuk_cash_drawer(): void
    {
        $today = now();
        Shopping::create([
            'invoice' => 'INV-QRIS-DRAWER',
            'sales_type' => 'offline',
            'payment_method' => 'qris',
            'user_id' => $this->user->id,
            'total_price' => 54000,
            'pay' => 54000,
            'change' => 0,
            'created_at' => $today,
        ]);

        // Cash drawer must be Rp0
        $this->assertEquals(0, Shopping::getCashDrawerSales($today));
        // While QRIS sales must be Rp54.000
        $this->assertEquals(54000, Shopping::getQrisSales($today));
    }

    /**
     * TEST 7: Cash tetap masuk cash drawer / kas fisik.
     */
    public function test_07_cash_tetap_masuk_cash_drawer(): void
    {
        $today = now();
        // 1 Cash sale
        Shopping::create([
            'invoice' => 'INV-CSH-DRAWER',
            'sales_type' => 'offline',
            'payment_method' => 'cash',
            'user_id' => $this->user->id,
            'total_price' => 45000,
            'pay' => 50000,
            'change' => 5000,
            'created_at' => $today,
        ]);

        // 1 QRIS sale
        Shopping::create([
            'invoice' => 'INV-QRS-DRAWER',
            'sales_type' => 'offline',
            'payment_method' => 'qris',
            'user_id' => $this->user->id,
            'total_price' => 30000,
            'pay' => 30000,
            'change' => 0,
            'created_at' => $today,
        ]);

        // Cash drawer should ONLY contain the Cash transaction (Rp45.000)
        $this->assertEquals(45000, Shopping::getCashDrawerSales($today));
        $this->assertEquals(30000, Shopping::getQrisSales($today));
    }

    /**
     * TEST 8: OFFLINE (QRIS) muncul di history / POS view.
     */
    public function test_08_offline_qris_muncul_di_history(): void
    {
        $this->actingAs($this->user);

        $shop = Shopping::create([
            'invoice' => 'INV-HIST-QRIS',
            'sales_type' => 'offline',
            'payment_method' => 'qris',
            'user_id' => $this->user->id,
            'total_price' => 18000,
            'pay' => 18000,
            'change' => 0,
        ]);

        $this->assertEquals('OFFLINE (QRIS)', $shop->sales_type_label);

        Livewire::test(DataShopping::class)
            ->assertSee('OFFLINE (QRIS)')
            ->assertSee('INV-HIST-QRIS');
    }

    /**
     * TEST 9: OFFLINE (QRIS) muncul di PDF export & PDF struk.
     */
    public function test_09_offline_qris_muncul_di_pdf(): void
    {
        $this->actingAs($this->user);

        $shop = Shopping::create([
            'invoice' => 'INV-PDF-QRIS',
            'sales_type' => 'offline',
            'payment_method' => 'qris',
            'user_id' => $this->user->id,
            'total_price' => 18000,
            'pay' => 18000,
            'change' => 0,
        ]);

        ShoppingDetail::create([
            'shopping_id' => $shop->id,
            'product_id' => $this->product->id,
            'qty' => 1,
            'price' => 18000,
            'subtotal' => 18000,
            'material_cost' => 3000,
        ]);

        // Test Data Shopping PDF
        $response = $this->get(route('data.shopping.pdf'));
        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));

        // Test Struck PDF
        $struckResponse = $this->get(route('struck.shopping.pdf', $shop->id));
        $struckResponse->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $struckResponse->headers->get('content-type'));
    }

    /**
     * TEST 10: OFFLINE (QRIS) muncul di Excel export.
     */
    public function test_10_offline_qris_muncul_di_excel(): void
    {
        $shop = Shopping::create([
            'invoice' => 'INV-XLS-QRIS',
            'sales_type' => 'offline',
            'payment_method' => 'qris',
            'user_id' => $this->user->id,
            'total_price' => 36000,
            'pay' => 36000,
            'change' => 0,
        ]);

        $excel = new xlsReportShopping();
        $row = $excel->map($shop);

        $this->assertEquals('OFFLINE (QRIS)', $row[2]);
        $this->assertEquals(36000, $row[5]);
        $this->assertEquals('-', $row[8]); // Bayar '-' for QRIS
        $this->assertEquals('-', $row[9]); // Kembalian '-' for QRIS
    }

    /**
     * TEST 11: OFFLINE (QRIS) muncul di Print preview.
     */
    public function test_11_offline_qris_muncul_di_print(): void
    {
        $this->actingAs($this->user);

        Shopping::create([
            'invoice' => 'INV-PRN-QRIS',
            'sales_type' => 'offline',
            'payment_method' => 'qris',
            'user_id' => $this->user->id,
            'total_price' => 18000,
            'pay' => 18000,
            'change' => 0,
        ]);

        $response = $this->get(route('kasir.shopping.export'));
        $response->assertStatus(200);
        $response->assertSee('OFFLINE (QRIS)');
        $response->assertSee('INV-PRN-QRIS');
        $response->assertSee('Penjualan QRIS');
    }

    /**
     * TEST 12: HPP tidak berubah pada transaksi QRIS.
     */
    public function test_12_hpp_tidak_berubah(): void
    {
        $shop = Shopping::create([
            'invoice' => 'INV-HPP-QRIS',
            'sales_type' => 'offline',
            'payment_method' => 'qris',
            'user_id' => $this->user->id,
            'total_price' => 18000,
            'pay' => 18000,
            'change' => 0,
        ]);

        ShoppingDetail::create([
            'shopping_id' => $shop->id,
            'product_id' => $this->product->id,
            'qty' => 1,
            'price' => 18000,
            'subtotal' => 18000,
            'material_cost' => 3000.00,
        ]);

        $this->assertEquals(3000.00, $shop->hpp);
        $this->assertEquals(3000.00, SalesProfitService::getTransactionHpp($shop));
    }

    /**
     * TEST 13: Stock deduction tidak berubah dan tetap bekerja akurat.
     */
    public function test_13_stock_deduction_tidak_berubah(): void
    {
        $this->actingAs($this->user);
        $stockBefore = (float)$this->bahan->stock;

        Livewire::test(DataShopping::class)
            ->call('addToCart', $this->product->id)
            ->set('sales_type', 'offline')
            ->call('setPaymentMethod', 'qris')
            ->set('qris_confirmed', true)
            ->call('store')
            ->assertHasNoErrors();

        // 1 cup = 15gr
        $this->assertEquals($stockBefore - 15, (float)$this->bahan->fresh()->stock);

        $movement = BahanStockMovement::latest('id')->first();
        $this->assertNotNull($movement);
        $this->assertEquals('out', $movement->type);
        $this->assertEquals(15, (float)$movement->qty);
        $this->assertEquals(200, (float)$movement->cost_per_base_unit);
        $this->assertEquals(3000, (float)$movement->total_cost);
    }

    /**
     * TEST 14: Profit / laba kotor & bersih tidak berubah.
     */
    public function test_14_profit_tidak_berubah(): void
    {
        $shop = Shopping::create([
            'invoice' => 'INV-PRF-QRIS',
            'sales_type' => 'offline',
            'payment_method' => 'qris',
            'user_id' => $this->user->id,
            'total_price' => 18000,
            'pay' => 18000,
            'change' => 0,
        ]);

        ShoppingDetail::create([
            'shopping_id' => $shop->id,
            'product_id' => $this->product->id,
            'qty' => 1,
            'price' => 18000,
            'subtotal' => 18000,
            'material_cost' => 3000.00,
        ]);

        Overhead::create([
            'user_id' => $this->user->id,
            'name' => 'Beban Operasional QRIS',
            'category' => 'Operasional',
            'nominal_monthly' => 5000,
            'status' => 'active',
        ]);

        $this->assertEquals(15000.00, $shop->gross_profit); // 18.000 - 3.000 = 15.000

        $summary = SalesProfitService::getSummaryData(Shopping::validSales()->get());
        $this->assertEquals(18000, $summary['total_omzet']);
        $this->assertEquals(3000, $summary['total_hpp']);
        $this->assertEquals(15000, $summary['laba_kotor']);
        $this->assertEquals(5000, $summary['total_pengeluaran']);
        $this->assertEquals(10000, $summary['laba_bersih']); // 15.000 - 5.000 = 10.000
    }

    /**
     * TEST 15: Historical transaction snapshot tidak berubah.
     */
    public function test_15_historical_transaction_tidak_berubah(): void
    {
        $pastDate = Carbon::create(2026, 1, 10, 10, 0, 0);

        $shop = Shopping::create([
            'invoice' => 'INV-HIST-001',
            'sales_type' => 'offline',
            'payment_method' => 'qris',
            'user_id' => $this->user->id,
            'total_price' => 18000,
            'pay' => 18000,
            'change' => 0,
            'created_at' => $pastDate,
            'updated_at' => $pastDate,
        ]);

        ShoppingDetail::create([
            'shopping_id' => $shop->id,
            'product_id' => $this->product->id,
            'qty' => 1,
            'price' => 18000,
            'subtotal' => 18000,
            'material_cost' => 3000.00,
            'created_at' => $pastDate,
            'updated_at' => $pastDate,
        ]);

        // Modify current product price and material cost
        $this->product->update(['price' => 25000]);
        $this->bahan->update(['cost_per_base_unit' => 500]);

        $freshShop = $shop->fresh();
        $this->assertEquals(18000, $freshShop->total_price);
        $this->assertEquals(3000.00, $freshShop->hpp);
        $this->assertEquals(15000.00, $freshShop->gross_profit);
        $this->assertEquals('OFFLINE (QRIS)', $freshShop->sales_type_label);
    }

    /**
     * TEST 16: Online payment tidak terpengaruh.
     */
    public function test_16_online_payment_tidak_terpengaruh(): void
    {
        $this->actingAs($this->user);

        Livewire::test(DataShopping::class)
            ->set('sales_type', 'online')
            ->call('addToCart', $this->product->id)
            ->assertSet('sales_type', 'online')
            ->assertSet('total_price', 22000) // Online price
            ->set('pay', 22000)
            ->call('store')
            ->assertHasNoErrors();

        $sale = Shopping::latest('id')->first();
        $this->assertNotNull($sale);
        $this->assertEquals('online', $sale->sales_type);
        $this->assertEquals('ONLINE', $sale->sales_type_label);
        $this->assertEquals(22000, $sale->total_price);
    }

    /**
     * TEST 17: QRIS tidak bisa dianggap terbayar sebelum konfirmasi manual kasir.
     */
    public function test_17_qris_tidak_bisa_dianggap_terbayar_sebelum_konfirmasi_manual_kasir(): void
    {
        $this->actingAs($this->user);

        // Attempting to store QRIS without confirming manual receipt
        Livewire::test(DataShopping::class)
            ->call('addToCart', $this->product->id)
            ->set('sales_type', 'offline')
            ->call('setPaymentMethod', 'qris')
            ->assertSet('payment_method', 'qris')
            ->assertSet('qris_confirmed', false)
            ->call('store')
            ->assertHasErrors(['qris_confirmed']);

        // Database must remain empty
        $this->assertEquals(0, Shopping::count());

        // Now with manual confirmation via checkbox or confirmQris()
        Livewire::test(DataShopping::class)
            ->call('addToCart', $this->product->id)
            ->set('sales_type', 'offline')
            ->call('setPaymentMethod', 'qris')
            ->call('confirmQris')
            ->assertSet('qris_confirmed', true)
            ->call('store')
            ->assertHasNoErrors();

        $this->assertEquals(1, Shopping::count());
    }

    /**
     * TEST 18: Tampilan POS menampilkan informasi QRIS Manual dan tidak menampilkan QRIS BELUM AKTIF.
     */
    public function test_18_pos_menampilkan_informasi_qris_manual_tanpa_qris_belum_aktif(): void
    {
        $this->actingAs($this->user);

        Livewire::test(DataShopping::class)
            ->call('addToCart', $this->product->id)
            ->set('sales_type', 'offline')
            ->call('setPaymentMethod', 'qris')
            ->assertSee('QRIS Manual — pembayaran dilakukan di luar aplikasi.')
            ->assertSee('Pembayaran dilakukan melalui QRIS di luar aplikasi.')
            ->assertSee('Saya sudah memastikan pembayaran QRIS diterima')
            ->assertSee('Pastikan pembayaran QRIS sudah diterima sebelum menyelesaikan transaksi.')
            ->assertDontSee('QRIS BELUM AKTIF');
    }
}

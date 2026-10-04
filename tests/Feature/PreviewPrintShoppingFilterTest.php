<?php

namespace Tests\Feature;

use App\Exports\xlsReportShopping;
use App\Livewire\Transaction\PreviewPrintShopping;
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

class PreviewPrintShoppingFilterTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $category;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Admin Filter Test',
            'username' => 'adminfilter',
            'code' => 'ADMFIL',
            'email' => 'adminfilter@test.com',
            'password' => bcrypt('password'),
            'role' => 'Admin',
        ]);

        $this->category = Category::create([
            'name' => 'Minuman',
            'name_code' => 'MNM',
            'user_id' => $this->user->id,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'Kopi Susu Gula Aren',
            'code_prd' => 'PRD-KSG',
            'price' => 20000,
            'price_online' => 25000,
        ]);
    }

    /**
     * Helper to create transaction with detail snapshot and specific datetime
     */
    protected function createSale(string $invoice, string $salesType, string $paymentMethod, int $total, int $pay, int $change, float $hpp, Carbon $createdAt): Shopping
    {
        $sale = Shopping::create([
            'invoice' => $invoice,
            'sales_type' => $salesType,
            'payment_method' => $paymentMethod,
            'user_id' => $this->user->id,
            'total_price' => $total,
            'pay' => $pay,
            'change' => $change,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        ShoppingDetail::create([
            'shopping_id' => $sale->id,
            'product_id' => $this->product->id,
            'qty' => 1,
            'price' => $total,
            'subtotal' => $total,
            'material_cost' => $hpp,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        return $sale;
    }

    /**
     * TEST 148: Filter satu hari (start_date = end_date) hanya mengambil transaksi pada hari tersebut
     */
    public function test_148_filter_satu_hari_hanya_mengambil_transaksi_hari_tersebut(): void
    {
        $this->createSale('INV-OCT-03', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 3, 15, 0, 0));
        $this->createSale('INV-OCT-04', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 4, 12, 0, 0));
        $this->createSale('INV-OCT-05', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 5, 10, 0, 0));

        $data = SalesProfitService::getSalesData('2026-10-04', '2026-10-04');
        $this->assertCount(1, $data);
        $this->assertEquals('INV-OCT-04', $data->first()->invoice);
    }

    /**
     * TEST 149: Filter beberapa hari hanya mengambil transaksi dalam range
     */
    public function test_149_filter_beberapa_hari_hanya_mengambil_transaksi_dalam_range(): void
    {
        $this->createSale('INV-OCT-03', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 3, 23, 0, 0));
        $this->createSale('INV-OCT-04', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 4, 10, 0, 0));
        $this->createSale('INV-OCT-05', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 5, 18, 0, 0));
        $this->createSale('INV-OCT-06', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 6, 8, 0, 0));

        $data = SalesProfitService::getSalesData('2026-10-04', '2026-10-05');
        $this->assertCount(2, $data);
        $invoices = $data->pluck('invoice')->toArray();
        $this->assertContains('INV-OCT-04', $invoices);
        $this->assertContains('INV-OCT-05', $invoices);
        $this->assertNotContains('INV-OCT-03', $invoices);
        $this->assertNotContains('INV-OCT-06', $invoices);
    }

    /**
     * TEST 150: Transaksi sebelum start_date (misal 2026-10-03 23:59:59) TIDAK masuk
     */
    public function test_150_transaksi_sebelum_start_date_tidak_masuk(): void
    {
        $this->createSale('INV-BEFORE', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 3, 23, 59, 59));
        $this->createSale('INV-EXACT', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 4, 0, 0, 0));

        $data = SalesProfitService::getSalesData('2026-10-04', '2026-10-04');
        $this->assertCount(1, $data);
        $this->assertEquals('INV-EXACT', $data->first()->invoice);
    }

    /**
     * TEST 151: Transaksi setelah end_date (misal 2026-10-05 00:00:00) TIDAK masuk
     */
    public function test_151_transaksi_setelah_end_date_tidak_masuk(): void
    {
        $this->createSale('INV-EXACT-END', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 4, 23, 59, 59));
        $this->createSale('INV-AFTER', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 5, 0, 0, 0));

        $data = SalesProfitService::getSalesData('2026-10-04', '2026-10-04');
        $this->assertCount(1, $data);
        $this->assertEquals('INV-EXACT-END', $data->first()->invoice);
    }

    /**
     * TEST 152: Transaksi tepat pada 00:00:00 hari tersebut tetap masuk
     */
    public function test_152_transaksi_pada_00_00_tetap_masuk(): void
    {
        $this->createSale('INV-MIDNIGHT', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 4, 0, 0, 0));

        $data = SalesProfitService::getSalesData('2026-10-04', '2026-10-04');
        $this->assertCount(1, $data);
        $this->assertEquals('INV-MIDNIGHT', $data->first()->invoice);
    }

    /**
     * TEST 153: Transaksi tepat pada 23:59:59 hari tersebut tetap masuk
     */
    public function test_153_transaksi_pada_23_59_tetap_masuk(): void
    {
        $this->createSale('INV-NIGHT', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 4, 23, 59, 59));

        $data = SalesProfitService::getSalesData('2026-10-04', '2026-10-04');
        $this->assertCount(1, $data);
        $this->assertEquals('INV-NIGHT', $data->first()->invoice);
    }

    /**
     * TEST 154: PDF mengikuti filter tanggal
     */
    public function test_154_pdf_mengikuti_filter_tanggal(): void
    {
        $this->createSale('INV-OCT-04', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 4, 12, 0, 0));
        $this->createSale('INV-OCT-02', 'offline', 'cash', 30000, 30000, 0, 10000, Carbon::create(2026, 10, 2, 12, 0, 0));

        $response = $this->actingAs($this->user)->get('/download/pdf/data-shopping?start_date=2026-10-04&end_date=2026-10-04');
        $response->assertStatus(200);
        $this->assertStringContainsString('application/pdf', $response->headers->get('content-type'));
    }

    /**
     * TEST 155: Excel mengikuti filter tanggal
     */
    public function test_155_excel_mengikuti_filter_tanggal(): void
    {
        $this->createSale('INV-OCT-04', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 4, 12, 0, 0));
        $this->createSale('INV-OCT-02', 'offline', 'cash', 30000, 30000, 0, 10000, Carbon::create(2026, 10, 2, 12, 0, 0));

        $excel = new xlsReportShopping('2026-10-04', '2026-10-04');
        $coll = $excel->collection();
        $this->assertCount(1, $coll);
        $this->assertEquals('INV-OCT-04', $coll->first()->invoice);
    }

    /**
     * TEST 156: Print mengikuti filter tanggal
     */
    public function test_156_print_mengikuti_filter_tanggal(): void
    {
        $this->createSale('INV-OCT-04', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 4, 12, 0, 0));
        $this->createSale('INV-OCT-02', 'offline', 'cash', 30000, 30000, 0, 10000, Carbon::create(2026, 10, 2, 12, 0, 0));

        $response = $this->actingAs($this->user)->get('/print/data-shopping?start_date=2026-10-04&end_date=2026-10-04');
        $response->assertStatus(200);
        $response->assertSee('INV-OCT-04');
        $response->assertDontSee('INV-OCT-02');
    }

    /**
     * TEST 157: Halaman dan export memiliki jumlah transaksi yang sama
     */
    public function test_157_halaman_dan_export_memiliki_jumlah_transaksi_sama(): void
    {
        $this->createSale('INV-OCT-04-A', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 4, 10, 0, 0));
        $this->createSale('INV-OCT-04-B', 'offline', 'qris', 25000, 25000, 0, 10000, Carbon::create(2026, 10, 4, 14, 0, 0));
        $this->createSale('INV-OCT-02-A', 'offline', 'cash', 30000, 30000, 0, 12000, Carbon::create(2026, 10, 2, 12, 0, 0));

        $livewire = Livewire::actingAs($this->user)
            ->test(PreviewPrintShopping::class)
            ->set('start_date', '2026-10-04')
            ->set('end_date', '2026-10-04');

        $livewire->assertSee('INV-OCT-04-A');
        $livewire->assertSee('INV-OCT-04-B');
        $livewire->assertDontSee('INV-OCT-02-A');

        $excel = new xlsReportShopping('2026-10-04', '2026-10-04');
        $this->assertCount(2, $excel->collection());

        $print = $this->actingAs($this->user)->get('/print/data-shopping?start_date=2026-10-04&end_date=2026-10-04');
        $print->assertSee('INV-OCT-04-A');
        $print->assertSee('INV-OCT-04-B');
        $print->assertDontSee('INV-OCT-02-A');
    }

    /**
     * TEST 158: Total omzet sama antara halaman, print, dan excel
     */
    public function test_158_total_omzet_sama_pada_seluruh_jalur_export(): void
    {
        $this->createSale('INV-1', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 4, 10, 0, 0));
        $this->createSale('INV-2', 'offline', 'qris', 30000, 30000, 0, 12000, Carbon::create(2026, 10, 4, 14, 0, 0));
        $this->createSale('INV-EXCLUDE', 'offline', 'cash', 50000, 50000, 0, 20000, Carbon::create(2026, 10, 2, 12, 0, 0));

        $summary = SalesProfitService::getSummaryData(SalesProfitService::getSalesData('2026-10-04', '2026-10-04'), '2026-10-04', '2026-10-04');
        $this->assertEquals(50000, $summary['total_omzet']);

        $excel = new xlsReportShopping('2026-10-04', '2026-10-04');
        $this->assertEquals(50000, $excel->getSummary()['total_omzet']);

        $print = $this->actingAs($this->user)->get('/print/data-shopping?start_date=2026-10-04&end_date=2026-10-04');
        $print->assertSee('Rp50.000');
    }

    /**
     * TEST 159: Total HPP sama pada dataset terfilter
     */
    public function test_159_total_hpp_sama_pada_dataset_terfilter(): void
    {
        $this->createSale('INV-1', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 4, 10, 0, 0));
        $this->createSale('INV-2', 'offline', 'qris', 30000, 30000, 0, 12000, Carbon::create(2026, 10, 4, 14, 0, 0));
        $this->createSale('INV-EXCLUDE', 'offline', 'cash', 50000, 50000, 0, 20000, Carbon::create(2026, 10, 2, 12, 0, 0));

        $summary = SalesProfitService::getSummaryData(SalesProfitService::getSalesData('2026-10-04', '2026-10-04'), '2026-10-04', '2026-10-04');
        $this->assertEquals(20000, $summary['total_hpp']); // 8000 + 12000 = 20000

        $excel = new xlsReportShopping('2026-10-04', '2026-10-04');
        $this->assertEquals(20000, $excel->getSummary()['total_hpp']);
    }

    /**
     * TEST 160: Laba kotor sama pada dataset terfilter
     */
    public function test_160_laba_kotor_sama_pada_dataset_terfilter(): void
    {
        $this->createSale('INV-1', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 4, 10, 0, 0));
        $this->createSale('INV-2', 'offline', 'qris', 30000, 30000, 0, 12000, Carbon::create(2026, 10, 4, 14, 0, 0));

        $summary = SalesProfitService::getSummaryData(SalesProfitService::getSalesData('2026-10-04', '2026-10-04'), '2026-10-04', '2026-10-04');
        // Total Omzet 50000 - Total HPP 20000 = 30000
        $this->assertEquals(30000, $summary['laba_kotor']);
    }

    /**
     * TEST 161: Laba bersih sama pada dataset terfilter
     */
    public function test_161_laba_bersih_sama_pada_dataset_terfilter(): void
    {
        $this->createSale('INV-1', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 4, 10, 0, 0));
        $this->createSale('INV-2', 'offline', 'qris', 30000, 30000, 0, 12000, Carbon::create(2026, 10, 4, 14, 0, 0));

        // Expense on Oct 4
        Overhead::create([
            'user_id' => $this->user->id,
            'name' => 'Listrik Harian',
            'category' => 'Utility',
            'nominal_monthly' => 5000,
            'status' => 'active',
            'created_at' => Carbon::create(2026, 10, 4, 8, 0, 0),
        ]);

        // Expense outside Oct 4
        Overhead::create([
            'user_id' => $this->user->id,
            'name' => 'Sewa Gudang',
            'category' => 'Rent',
            'nominal_monthly' => 50000,
            'status' => 'active',
            'created_at' => Carbon::create(2026, 9, 1, 8, 0, 0),
        ]);

        $summary = SalesProfitService::getSummaryData(SalesProfitService::getSalesData('2026-10-04', '2026-10-04'), '2026-10-04', '2026-10-04');
        // Laba Kotor 30000 - Expense 5000 = 25000
        $this->assertEquals(5000, $summary['total_pengeluaran']);
        $this->assertEquals(25000, $summary['laba_bersih']);
    }

    /**
     * TEST 162: Penjualan Cash, QRIS, dan Online terpisah akurat sesuai periode
     */
    public function test_162_breakdown_cash_qris_online_akurat_sesuai_periode(): void
    {
        $this->createSale('INV-CASH', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 4, 10, 0, 0));
        $this->createSale('INV-QRIS', 'offline', 'qris', 30000, 30000, 0, 12000, Carbon::create(2026, 10, 4, 11, 0, 0));
        $this->createSale('INV-ONLINE', 'online', 'cash', 25000, 25000, 0, 10000, Carbon::create(2026, 10, 4, 12, 0, 0));

        // Outside period
        $this->createSale('INV-CASH-OLD', 'offline', 'cash', 100000, 100000, 0, 40000, Carbon::create(2026, 9, 20, 10, 0, 0));

        $summary = SalesProfitService::getSummaryData(SalesProfitService::getSalesData('2026-10-04', '2026-10-04'), '2026-10-04', '2026-10-04');
        $this->assertEquals(20000, $summary['penjualan_cash']);
        $this->assertEquals(30000, $summary['penjualan_qris']);
        $this->assertEquals(25000, $summary['penjualan_online']);
        $this->assertEquals(75000, $summary['total_omzet']);
    }

    /**
     * TEST 163: Expense mengikuti periode filter
     */
    public function test_163_expense_mengikuti_periode_filter(): void
    {
        Overhead::create([
            'user_id' => $this->user->id,
            'name' => 'Air Oct 4',
            'category' => 'Utility',
            'nominal_monthly' => 10000,
            'status' => 'active',
            'created_at' => Carbon::create(2026, 10, 4, 9, 0, 0),
        ]);

        Overhead::create([
            'user_id' => $this->user->id,
            'name' => 'Listrik Oct 2',
            'category' => 'Utility',
            'nominal_monthly' => 20000,
            'status' => 'active',
            'created_at' => Carbon::create(2026, 10, 2, 9, 0, 0),
        ]);

        $expenseOct4 = SalesProfitService::getPeriodExpense('2026-10-04', '2026-10-04');
        $this->assertEquals(10000, $expenseOct4);

        $expenseRange = SalesProfitService::getPeriodExpense('2026-10-02', '2026-10-04');
        $this->assertEquals(30000, $expenseRange);
    }

    /**
     * TEST 164: Tidak ada transaksi di luar periode yang bocor ke dataset
     */
    public function test_164_tidak_ada_transaksi_di_luar_periode(): void
    {
        $this->createSale('INV-SEP-30', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 9, 30, 23, 59, 59));
        $this->createSale('INV-OCT-01', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 1, 0, 0, 0));
        $this->createSale('INV-OCT-31', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 31, 23, 59, 59));
        $this->createSale('INV-NOV-01', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 11, 1, 0, 0, 0));

        $data = SalesProfitService::getSalesData('2026-10-01', '2026-10-31');
        $invoices = $data->pluck('invoice')->toArray();

        $this->assertContains('INV-OCT-01', $invoices);
        $this->assertContains('INV-OCT-31', $invoices);
        $this->assertNotContains('INV-SEP-30', $invoices);
        $this->assertNotContains('INV-NOV-01', $invoices);
    }

    /**
     * TEST 165: Kedua tanggal kosong → SEMUA transaksi masuk (tidak otomatis hari ini)
     */
    public function test_165_kedua_tanggal_kosong_semua_transaksi_masuk(): void
    {
        $this->createSale('INV-OLD', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2025, 1, 1, 10, 0, 0));
        $this->createSale('INV-MID', 'offline', 'cash', 30000, 30000, 0, 10000, Carbon::create(2026, 5, 15, 10, 0, 0));
        $this->createSale('INV-NEW', 'offline', 'cash', 40000, 40000, 0, 15000, Carbon::create(2026, 10, 4, 10, 0, 0));

        $data = SalesProfitService::getSalesData(null, null);
        $this->assertCount(3, $data);

        $dataEmpty = SalesProfitService::getSalesData('', '');
        $this->assertCount(3, $dataEmpty);

        $excel = new xlsReportShopping(null, null);
        $this->assertCount(3, $excel->collection());

        $responsePrint = $this->actingAs($this->user)->get('/print/data-shopping');
        $responsePrint->assertSee('INV-OLD');
        $responsePrint->assertSee('INV-MID');
        $responsePrint->assertSee('INV-NEW');
    }

    /**
     * TEST 166: Hanya start_date diisi → transaksi >= start_date masuk
     */
    public function test_166_hanya_start_date_diisi_transaksi_setelah_atau_sama_masuk(): void
    {
        $this->createSale('INV-OCT-03', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 3, 23, 59, 0));
        $this->createSale('INV-OCT-04', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 4, 10, 0, 0));
        $this->createSale('INV-OCT-10', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 10, 10, 0, 0));

        $data = SalesProfitService::getSalesData('2026-10-04', null);
        $this->assertCount(2, $data);
        $invoices = $data->pluck('invoice')->toArray();
        $this->assertContains('INV-OCT-04', $invoices);
        $this->assertContains('INV-OCT-10', $invoices);
        $this->assertNotContains('INV-OCT-03', $invoices);
    }

    /**
     * TEST 167: Hanya end_date diisi → transaksi <= end_date masuk
     */
    public function test_167_hanya_end_date_diisi_transaksi_sebelum_atau_sama_masuk(): void
    {
        $this->createSale('INV-OCT-01', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 1, 10, 0, 0));
        $this->createSale('INV-OCT-04', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 4, 23, 0, 0));
        $this->createSale('INV-OCT-05', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 5, 8, 0, 0));

        $data = SalesProfitService::getSalesData(null, '2026-10-04');
        $this->assertCount(2, $data);
        $invoices = $data->pluck('invoice')->toArray();
        $this->assertContains('INV-OCT-01', $invoices);
        $this->assertContains('INV-OCT-04', $invoices);
        $this->assertNotContains('INV-OCT-05', $invoices);
    }

    /**
     * TEST 168: Format tanggal UI DD/MM/YYYY diparsing konsisten sebagai hari-bulan-tahun
     */
    public function test_168_format_tanggal_ui_dd_mm_yyyy_diparsing_konsisten(): void
    {
        // 04/10/2026 must be October 4, 2026 (not April 10, 2026)
        $parsed = SalesProfitService::parseDate('04/10/2026');
        $this->assertNotNull($parsed);
        $this->assertEquals(4, $parsed->day);
        $this->assertEquals(10, $parsed->month);
        $this->assertEquals(2026, $parsed->year);

        // Test with dash DD-MM-YYYY
        $parsedDash = SalesProfitService::parseDate('04-10-2026');
        $this->assertNotNull($parsedDash);
        $this->assertEquals(4, $parsedDash->day);
        $this->assertEquals(10, $parsedDash->month);
        $this->assertEquals(2026, $parsedDash->year);

        // Verify querying with DD/MM/YYYY
        $this->createSale('INV-OCT-04', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 10, 4, 12, 0, 0));
        $this->createSale('INV-APR-10', 'offline', 'cash', 20000, 20000, 0, 8000, Carbon::create(2026, 4, 10, 12, 0, 0));

        $data = SalesProfitService::getSalesData('04/10/2026', '04/10/2026');
        $this->assertCount(1, $data);
        $this->assertEquals('INV-OCT-04', $data->first()->invoice);
    }

    /**
     * TEST 169: Livewire filter update dynamically alters URL query params and links
     */
    public function test_169_livewire_filter_update_links(): void
    {
        $this->actingAs($this->user);

        $test = Livewire::test(PreviewPrintShopping::class);

        // Initially no query params
        $test->assertSee(route('data.shopping.excel'));

        // Set filter
        $test->set('start_date', '2026-10-04')
             ->set('end_date', '2026-10-04');

        $test->assertSee('start_date=2026-10-04');
        $test->assertSee('end_date=2026-10-04');

        // Reset filter
        $test->call('resetFilter');
        $this->assertNull($test->get('start_date'));
        $this->assertNull($test->get('end_date'));
    }

    /**
     * TEST 170: Ringkasan keuangan lengkap pada dataset terfilter
     */
    public function test_170_ringkasan_keuangan_lengkap_terfilter(): void
    {
        $this->createSale('INV-1', 'offline', 'cash', 50000, 60000, 10000, 25000, Carbon::create(2026, 10, 4, 10, 0, 0));
        $this->createSale('INV-2', 'offline', 'qris', 30000, 30000, 0, 15000, Carbon::create(2026, 10, 4, 12, 0, 0));
        $this->createSale('INV-3', 'online', 'cash', 40000, 40000, 0, 20000, Carbon::create(2026, 10, 4, 14, 0, 0));

        // Expense on Oct 4
        Overhead::create([
            'user_id' => $this->user->id,
            'name' => 'Operational Harian',
            'category' => 'Operational',
            'nominal_monthly' => 10000,
            'status' => 'active',
            'created_at' => Carbon::create(2026, 10, 4, 8, 0, 0),
        ]);

        $summary = SalesProfitService::getSummaryData(SalesProfitService::getSalesData('2026-10-04', '2026-10-04'), '2026-10-04', '2026-10-04');

        $this->assertEquals(3, $summary['total_transactions']);
        $this->assertEquals(120000, $summary['total_omzet']);
        $this->assertEquals(50000, $summary['penjualan_cash']);
        $this->assertEquals(30000, $summary['penjualan_qris']);
        $this->assertEquals(40000, $summary['penjualan_online']);
        $this->assertEquals(60000, $summary['total_hpp']); // 25k + 15k + 20k
        $this->assertEquals(60000, $summary['laba_kotor']); // 120k - 60k
        $this->assertEquals(50.0, $summary['margin_laba_kotor']);
        $this->assertEquals(10000, $summary['total_pengeluaran']);
        $this->assertEquals(50000, $summary['laba_bersih']); // 60k - 10k
        $this->assertEquals(round((50000 / 120000) * 100, 2), $summary['margin_laba_bersih']);
    }
}

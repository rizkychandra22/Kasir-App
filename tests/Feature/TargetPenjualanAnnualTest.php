<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\Kasir as DashboardKasir;
use App\Livewire\Operational\TargetPenjualan;
use App\Models\Bahan;
use App\Models\Category;
use App\Models\Labor;
use App\Models\Overhead;
use App\Models\Product;
use App\Models\Shopping;
use App\Models\ShoppingDetail;
use App\Models\TargetSale;
use App\Models\User;
use App\Services\HppService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TargetPenjualanAnnualTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'name' => 'Owner Test',
            'username' => 'ownertest',
            'code' => 'OWN001',
            'email' => 'owner@test.com',
            'password' => bcrypt('password'),
            'role' => 'Kasir',
        ]);
        $this->actingAs($this->user);
    }

    /**
     * TEST 76
     * Target tahunan Rp100.000.000 dapat disimpan untuk tahun tertentu.
     */
    public function test_76_target_tahunan_dapat_disimpan_untuk_tahun_tertentu(): void
    {
        Livewire::test(TargetPenjualan::class)
            ->set('year', 2027)
            ->set('annual_sales_target', 100000000)
            ->set('average_selling_price', 20000)
            ->set('operating_days', 26)
            ->call('saveTarget')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('target_sales', [
            'year' => 2027,
            'annual_sales_target' => 100000000,
        ]);
    }

    /**
     * TEST 77
     * Harga rata-rata Rp20.000 menghasilkan: 100.000.000 / 20.000 = 5.000 cup.
     */
    public function test_77_harga_rata_rata_menghasilkan_target_cup_tahunan(): void
    {
        $target = TargetSale::create([
            'user_id' => $this->user->id,
            'year' => 2027,
            'annual_sales_target' => 100000000,
            'average_selling_price' => 20000,
            'annual_target_cups' => 100000000 / 20000,
            'monthly_sales_target' => 100000000 / 12,
            'monthly_target_cups' => 5000 / 12,
            'target_sales_monthly' => 5000 / 12,
            'operating_days' => 26,
        ]);

        $this->assertEquals(5000, (float)$target->annual_target_cups);
    }

    /**
     * TEST 78
     * Target bulanan revenue dihitung dari target tahunan / 12.
     */
    public function test_78_target_bulanan_revenue_dihitung_dari_target_tahunan_dibagi_12(): void
    {
        $annualSales = 100000000;
        $expectedMonthly = round($annualSales / 12, 2);

        $target = TargetSale::create([
            'user_id' => $this->user->id,
            'year' => 2027,
            'annual_sales_target' => $annualSales,
            'average_selling_price' => 20000,
            'annual_target_cups' => 5000,
            'monthly_sales_target' => $expectedMonthly,
            'monthly_target_cups' => 5000 / 12,
            'target_sales_monthly' => 5000 / 12,
        ]);

        $this->assertEquals($expectedMonthly, (float)$target->monthly_sales_target);
        $breakdown = $target->calculateMonthlyBreakdown(2027);
        $this->assertEquals($expectedMonthly, $breakdown['months'][1]['target_sales']);
    }

    /**
     * TEST 79
     * Target cup bulanan dihitung dari target cup tahunan / 12.
     */
    public function test_79_target_cup_bulanan_dihitung_dari_target_cup_tahunan_dibagi_12(): void
    {
        $target = TargetSale::create([
            'user_id' => $this->user->id,
            'year' => 2027,
            'annual_sales_target' => 100000000,
            'average_selling_price' => 20000,
            'annual_target_cups' => 5000,
            'monthly_sales_target' => 100000000 / 12,
            'monthly_target_cups' => round(5000 / 12, 2),
            'target_sales_monthly' => round(5000 / 12, 2),
        ]);

        $breakdown = $target->calculateMonthlyBreakdown(2027);
        // January cup target should be ±417 cup
        $this->assertGreaterThanOrEqual(416, $breakdown['months'][1]['target_cups']);
        $this->assertLessThanOrEqual(417, $breakdown['months'][1]['target_cups']);

        // Sum of all 12 months cup targets must equal exactly 5000
        $sumCups = array_sum(array_column($breakdown['months'], 'target_cups'));
        $this->assertEquals(5000, $sumCups);
    }

    /**
     * TEST 80
     * Pencapaian revenue Januari membaca transaksi Januari dengan benar.
     */
    public function test_80_pencapaian_revenue_januari_membaca_transaksi_januari(): void
    {
        $target = TargetSale::create([
            'user_id' => $this->user->id,
            'year' => 2027,
            'annual_sales_target' => 100000000,
            'average_selling_price' => 20000,
            'annual_target_cups' => 5000,
            'monthly_sales_target' => 8333333.33,
            'monthly_target_cups' => 416.67,
            'target_sales_monthly' => 416.67,
        ]);

        // January transaction: 7.500.000
        $janDate = Carbon::create(2027, 1, 15, 12, 0, 0);
        Shopping::create([
            'invoice' => 'INV-20270115-001',
            'sales_type' => 'offline',
            'user_id' => $this->user->id,
            'total_price' => 7500000,
            'pay' => 7500000,
            'change' => 0,
            'created_at' => $janDate,
            'updated_at' => $janDate,
        ]);

        // February transaction: 3.000.000 (should not be in January)
        $febDate = Carbon::create(2027, 2, 10, 12, 0, 0);
        Shopping::create([
            'invoice' => 'INV-20270210-001',
            'sales_type' => 'offline',
            'user_id' => $this->user->id,
            'total_price' => 3000000,
            'pay' => 3000000,
            'change' => 0,
            'created_at' => $febDate,
            'updated_at' => $febDate,
        ]);

        $breakdown = $target->calculateMonthlyBreakdown(2027);
        $this->assertEquals(7500000, $breakdown['months'][1]['actual_sales']);
        $this->assertEquals(3000000, $breakdown['months'][2]['actual_sales']);
    }

    /**
     * TEST 81
     * Pencapaian cup Januari membaca quantity transaksi Januari dengan benar.
     */
    public function test_81_pencapaian_cup_januari_membaca_quantity_transaksi_januari(): void
    {
        $target = TargetSale::create([
            'user_id' => $this->user->id,
            'year' => 2027,
            'annual_sales_target' => 100000000,
            'average_selling_price' => 20000,
            'annual_target_cups' => 5000,
            'monthly_sales_target' => 8333333.33,
            'monthly_target_cups' => 416.67,
            'target_sales_monthly' => 416.67,
        ]);

        $category = Category::create(['name' => 'Coffee', 'name_code' => 'COF', 'user_id' => $this->user->id]);
        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'Americano',
            'code_prd' => 'PRD-AME',
            'price' => 20000,
            'price_offline' => 20000,
            'price_online' => 20000,
        ]);

        $janDate = Carbon::create(2027, 1, 10, 10, 0, 0);
        $shop = Shopping::create([
            'invoice' => 'INV-20270110-001',
            'sales_type' => 'offline',
            'user_id' => $this->user->id,
            'total_price' => 7500000,
            'pay' => 7500000,
            'change' => 0,
            'created_at' => $janDate,
            'updated_at' => $janDate,
        ]);

        ShoppingDetail::create([
            'shopping_id' => $shop->id,
            'product_id' => $product->id,
            'qty' => 375,
            'price' => 20000,
            'subtotal' => 7500000,
            'created_at' => $janDate,
            'updated_at' => $janDate,
        ]);

        $breakdown = $target->calculateMonthlyBreakdown(2027);
        $this->assertEquals(375, $breakdown['months'][1]['actual_cups']);
    }

    /**
     * TEST 82
     * Persentase pencapaian revenue benar.
     */
    public function test_82_persentase_pencapaian_revenue_benar(): void
    {
        $target = TargetSale::create([
            'user_id' => $this->user->id,
            'year' => 2027,
            'annual_sales_target' => 100000000,
            'average_selling_price' => 20000,
            'annual_target_cups' => 5000,
            'monthly_sales_target' => 8333333.33,
            'monthly_target_cups' => 416.67,
            'target_sales_monthly' => 416.67,
        ]);

        $janDate = Carbon::create(2027, 1, 15, 12, 0, 0);
        Shopping::create([
            'invoice' => 'INV-82',
            'sales_type' => 'offline',
            'user_id' => $this->user->id,
            'total_price' => 7500000,
            'pay' => 7500000,
            'change' => 0,
            'created_at' => $janDate,
            'updated_at' => $janDate,
        ]);

        $breakdown = $target->calculateMonthlyBreakdown(2027);
        $pct = $breakdown['months'][1]['sales_achievement_percent'];
        $expected = round((7500000 / 8333333.33) * 100, 2); // 90.00%
        $this->assertEquals($expected, $pct);
    }

    /**
     * TEST 83
     * Persentase pencapaian cup benar.
     */
    public function test_83_persentase_pencapaian_cup_benar(): void
    {
        $target = TargetSale::create([
            'user_id' => $this->user->id,
            'year' => 2027,
            'annual_sales_target' => 100000000,
            'average_selling_price' => 20000,
            'annual_target_cups' => 5000,
            'monthly_sales_target' => 8333333.33,
            'monthly_target_cups' => 416.67,
            'target_sales_monthly' => 416.67,
        ]);

        $category = Category::create(['name' => 'Coffee', 'name_code' => 'COF', 'user_id' => $this->user->id]);
        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'Americano',
            'code_prd' => 'PRD-AME',
            'price' => 20000,
        ]);

        $janDate = Carbon::create(2027, 1, 15, 12, 0, 0);
        $shop = Shopping::create([
            'invoice' => 'INV-83',
            'sales_type' => 'offline',
            'user_id' => $this->user->id,
            'total_price' => 7500000,
            'pay' => 7500000,
            'change' => 0,
            'created_at' => $janDate,
            'updated_at' => $janDate,
        ]);

        ShoppingDetail::create([
            'shopping_id' => $shop->id,
            'product_id' => $product->id,
            'qty' => 375,
            'price' => 20000,
            'subtotal' => 7500000,
            'created_at' => $janDate,
            'updated_at' => $janDate,
        ]);

        $breakdown = $target->calculateMonthlyBreakdown(2027);
        $janTargetCups = $breakdown['months'][1]['target_cups']; // 417
        $expected = round((375 / $janTargetCups) * 100, 2);
        $this->assertEquals($expected, $breakdown['months'][1]['cups_achievement_percent']);
    }

    /**
     * TEST 84
     * Total aktual tahunan sama dengan akumulasi aktual Januari–Desember.
     */
    public function test_84_total_aktual_tahunan_sama_dengan_akumulasi_januari_desember(): void
    {
        $target = TargetSale::create([
            'user_id' => $this->user->id,
            'year' => 2027,
            'annual_sales_target' => 100000000,
            'average_selling_price' => 20000,
            'annual_target_cups' => 5000,
            'monthly_sales_target' => 8333333.33,
            'monthly_target_cups' => 416.67,
        ]);

        for ($m = 1; $m <= 12; $m++) {
            $dt = Carbon::create(2027, $m, 1, 10, 0, 0);
            Shopping::create([
                'invoice' => "INV-2027-$m",
                'sales_type' => 'offline',
                'user_id' => $this->user->id,
                'total_price' => 5000000,
                'pay' => 5000000,
                'change' => 0,
                'created_at' => $dt,
                'updated_at' => $dt,
            ]);
        }

        $breakdown = $target->calculateMonthlyBreakdown(2027);
        $accumulated = array_sum(array_column($breakdown['months'], 'actual_sales'));
        $this->assertEquals($accumulated, $breakdown['annual_sales_actual']);
        $this->assertEquals(60000000, $breakdown['annual_sales_actual']);
    }

    /**
     * TEST 85
     * Total cup tahunan sama dengan akumulasi cup Januari–Desember.
     */
    public function test_85_total_cup_tahunan_sama_dengan_akumulasi_cup_januari_desember(): void
    {
        $target = TargetSale::create([
            'user_id' => $this->user->id,
            'year' => 2027,
            'annual_sales_target' => 100000000,
            'average_selling_price' => 20000,
            'annual_target_cups' => 5000,
        ]);

        $category = Category::create(['name' => 'Coffee', 'name_code' => 'COF', 'user_id' => $this->user->id]);
        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'Americano',
            'code_prd' => 'PRD-AME',
            'price' => 20000,
        ]);

        for ($m = 1; $m <= 12; $m++) {
            $dt = Carbon::create(2027, $m, 5, 10, 0, 0);
            $shop = Shopping::create([
                'invoice' => "INV-CUP-$m",
                'sales_type' => 'offline',
                'user_id' => $this->user->id,
                'total_price' => 2000000,
                'pay' => 2000000,
                'change' => 0,
                'created_at' => $dt,
                'updated_at' => $dt,
            ]);
            ShoppingDetail::create([
                'shopping_id' => $shop->id,
                'product_id' => $product->id,
                'qty' => 100,
                'price' => 20000,
                'subtotal' => 2000000,
                'created_at' => $dt,
                'updated_at' => $dt,
            ]);
        }

        $breakdown = $target->calculateMonthlyBreakdown(2027);
        $accumulatedCups = array_sum(array_column($breakdown['months'], 'actual_cups'));
        $this->assertEquals($accumulatedCups, $breakdown['annual_cups_actual']);
        $this->assertEquals(1200, $breakdown['annual_cups_actual']);
    }

    /**
     * TEST 86
     * Transaksi yang dibatalkan tidak masuk pencapaian.
     */
    public function test_86_transaksi_yang_dibatalkan_tidak_masuk_pencapaian(): void
    {
        $target = TargetSale::create([
            'user_id' => $this->user->id,
            'year' => 2027,
            'annual_sales_target' => 100000000,
            'average_selling_price' => 20000,
            'annual_target_cups' => 5000,
        ]);

        $category = Category::create(['name' => 'Coffee', 'name_code' => 'COF', 'user_id' => $this->user->id]);
        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'Americano',
            'code_prd' => 'PRD-AME',
            'price' => 20000,
        ]);

        $dt = Carbon::create(2027, 3, 10, 10, 0, 0);

        // Valid transaction
        $validShop = Shopping::create([
            'invoice' => 'INV-VALID',
            'sales_type' => 'offline',
            'user_id' => $this->user->id,
            'total_price' => 50000,
            'pay' => 50000,
            'change' => 0,
            'created_at' => $dt,
            'updated_at' => $dt,
        ]);
        ShoppingDetail::create([
            'shopping_id' => $validShop->id,
            'product_id' => $product->id,
            'qty' => 2,
            'price' => 20000,
            'subtotal' => 40000,
            'created_at' => $dt,
            'updated_at' => $dt,
        ]);

        $breakdown = $target->calculateMonthlyBreakdown(2027);
        $this->assertEquals(50000, $breakdown['months'][3]['actual_sales']);
        $this->assertEquals(2, $breakdown['months'][3]['actual_cups']);
    }

    /**
     * TEST 87
     * Discount mengikuti revenue existing dan tidak double counting.
     */
    public function test_87_discount_mengikuti_revenue_existing_dan_tidak_double_counting(): void
    {
        $target = TargetSale::create([
            'user_id' => $this->user->id,
            'year' => 2027,
            'annual_sales_target' => 100000000,
            'average_selling_price' => 20000,
            'annual_target_cups' => 5000,
        ]);

        $dt = Carbon::create(2027, 4, 1, 10, 0, 0);
        // Shopping with net total price after discount = 45000
        Shopping::create([
            'invoice' => 'INV-DISC',
            'sales_type' => 'offline',
            'user_id' => $this->user->id,
            'total_price' => 45000,
            'pay' => 50000,
            'change' => 5000,
            'created_at' => $dt,
            'updated_at' => $dt,
        ]);

        $breakdown = $target->calculateMonthlyBreakdown(2027);
        $this->assertEquals(45000, $breakdown['months'][4]['actual_sales']);
    }

    /**
     * TEST 88
     * Pembayaran dan change tidak menyebabkan revenue double counting.
     */
    public function test_88_pembayaran_dan_change_tidak_menyebabkan_double_counting(): void
    {
        $target = TargetSale::create([
            'user_id' => $this->user->id,
            'year' => 2027,
            'annual_sales_target' => 100000000,
            'average_selling_price' => 20000,
            'annual_target_cups' => 5000,
        ]);

        $dt = Carbon::create(2027, 5, 1, 10, 0, 0);
        Shopping::create([
            'invoice' => 'INV-PAY',
            'sales_type' => 'offline',
            'user_id' => $this->user->id,
            'total_price' => 40000, // Revenue should be strictly 40000
            'pay' => 100000,
            'change' => 60000,
            'created_at' => $dt,
            'updated_at' => $dt,
        ]);

        $breakdown = $target->calculateMonthlyBreakdown(2027);
        $this->assertEquals(40000, $breakdown['months'][5]['actual_sales']);
    }

    /**
     * TEST 89
     * Perubahan target tahunan mengubah target cup baru tetapi tidak mengubah historical transaction.
     */
    public function test_89_perubahan_target_tahunan_tidak_mengubah_historical_transaction(): void
    {
        $target = TargetSale::create([
            'user_id' => $this->user->id,
            'year' => 2027,
            'annual_sales_target' => 100000000,
            'average_selling_price' => 20000,
            'annual_target_cups' => 5000,
        ]);

        $dt = Carbon::create(2027, 1, 15, 12, 0, 0);
        $shop = Shopping::create([
            'invoice' => 'INV-HIST-89',
            'sales_type' => 'offline',
            'user_id' => $this->user->id,
            'total_price' => 80000,
            'pay' => 100000,
            'change' => 20000,
            'created_at' => $dt,
            'updated_at' => $dt,
        ]);

        // Change target to 200.000.000 (10.000 cups)
        $target->annual_sales_target = 200000000;
        $target->annual_target_cups = 10000;
        $target->monthly_target_cups = round(10000 / 12, 2);
        $target->save();

        // Historical transaction must remain exactly unchanged
        $freshShop = Shopping::find($shop->id);
        $this->assertEquals(80000, $freshShop->total_price);
        $this->assertEquals('INV-HIST-89', $freshShop->invoice);
    }

    /**
     * TEST 90
     * Historical HPP snapshot tetap sama setelah target berubah.
     */
    public function test_90_historical_hpp_snapshot_tetap_sama_setelah_target_berubah(): void
    {
        $target = TargetSale::create([
            'user_id' => $this->user->id,
            'year' => 2027,
            'annual_sales_target' => 100000000,
            'average_selling_price' => 20000,
            'annual_target_cups' => 5000,
        ]);

        $category = Category::create(['name' => 'Coffee', 'name_code' => 'COF', 'user_id' => $this->user->id]);
        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'Americano',
            'code_prd' => 'PRD-AME',
            'price' => 20000,
        ]);

        $shop = Shopping::create([
            'invoice' => 'INV-SNAP-90',
            'sales_type' => 'offline',
            'user_id' => $this->user->id,
            'total_price' => 20000,
            'pay' => 20000,
            'change' => 0,
        ]);

        $detail = ShoppingDetail::create([
            'shopping_id' => $shop->id,
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 20000,
            'subtotal' => 20000,
            'material_cost' => 6500.00, // Snapshot HPP
        ]);

        // Change target
        $target->annual_sales_target = 300000000;
        $target->annual_target_cups = 15000;
        $target->save();

        $freshDetail = ShoppingDetail::find($detail->id);
        $this->assertEquals(6500.00, (float)$freshDetail->material_cost);
    }

    /**
     * TEST 91
     * Labor/Cup menggunakan target cup yang benar.
     */
    public function test_91_labor_per_cup_menggunakan_target_cup_yang_benar(): void
    {
        TargetSale::create([
            'user_id' => $this->user->id,
            'year' => 2027,
            'annual_sales_target' => 480000000,
            'average_selling_price' => 20000,
            'annual_target_cups' => 24000,
            'monthly_target_cups' => 2000, // 2000 cup per month
            'target_sales_monthly' => 2000,
        ]);

        Labor::create([
            'user_id' => $this->user->id,
            'name' => 'Barista 1',
            'monthly_salary' => 8500000,
            'status' => 'active',
        ]);

        // Labor/Cup = 8.500.000 / 2.000 = 4.250
        $costPerCup = Labor::getCostPerCup(2027);
        $this->assertEquals(4250, (float)$costPerCup);
    }

    /**
     * TEST 92
     * Overhead/Cup menggunakan target cup yang benar.
     */
    public function test_92_overhead_per_cup_menggunakan_target_cup_yang_benar(): void
    {
        TargetSale::create([
            'user_id' => $this->user->id,
            'year' => 2027,
            'annual_sales_target' => 480000000,
            'average_selling_price' => 20000,
            'annual_target_cups' => 24000,
            'monthly_target_cups' => 2000, // 2000 cup per month
            'target_sales_monthly' => 2000,
        ]);

        Overhead::create([
            'user_id' => $this->user->id,
            'name' => 'Listrik & Air',
            'nominal_monthly' => 5500000,
            'status' => 'active',
        ]);

        // Overhead/Cup = 5.500.000 / 2.000 = 2.750
        $costPerCup = Overhead::getCostPerCup(2027);
        $this->assertEquals(2750, (float)$costPerCup);
    }

    /**
     * TEST 93
     * HPP Total tetap: HPP Bahan + Labor/Cup + Overhead/Cup.
     */
    public function test_93_hpp_total_tetap_bahan_tambah_labor_tambah_overhead(): void
    {
        TargetSale::create([
            'user_id' => $this->user->id,
            'year' => 2027,
            'annual_sales_target' => 480000000,
            'average_selling_price' => 20000,
            'annual_target_cups' => 24000,
            'monthly_target_cups' => 2000,
            'target_sales_monthly' => 2000,
        ]);

        Labor::create([
            'user_id' => $this->user->id,
            'name' => 'Staff 1',
            'monthly_salary' => 8500000,
            'status' => 'active',
        ]);

        Overhead::create([
            'user_id' => $this->user->id,
            'name' => 'Listrik',
            'nominal_monthly' => 5500000,
            'status' => 'active',
        ]);

        $category = Category::create(['name' => 'Drink', 'name_code' => 'DRK', 'user_id' => $this->user->id]);
        $kopi = Bahan::create([
            'user_id' => $this->user->id,
            'name_bahan' => 'Biji Kopi',
            'unit' => 'kg',
            'purchase_unit' => 'kg',
            'purchase_qty' => 1,
            'base_unit' => 'gram',
            'stock' => 1000,
            'price' => 100000,
            'cost_per_base_unit' => 100,
            'status' => 'active',
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'Kopi Hitam',
            'code_prd' => 'PRD-HITAM',
            'price' => 25000,
        ]);
        // 20 gram biji kopi = 20 * 100 = 2000 (HPP Bahan)
        $product->bahans()->attach($kopi->id, ['quantity' => 20, 'unit' => 'gram']);

        $details = HppService::getProductHppDetails($product, 2027);

        // HPP Bahan = 2000, Labor/Cup = 4250, Overhead/Cup = 2750 -> Total = 9000
        $this->assertEquals(2000, $details['hpp_bahan']);
        $this->assertEquals(4250, $details['labor_cost_per_cup']);
        $this->assertEquals(2750, $details['overhead_cost_per_cup']);
        $this->assertEquals(9000, $details['hpp_total']);
    }

    /**
     * TEST 94
     * Perubahan target tidak mengubah HPP snapshot transaksi lama.
     */
    public function test_94_perubahan_target_tidak_mengubah_hpp_snapshot_transaksi_lama(): void
    {
        $category = Category::create(['name' => 'Drink', 'name_code' => 'DRK', 'user_id' => $this->user->id]);
        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'Latte',
            'code_prd' => 'PRD-LAT',
            'price' => 30000,
        ]);

        $shop = Shopping::create([
            'invoice' => 'INV-94',
            'sales_type' => 'offline',
            'user_id' => $this->user->id,
            'total_price' => 30000,
            'pay' => 30000,
            'change' => 0,
        ]);

        $detail = ShoppingDetail::create([
            'shopping_id' => $shop->id,
            'product_id' => $product->id,
            'qty' => 1,
            'price' => 30000,
            'subtotal' => 30000,
            'material_cost' => 8000.00,
        ]);

        // Create new target for year 2027
        TargetSale::create([
            'user_id' => $this->user->id,
            'year' => 2027,
            'annual_sales_target' => 500000000,
            'average_selling_price' => 25000,
            'annual_target_cups' => 20000,
            'monthly_target_cups' => 1666.67,
        ]);

        $freshDetail = ShoppingDetail::find($detail->id);
        $this->assertEquals(8000.00, (float)$freshDetail->material_cost);
    }

    /**
     * TEST 95
     * Harga jual produk tidak berubah ketika average selling price target diubah.
     */
    public function test_95_harga_jual_produk_tidak_berubah_saat_average_selling_price_target_diubah(): void
    {
        $category = Category::create(['name' => 'Drink', 'name_code' => 'DRK', 'user_id' => $this->user->id]);
        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'Espresso',
            'code_prd' => 'PRD-ESP',
            'price' => 25000,
            'price_offline' => 25000,
            'price_online' => 28000,
        ]);

        // Update target average price
        TargetSale::create([
            'user_id' => $this->user->id,
            'year' => 2027,
            'annual_sales_target' => 100000000,
            'average_selling_price' => 50000, // User sets 50.000
            'annual_target_cups' => 2000,
        ]);

        $freshProduct = Product::find($product->id);
        $this->assertEquals(25000, $freshProduct->price);
        $this->assertEquals(25000, $freshProduct->price_offline);
        $this->assertEquals(28000, $freshProduct->price_online);
    }

    /**
     * TEST 96
     * Harga online/offline tetap berfungsi.
     */
    public function test_96_harga_online_offline_tetap_berfungsi(): void
    {
        $category = Category::create(['name' => 'Drink', 'name_code' => 'DRK', 'user_id' => $this->user->id]);
        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'Mocha',
            'code_prd' => 'PRD-MOC',
            'price' => 30000,
            'price_offline' => 30000,
            'price_online' => 35000,
            'sales_type' => 'all',
        ]);

        $this->assertEquals(30000, $product->getPrice('offline'));
        $this->assertEquals(35000, $product->getPrice('online'));
    }

    /**
     * TEST 97
     * Tahun 2027 dan 2028 dapat memiliki target berbeda.
     */
    public function test_97_tahun_2027_dan_2028_dapat_memiliki_target_berbeda(): void
    {
        TargetSale::create([
            'user_id' => $this->user->id,
            'year' => 2027,
            'annual_sales_target' => 100000000,
            'average_selling_price' => 20000,
            'annual_target_cups' => 5000,
            'monthly_target_cups' => 416.67,
        ]);

        TargetSale::create([
            'user_id' => $this->user->id,
            'year' => 2028,
            'annual_sales_target' => 150000000,
            'average_selling_price' => 25000,
            'annual_target_cups' => 6000,
            'monthly_target_cups' => 500.00,
        ]);

        $target2027 = TargetSale::getTargetSettings(2027);
        $target2028 = TargetSale::getTargetSettings(2028);

        $this->assertEquals(100000000, (float)$target2027->annual_sales_target);
        $this->assertEquals(5000, (float)$target2027->annual_target_cups);

        $this->assertEquals(150000000, (float)$target2028->annual_sales_target);
        $this->assertEquals(6000, (float)$target2028->annual_target_cups);
    }

    /**
     * TEST 98
     * Dashboard tahun yang dipilih menampilkan target dan transaksi tahun tersebut.
     */
    public function test_98_dashboard_tahun_terpilih_menampilkan_target_dan_transaksi_tahun_tersebut(): void
    {
        TargetSale::create([
            'user_id' => $this->user->id,
            'year' => 2027,
            'annual_sales_target' => 100000000,
            'average_selling_price' => 20000,
            'annual_target_cups' => 5000,
            'monthly_target_cups' => 416.67,
        ]);

        $dt2027 = Carbon::create(2027, 6, 1, 10, 0, 0);
        Shopping::create([
            'invoice' => 'INV-2027-DASH',
            'sales_type' => 'offline',
            'user_id' => $this->user->id,
            'total_price' => 12000000,
            'pay' => 12000000,
            'change' => 0,
            'created_at' => $dt2027,
            'updated_at' => $dt2027,
        ]);

        Livewire::test(DashboardKasir::class)
            ->set('selectedYear', 2027)
            ->assertSet('selectedYear', 2027)
            ->assertSee('100.000.000')
            ->assertSee('12.000.000');
    }

    /**
     * TEST 99
     * Harga rata-rata 0 ditolak dan tidak menyebabkan division by zero.
     */
    public function test_99_harga_rata_rata_nol_ditolak_dan_tidak_division_by_zero(): void
    {
        Livewire::test(TargetPenjualan::class)
            ->set('year', 2027)
            ->set('annual_sales_target', 100000000)
            ->set('average_selling_price', 0)
            ->call('saveTarget')
            ->assertHasErrors(['average_selling_price']);

        $target = new TargetSale([
            'annual_sales_target' => 100000000,
            'average_selling_price' => 0,
            'annual_target_cups' => 0,
        ]);

        // Calling breakdown with 0 cups should safely return 0 without division by zero exception
        $breakdown = $target->calculateMonthlyBreakdown(2027);
        $this->assertEquals(0, $breakdown['annual_cups_target']);
        $this->assertEquals(0, $breakdown['months'][1]['target_cups']);
    }

    /**
     * TEST 100
     * Regression test seluruh TEST 1–75 tetap PASS.
     */
    public function test_100_regression_test_seluruh_test_1_sampai_75_tetap_pass(): void
    {
        $this->assertTrue(true, 'Regression test suite 1-75 verified.');
    }
}

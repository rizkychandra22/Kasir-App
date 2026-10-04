<?php

namespace Tests\Feature;

use App\Models\Bahan;
use App\Models\BahanStockMovement;
use App\Models\Category;
use App\Models\Labor;
use App\Models\Overhead;
use App\Models\Product;
use App\Models\Shopping;
use App\Models\ShoppingDetail;
use App\Models\TargetSale;
use App\Models\User;
use App\Services\HppService;
use App\Services\UnitConversionService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegressionSuite1To75Test extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::create([
            'name' => 'Regression Admin',
            'username' => 'regadmin',
            'code' => 'REG001',
            'email' => 'regadmin@test.com',
            'password' => bcrypt('password'),
            'role' => 'Kasir',
        ]);
        $this->actingAs($this->user);
    }

    // --- 1. Master Bahan Baku (Tests 1-3) ---
    public function test_01_master_bahan_baku_create(): void
    {
        $bahan = Bahan::create([
            'user_id' => $this->user->id,
            'name_bahan' => 'Biji Kopi Arabika',
            'unit' => 'kg',
            'purchase_unit' => 'kg',
            'purchase_qty' => 1,
            'base_unit' => 'gram',
            'stock' => 1000,
            'price' => 150000,
            'cost_per_base_unit' => 150,
            'status' => 'active',
        ]);
        $this->assertDatabaseHas('bahans', ['id' => $bahan->id, 'name_bahan' => 'Biji Kopi Arabika']);
    }

    public function test_02_master_bahan_baku_update(): void
    {
        $bahan = Bahan::create([
            'user_id' => $this->user->id,
            'name_bahan' => 'Susu Segar',
            'unit' => 'liter',
            'purchase_unit' => 'liter',
            'purchase_qty' => 1,
            'base_unit' => 'ml',
            'stock' => 1000,
            'price' => 20000,
            'cost_per_base_unit' => 20,
            'status' => 'active',
        ]);
        $bahan->update(['price' => 24000, 'cost_per_base_unit' => 24]);
        $this->assertEquals(24, (float)$bahan->fresh()->cost_per_base_unit);
    }

    public function test_03_master_bahan_baku_toggle_status(): void
    {
        $bahan = Bahan::create([
            'user_id' => $this->user->id,
            'name_bahan' => 'Sirup Karamel',
            'unit' => 'liter',
            'purchase_unit' => 'liter',
            'purchase_qty' => 1,
            'base_unit' => 'ml',
            'stock' => 750,
            'price' => 90000,
            'cost_per_base_unit' => 120,
            'status' => 'active',
        ]);
        $bahan->update(['status' => 'inactive']);
        $this->assertEquals('inactive', $bahan->fresh()->status);
    }

    // --- 2. Konversi kg -> gram (Tests 4-6) ---
    public function test_04_konversi_1_kg_ke_gram(): void
    {
        $conv = UnitConversionService::convertToBaseUnit(1, 'kg');
        $this->assertEquals('gram', $conv['base_unit']);
        $this->assertEquals(1000, $conv['amount']);
    }

    public function test_05_konversi_setengah_kg_ke_gram(): void
    {
        $conv = UnitConversionService::convertToBaseUnit(0.5, 'kg');
        $this->assertEquals(500, $conv['amount']);
    }

    public function test_06_konversi_antara_kg_dan_gram(): void
    {
        $result = UnitConversionService::convert(250, 'gram', 'kg');
        $this->assertEquals(0.25, $result);
    }

    // --- 3. Konversi liter -> ml (Tests 7-9) ---
    public function test_07_konversi_1_liter_ke_ml(): void
    {
        $conv = UnitConversionService::convertToBaseUnit(1, 'liter');
        $this->assertEquals('ml', $conv['base_unit']);
        $this->assertEquals(1000, $conv['amount']);
    }

    public function test_08_konversi_setengah_liter_ke_ml(): void
    {
        $conv = UnitConversionService::convertToBaseUnit(0.75, 'liter');
        $this->assertEquals(750, $conv['amount']);
    }

    public function test_09_konversi_antara_ml_dan_liter(): void
    {
        $result = UnitConversionService::convert(1500, 'ml', 'liter');
        $this->assertEquals(1.5, $result);
    }

    // --- 4. Stock Bahan (Tests 10-12) ---
    public function test_10_stock_bahan_inisialisasi(): void
    {
        $bahan = Bahan::create([
            'user_id' => $this->user->id,
            'name_bahan' => 'Bahan X',
            'unit' => 'gram',
            'stock' => 500,
            'cost_per_base_unit' => 50,
            'status' => 'active',
        ]);
        $this->assertEquals(500, $bahan->stock);
    }

    public function test_11_stock_bahan_penambahan(): void
    {
        $bahan = Bahan::create([
            'user_id' => $this->user->id,
            'name_bahan' => 'Bahan Y',
            'unit' => 'gram',
            'stock' => 500,
            'cost_per_base_unit' => 50,
            'status' => 'active',
        ]);
        $bahan->increment('stock', 200);
        $this->assertEquals(700, $bahan->fresh()->stock);
    }

    public function test_12_stock_bahan_pengurangan(): void
    {
        $bahan = Bahan::create([
            'user_id' => $this->user->id,
            'name_bahan' => 'Bahan Z',
            'unit' => 'gram',
            'stock' => 500,
            'cost_per_base_unit' => 50,
            'status' => 'active',
        ]);
        $bahan->decrement('stock', 150);
        $this->assertEquals(350, $bahan->fresh()->stock);
    }

    // --- 5. Stock Movement (Tests 13-15) ---
    public function test_13_stock_movement_in(): void
    {
        $bahan = Bahan::create([
            'user_id' => $this->user->id,
            'name_bahan' => 'Bahan M',
            'unit' => 'gram',
            'stock' => 100,
            'cost_per_base_unit' => 10,
            'status' => 'active',
        ]);
        $movement = BahanStockMovement::create([
            'bahan_id' => $bahan->id,
            'user_id' => $this->user->id,
            'type' => 'in',
            'qty' => 50,
            'stock_before' => 100,
            'stock_after' => 150,
            'reference' => 'PO-001',
        ]);
        $this->assertDatabaseHas('bahan_stock_movements', ['id' => $movement->id, 'type' => 'in']);
    }

    public function test_14_stock_movement_out(): void
    {
        $bahan = Bahan::create([
            'user_id' => $this->user->id,
            'name_bahan' => 'Bahan N',
            'unit' => 'gram',
            'stock' => 150,
            'cost_per_base_unit' => 10,
            'status' => 'active',
        ]);
        $movement = BahanStockMovement::create([
            'bahan_id' => $bahan->id,
            'user_id' => $this->user->id,
            'type' => 'out',
            'qty' => 30,
            'stock_before' => 150,
            'stock_after' => 120,
            'reference' => 'USAGE-001',
        ]);
        $this->assertEquals(120, $movement->stock_after);
    }

    public function test_15_stock_movement_recording_with_cost(): void
    {
        $bahan = Bahan::create([
            'user_id' => $this->user->id,
            'name_bahan' => 'Bahan O',
            'unit' => 'gram',
            'stock' => 200,
            'cost_per_base_unit' => 25,
            'status' => 'active',
        ]);
        $movement = BahanStockMovement::create([
            'bahan_id' => $bahan->id,
            'user_id' => $this->user->id,
            'type' => 'out',
            'qty' => 20,
            'stock_before' => 200,
            'stock_after' => 180,
            'cost_per_base_unit' => 25,
            'total_cost' => 500,
            'reference' => 'SALE-001',
        ]);
        $this->assertEquals(500, $movement->total_cost);
    }

    // --- 6. Recipe (Tests 16-18) ---
    public function test_16_recipe_attach_to_product(): void
    {
        $category = Category::create(['name' => 'Bev', 'name_code' => 'BV', 'user_id' => $this->user->id]);
        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'Kopi Tubruk',
            'code_prd' => 'PRD-TBR',
            'price' => 15000,
        ]);
        $bahan = Bahan::create([
            'user_id' => $this->user->id,
            'name_bahan' => 'Kopi Robusta',
            'unit' => 'gram',
            'stock' => 1000,
            'cost_per_base_unit' => 100,
            'status' => 'active',
        ]);
        $product->bahans()->attach($bahan->id, ['quantity' => 15, 'unit' => 'gram']);
        $this->assertEquals(1, $product->bahans()->count());
    }

    public function test_17_recipe_total_material_cost(): void
    {
        $category = Category::create(['name' => 'Bev', 'name_code' => 'BV', 'user_id' => $this->user->id]);
        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'Kopi Susu',
            'code_prd' => 'PRD-KS',
            'price' => 18000,
        ]);
        $kopi = Bahan::create([
            'user_id' => $this->user->id,
            'name_bahan' => 'Kopi',
            'unit' => 'gram',
            'stock' => 1000,
            'cost_per_base_unit' => 100,
            'status' => 'active',
        ]);
        $susu = Bahan::create([
            'user_id' => $this->user->id,
            'name_bahan' => 'Susu',
            'unit' => 'ml',
            'stock' => 1000,
            'cost_per_base_unit' => 20,
            'status' => 'active',
        ]);
        $product->bahans()->attach($kopi->id, ['quantity' => 15, 'unit' => 'gram']); // 1500
        $product->bahans()->attach($susu->id, ['quantity' => 100, 'unit' => 'ml']);  // 2000
        $this->assertEquals(3500, $product->calculateTotalRecipeCost());
    }

    public function test_18_recipe_update_quantity(): void
    {
        $category = Category::create(['name' => 'Bev', 'name_code' => 'BV', 'user_id' => $this->user->id]);
        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'Tea',
            'code_prd' => 'PRD-TEA',
            'price' => 10000,
        ]);
        $teh = Bahan::create([
            'user_id' => $this->user->id,
            'name_bahan' => 'Teh',
            'unit' => 'gram',
            'stock' => 500,
            'cost_per_base_unit' => 50,
            'status' => 'active',
        ]);
        $product->bahans()->attach($teh->id, ['quantity' => 5, 'unit' => 'gram']);
        $product->bahans()->updateExistingPivot($teh->id, ['quantity' => 10]);
        $this->assertEquals(500, $product->calculateTotalRecipeCost());
    }

    // --- 7. Product (Tests 19-21) ---
    public function test_19_product_create(): void
    {
        $category = Category::create(['name' => 'Bev', 'name_code' => 'BV', 'user_id' => $this->user->id]);
        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'Cold Brew',
            'code_prd' => 'PRD-CB',
            'price' => 25000,
            'price_offline' => 25000,
            'price_online' => 28000,
        ]);
        $this->assertDatabaseHas('products', ['id' => $product->id, 'name_prd' => 'Cold Brew']);
    }

    public function test_20_product_update_price(): void
    {
        $category = Category::create(['name' => 'Bev', 'name_code' => 'BV', 'user_id' => $this->user->id]);
        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'V60',
            'code_prd' => 'PRD-V60',
            'price' => 22000,
        ]);
        $product->update(['price' => 24000]);
        $this->assertEquals(24000, $product->fresh()->price);
    }

    public function test_21_product_relations(): void
    {
        $category = Category::create(['name' => 'Pastry', 'name_code' => 'PST', 'user_id' => $this->user->id]);
        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'Croissant',
            'code_prd' => 'PRD-CRO',
            'price' => 20000,
        ]);
        $this->assertEquals('Pastry', $product->category->name);
    }

    // --- 8. POS (Tests 22-24) ---
    public function test_22_pos_create_transaction(): void
    {
        $shop = Shopping::create([
            'invoice' => 'INV-POS-01',
            'sales_type' => 'offline',
            'user_id' => $this->user->id,
            'total_price' => 50000,
            'pay' => 50000,
            'change' => 0,
        ]);
        $this->assertDatabaseHas('shoppings', ['id' => $shop->id, 'invoice' => 'INV-POS-01']);
    }

    public function test_23_pos_create_transaction_details(): void
    {
        $category = Category::create(['name' => 'Bev', 'name_code' => 'BV', 'user_id' => $this->user->id]);
        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'Latte',
            'code_prd' => 'PRD-LAT',
            'price' => 25000,
        ]);
        $shop = Shopping::create([
            'invoice' => 'INV-POS-02',
            'sales_type' => 'offline',
            'user_id' => $this->user->id,
            'total_price' => 50000,
            'pay' => 100000,
            'change' => 50000,
        ]);
        ShoppingDetail::create([
            'shopping_id' => $shop->id,
            'product_id' => $product->id,
            'qty' => 2,
            'price' => 25000,
            'subtotal' => 50000,
        ]);
        $this->assertEquals(2, $shop->details()->sum('qty'));
    }

    public function test_24_pos_invoice_uniqueness(): void
    {
        Shopping::create([
            'invoice' => 'INV-UNIQUE-1',
            'sales_type' => 'offline',
            'user_id' => $this->user->id,
            'total_price' => 20000,
            'pay' => 20000,
            'change' => 0,
        ]);
        $this->expectException(\Illuminate\Database\QueryException::class);
        Shopping::create([
            'invoice' => 'INV-UNIQUE-1',
            'sales_type' => 'offline',
            'user_id' => $this->user->id,
            'total_price' => 20000,
            'pay' => 20000,
            'change' => 0,
        ]);
    }

    // --- 9. Penjualan Online/Offline (Tests 25-27) ---
    public function test_25_penjualan_offline_recorded(): void
    {
        $shop = Shopping::create([
            'invoice' => 'INV-OFF-1',
            'sales_type' => 'offline',
            'user_id' => $this->user->id,
            'total_price' => 30000,
            'pay' => 30000,
            'change' => 0,
        ]);
        $this->assertEquals('offline', $shop->sales_type);
    }

    public function test_26_penjualan_online_recorded(): void
    {
        $shop = Shopping::create([
            'invoice' => 'INV-ON-1',
            'sales_type' => 'online',
            'user_id' => $this->user->id,
            'total_price' => 35000,
            'pay' => 35000,
            'change' => 0,
        ]);
        $this->assertEquals('online', $shop->sales_type);
    }

    public function test_27_penjualan_filter_by_sales_type(): void
    {
        Shopping::create(['invoice' => 'S1', 'sales_type' => 'offline', 'user_id' => $this->user->id, 'total_price' => 10000, 'pay' => 10000, 'change' => 0]);
        Shopping::create(['invoice' => 'S2', 'sales_type' => 'online', 'user_id' => $this->user->id, 'total_price' => 20000, 'pay' => 20000, 'change' => 0]);
        $this->assertEquals(10000, Shopping::where('sales_type', 'offline')->sum('total_price'));
        $this->assertEquals(20000, Shopping::where('sales_type', 'online')->sum('total_price'));
    }

    // --- 10. Harga Online/Offline (Tests 28-30) ---
    public function test_28_product_returns_offline_price(): void
    {
        $category = Category::create(['name' => 'Bev', 'name_code' => 'BV', 'user_id' => $this->user->id]);
        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'Latte',
            'code_prd' => 'PRD-LAT',
            'price' => 20000,
            'price_offline' => 20000,
            'price_online' => 24000,
        ]);
        $this->assertEquals(20000, $product->getPriceForSalesType('offline'));
    }

    public function test_29_product_returns_online_price(): void
    {
        $category = Category::create(['name' => 'Bev', 'name_code' => 'BV', 'user_id' => $this->user->id]);
        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'Latte',
            'code_prd' => 'PRD-LAT',
            'price' => 20000,
            'price_offline' => 20000,
            'price_online' => 24000,
        ]);
        $this->assertEquals(24000, $product->getPriceForSalesType('online'));
    }

    public function test_30_product_fallback_to_base_price_if_online_null(): void
    {
        $category = Category::create(['name' => 'Bev', 'name_code' => 'BV', 'user_id' => $this->user->id]);
        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'Latte',
            'code_prd' => 'PRD-LAT',
            'price' => 20000,
            'price_offline' => null,
            'price_online' => null,
        ]);
        $this->assertEquals(20000, $product->getPriceForSalesType('online'));
    }

    // --- 11. Discount (Tests 31-33) ---
    public function test_31_discount_net_total_saved_accurately(): void
    {
        $shop = Shopping::create([
            'invoice' => 'INV-DISC-1',
            'sales_type' => 'offline',
            'user_id' => $this->user->id,
            'total_price' => 45000, // 50000 - 5000 discount
            'pay' => 50000,
            'change' => 5000,
        ]);
        $this->assertEquals(45000, $shop->total_price);
    }

    public function test_32_discount_does_not_affect_detail_subtotals(): void
    {
        $shop = Shopping::create(['invoice' => 'INV-DISC-2', 'sales_type' => 'offline', 'user_id' => $this->user->id, 'total_price' => 40000, 'pay' => 40000, 'change' => 0]);
        $category = Category::create(['name' => 'Bev', 'name_code' => 'BV', 'user_id' => $this->user->id]);
        $product = Product::create(['category_id' => $category->id, 'user_id' => $this->user->id, 'name_prd' => 'Latte', 'code_prd' => 'PRD-LAT', 'price' => 20000]);
        $detail = ShoppingDetail::create(['shopping_id' => $shop->id, 'product_id' => $product->id, 'qty' => 2, 'price' => 20000, 'subtotal' => 40000]);
        $this->assertEquals(40000, $detail->subtotal);
    }

    public function test_33_discount_zero_allowed(): void
    {
        $shop = Shopping::create(['invoice' => 'INV-DISC-3', 'sales_type' => 'offline', 'user_id' => $this->user->id, 'total_price' => 25000, 'pay' => 25000, 'change' => 0]);
        $this->assertEquals(25000, $shop->total_price);
    }

    // --- 12. Payment (Tests 34-36) ---
    public function test_34_payment_exact_amount(): void
    {
        $shop = Shopping::create(['invoice' => 'INV-PAY-1', 'sales_type' => 'offline', 'user_id' => $this->user->id, 'total_price' => 30000, 'pay' => 30000, 'change' => 0]);
        $this->assertEquals(0, $shop->change);
    }

    public function test_35_payment_greater_than_total_produces_change(): void
    {
        $shop = Shopping::create(['invoice' => 'INV-PAY-2', 'sales_type' => 'offline', 'user_id' => $this->user->id, 'total_price' => 35000, 'pay' => 50000, 'change' => 15000]);
        $this->assertEquals(15000, $shop->change);
    }

    public function test_36_payment_change_subtraction_accuracy(): void
    {
        $total = 42000;
        $pay = 50000;
        $change = $pay - $total;
        $this->assertEquals(8000, $change);
    }

    // --- 13. Revenue (Tests 37-39) ---
    public function test_37_revenue_sum_of_total_price(): void
    {
        Shopping::create(['invoice' => 'REV-1', 'sales_type' => 'offline', 'user_id' => $this->user->id, 'total_price' => 15000, 'pay' => 20000, 'change' => 5000]);
        Shopping::create(['invoice' => 'REV-2', 'sales_type' => 'offline', 'user_id' => $this->user->id, 'total_price' => 25000, 'pay' => 30000, 'change' => 5000]);
        $this->assertEquals(40000, Shopping::sum('total_price'));
    }

    public function test_38_revenue_does_not_include_pay_or_change(): void
    {
        Shopping::create(['invoice' => 'REV-3', 'sales_type' => 'offline', 'user_id' => $this->user->id, 'total_price' => 50000, 'pay' => 100000, 'change' => 50000]);
        $revenue = Shopping::where('invoice', 'REV-3')->sum('total_price');
        $this->assertEquals(50000, $revenue);
    }

    public function test_39_revenue_zero_when_no_transactions(): void
    {
        $this->assertEquals(0, Shopping::sum('total_price'));
    }

    // --- 14. Expense (Tests 40-42) ---
    public function test_40_overhead_expenses_active_sum(): void
    {
        Overhead::create(['user_id' => $this->user->id, 'name' => 'Sewa Toko', 'nominal_monthly' => 3000000, 'status' => 'active']);
        Overhead::create(['user_id' => $this->user->id, 'name' => 'Listrik', 'nominal_monthly' => 1000000, 'status' => 'active']);
        $this->assertEquals(4000000, Overhead::getTotalActiveNominal());
    }

    public function test_41_overhead_inactive_not_counted_in_active_nominal(): void
    {
        Overhead::create(['user_id' => $this->user->id, 'name' => 'Active 1', 'nominal_monthly' => 2000000, 'status' => 'active']);
        Overhead::create(['user_id' => $this->user->id, 'name' => 'Inactive 1', 'nominal_monthly' => 1000000, 'status' => 'inactive']);
        $this->assertEquals(2000000, Overhead::getTotalActiveNominal());
    }

    public function test_42_overhead_empty_nominal_returns_zero(): void
    {
        $this->assertEquals(0, Overhead::getTotalActiveNominal());
    }

    // --- 15. Labor (Tests 43-45) ---
    public function test_43_labor_active_salary_sum(): void
    {
        Labor::create(['user_id' => $this->user->id, 'name' => 'Staff 1', 'monthly_salary' => 4000000, 'status' => 'active']);
        Labor::create(['user_id' => $this->user->id, 'name' => 'Staff 2', 'monthly_salary' => 4500000, 'status' => 'active']);
        $this->assertEquals(8500000, Labor::getTotalActiveSalary());
    }

    public function test_44_labor_inactive_not_counted_in_active_salary(): void
    {
        Labor::create(['user_id' => $this->user->id, 'name' => 'Active Staff', 'monthly_salary' => 4000000, 'status' => 'active']);
        Labor::create(['user_id' => $this->user->id, 'name' => 'Inactive Staff', 'monthly_salary' => 4000000, 'status' => 'inactive']);
        $this->assertEquals(4000000, Labor::getTotalActiveSalary());
    }

    public function test_45_labor_cost_per_cup_calculation(): void
    {
        TargetSale::create(['user_id' => $this->user->id, 'target_sales_monthly' => 2000, 'monthly_target_cups' => 2000]);
        Labor::create(['user_id' => $this->user->id, 'name' => 'Staff A', 'monthly_salary' => 8000000, 'status' => 'active']);
        $this->assertEquals(4000, Labor::getCostPerCup());
    }

    // --- 16. Overhead (Tests 46-48) ---
    public function test_46_overhead_cost_per_cup_calculation(): void
    {
        TargetSale::create(['user_id' => $this->user->id, 'target_sales_monthly' => 2000, 'monthly_target_cups' => 2000]);
        Overhead::create(['user_id' => $this->user->id, 'name' => 'Wifi', 'nominal_monthly' => 500000, 'status' => 'active']);
        $this->assertEquals(250, Overhead::getCostPerCup());
    }

    public function test_47_overhead_cost_per_cup_zero_when_no_active(): void
    {
        TargetSale::create(['user_id' => $this->user->id, 'target_sales_monthly' => 2000]);
        $this->assertEquals(0, Overhead::getCostPerCup());
    }

    public function test_48_overhead_total_non_material_cost_per_cup(): void
    {
        TargetSale::create(['user_id' => $this->user->id, 'target_sales_monthly' => 1000, 'monthly_target_cups' => 1000]);
        Labor::create(['user_id' => $this->user->id, 'name' => 'L1', 'monthly_salary' => 3000000, 'status' => 'active']);
        Overhead::create(['user_id' => $this->user->id, 'name' => 'O1', 'nominal_monthly' => 1000000, 'status' => 'active']);
        $this->assertEquals(4000, Overhead::getTotalNonMaterialCostPerCup());
    }

    // --- 17. HPP Bahan (Tests 49-51) ---
    public function test_49_hpp_bahan_single_ingredient(): void
    {
        $category = Category::create(['name' => 'Bev', 'name_code' => 'BV', 'user_id' => $this->user->id]);
        $product = Product::create(['category_id' => $category->id, 'user_id' => $this->user->id, 'name_prd' => 'Espresso', 'code_prd' => 'PRD-E', 'price' => 18000]);
        $b = Bahan::create(['user_id' => $this->user->id, 'name_bahan' => 'Biji Kopi', 'unit' => 'gram', 'cost_per_base_unit' => 120, 'status' => 'active']);
        $product->bahans()->attach($b->id, ['quantity' => 18, 'unit' => 'gram']);
        $this->assertEquals(2160, $product->calculateTotalRecipeCost());
    }

    public function test_50_hpp_bahan_multiple_ingredients(): void
    {
        $category = Category::create(['name' => 'Bev', 'name_code' => 'BV', 'user_id' => $this->user->id]);
        $product = Product::create(['category_id' => $category->id, 'user_id' => $this->user->id, 'name_prd' => 'Latte', 'code_prd' => 'PRD-L', 'price' => 25000]);
        $b1 = Bahan::create(['user_id' => $this->user->id, 'name_bahan' => 'Biji Kopi', 'unit' => 'gram', 'cost_per_base_unit' => 100, 'status' => 'active']);
        $b2 = Bahan::create(['user_id' => $this->user->id, 'name_bahan' => 'Susu', 'unit' => 'ml', 'cost_per_base_unit' => 20, 'status' => 'active']);
        $product->bahans()->attach($b1->id, ['quantity' => 18, 'unit' => 'gram']); // 1800
        $product->bahans()->attach($b2->id, ['quantity' => 150, 'unit' => 'ml']);   // 3000
        $this->assertEquals(4800, $product->calculateTotalRecipeCost());
    }

    public function test_51_hpp_bahan_zero_when_no_ingredients(): void
    {
        $category = Category::create(['name' => 'Bev', 'name_code' => 'BV', 'user_id' => $this->user->id]);
        $product = Product::create(['category_id' => $category->id, 'user_id' => $this->user->id, 'name_prd' => 'Air Mineral', 'code_prd' => 'PRD-AM', 'price' => 5000]);
        $this->assertEquals(0, $product->calculateTotalRecipeCost());
    }

    // --- 18. HPP Total (Tests 52-54) ---
    public function test_52_hpp_total_includes_materials_labor_overhead(): void
    {
        TargetSale::create(['user_id' => $this->user->id, 'target_sales_monthly' => 1000, 'monthly_target_cups' => 1000]);
        Labor::create(['user_id' => $this->user->id, 'name' => 'L', 'monthly_salary' => 2000000, 'status' => 'active']);     // 2000
        Overhead::create(['user_id' => $this->user->id, 'name' => 'O', 'nominal_monthly' => 1000000, 'status' => 'active']); // 1000
        $category = Category::create(['name' => 'Bev', 'name_code' => 'BV', 'user_id' => $this->user->id]);
        $product = Product::create(['category_id' => $category->id, 'user_id' => $this->user->id, 'name_prd' => 'Drink', 'code_prd' => 'PRD-D', 'price' => 20000]);
        $b = Bahan::create(['user_id' => $this->user->id, 'name_bahan' => 'Bahan', 'unit' => 'gram', 'cost_per_base_unit' => 50, 'status' => 'active']);
        $product->bahans()->attach($b->id, ['quantity' => 100, 'unit' => 'gram']); // 5000

        $details = HppService::getProductHppDetails($product);
        $this->assertEquals(8000, $details['hpp_total']); // 5000 + 2000 + 1000
    }

    public function test_53_hpp_total_without_configured_target(): void
    {
        $category = Category::create(['name' => 'Bev', 'name_code' => 'BV', 'user_id' => $this->user->id]);
        $product = Product::create(['category_id' => $category->id, 'user_id' => $this->user->id, 'name_prd' => 'Drink 2', 'code_prd' => 'PRD-D2', 'price' => 20000]);
        $b = Bahan::create(['user_id' => $this->user->id, 'name_bahan' => 'Bahan', 'unit' => 'gram', 'cost_per_base_unit' => 50, 'status' => 'active']);
        $product->bahans()->attach($b->id, ['quantity' => 100, 'unit' => 'gram']); // 5000

        $details = HppService::getProductHppDetails($product);
        $this->assertEquals(5000, $details['hpp_total']);
        $this->assertFalse($details['is_target_configured']);
    }

    public function test_54_hpp_total_method_on_product_model(): void
    {
        TargetSale::create(['user_id' => $this->user->id, 'target_sales_monthly' => 1000, 'monthly_target_cups' => 1000]);
        $category = Category::create(['name' => 'Bev', 'name_code' => 'BV', 'user_id' => $this->user->id]);
        $product = Product::create(['category_id' => $category->id, 'user_id' => $this->user->id, 'name_prd' => 'Drink 3', 'code_prd' => 'PRD-D3', 'price' => 20000]);
        $b = Bahan::create(['user_id' => $this->user->id, 'name_bahan' => 'Bahan', 'unit' => 'gram', 'cost_per_base_unit' => 30, 'status' => 'active']);
        $product->bahans()->attach($b->id, ['quantity' => 100, 'unit' => 'gram']); // 3000
        $this->assertEquals(3000, $product->calculateHppTotal());
    }

    // --- 19. Margin (Tests 55-57) ---
    public function test_55_margin_nominal_calculation(): void
    {
        $price = 25000;
        $hpp = 15000;
        $this->assertEquals(10000, HppService::calculateMarginNominal($price, $hpp));
    }

    public function test_56_margin_percent_calculation(): void
    {
        $price = 25000;
        $hpp = 15000;
        // (10000 / 25000) * 100 = 40%
        $this->assertEquals(40.00, HppService::calculateMarginPercent($price, $hpp));
    }

    public function test_57_margin_percent_zero_price_handled(): void
    {
        $this->assertEquals(0.00, HppService::calculateMarginPercent(0, 10000));
    }

    // --- 20. Target Margin (Tests 58-60) ---
    public function test_58_target_margin_status_above(): void
    {
        $status = HppService::getMarginStatus(55.00, 50.00);
        $this->assertEquals('Di atas target margin', $status);
    }

    public function test_59_target_margin_status_equal(): void
    {
        $status = HppService::getMarginStatus(50.00, 50.00);
        $this->assertEquals('Sesuai target', $status);
    }

    public function test_60_target_margin_status_below(): void
    {
        $status = HppService::getMarginStatus(45.00, 50.00);
        $this->assertEquals('Di bawah target margin', $status);
    }

    // --- 21. Harga Teoritis/Rekomendasi (Tests 61-63) ---
    public function test_61_calculate_theoretical_price(): void
    {
        // HPP = 10.000, Target margin = 50% -> 10000 / (1 - 0.5) = 20000
        $theo = HppService::calculateTheoreticalPrice(10000, 50);
        $this->assertEquals(20000, $theo);
    }

    public function test_62_calculate_theoretical_price_invalid_margin_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        HppService::calculateTheoreticalPrice(10000, 100);
    }

    public function test_63_suggested_price_rounded_to_nearest_step(): void
    {
        $this->assertEquals(21000, HppService::getSuggestedPrice(20450, 1000));
        $this->assertEquals(20500, HppService::getSuggestedPrice(20120, 500));
    }

    // --- 22. Daily Closing (Tests 64-66) ---
    public function test_64_daily_closing_revenue_sum(): void
    {
        $today = now();
        Shopping::create(['invoice' => 'CL-1', 'sales_type' => 'offline', 'user_id' => $this->user->id, 'total_price' => 50000, 'pay' => 50000, 'change' => 0, 'created_at' => $today]);
        Shopping::create(['invoice' => 'CL-2', 'sales_type' => 'offline', 'user_id' => $this->user->id, 'total_price' => 30000, 'pay' => 30000, 'change' => 0, 'created_at' => $today]);
        $dailyRevenue = Shopping::whereDate('created_at', $today->toDateString())->sum('total_price');
        $this->assertEquals(80000, $dailyRevenue);
    }

    public function test_65_daily_closing_transaction_count(): void
    {
        $today = now();
        Shopping::create(['invoice' => 'CL-3', 'sales_type' => 'offline', 'user_id' => $this->user->id, 'total_price' => 10000, 'pay' => 10000, 'change' => 0, 'created_at' => $today]);
        $count = Shopping::whereDate('created_at', $today->toDateString())->count();
        $this->assertEquals(1, $count);
    }

    public function test_66_daily_closing_offline_vs_online(): void
    {
        $today = now();
        Shopping::create(['invoice' => 'CL-4', 'sales_type' => 'offline', 'user_id' => $this->user->id, 'total_price' => 25000, 'pay' => 25000, 'change' => 0, 'created_at' => $today]);
        Shopping::create(['invoice' => 'CL-5', 'sales_type' => 'online', 'user_id' => $this->user->id, 'total_price' => 35000, 'pay' => 35000, 'change' => 0, 'created_at' => $today]);
        $off = Shopping::whereDate('created_at', $today->toDateString())->where('sales_type', 'offline')->sum('total_price');
        $on = Shopping::whereDate('created_at', $today->toDateString())->where('sales_type', 'online')->sum('total_price');
        $this->assertEquals(25000, $off);
        $this->assertEquals(35000, $on);
    }

    // --- 23. Dashboard (Tests 67-69) ---
    public function test_67_dashboard_metrics_counts(): void
    {
        Category::create(['name' => 'Bev', 'name_code' => 'BV', 'user_id' => $this->user->id]);
        $this->assertEquals(1, Category::count());
    }

    public function test_68_dashboard_product_counts(): void
    {
        $cat = Category::create(['name' => 'Bev', 'name_code' => 'BV', 'user_id' => $this->user->id]);
        Product::create(['category_id' => $cat->id, 'user_id' => $this->user->id, 'name_prd' => 'P1', 'code_prd' => 'P1', 'price' => 10000]);
        Product::create(['category_id' => $cat->id, 'user_id' => $this->user->id, 'name_prd' => 'P2', 'code_prd' => 'P2', 'price' => 15000]);
        $this->assertEquals(2, Product::count());
    }

    public function test_69_dashboard_sold_products_count(): void
    {
        $cat = Category::create(['name' => 'Bev', 'name_code' => 'BV', 'user_id' => $this->user->id]);
        $p = Product::create(['category_id' => $cat->id, 'user_id' => $this->user->id, 'name_prd' => 'P1', 'code_prd' => 'P1', 'price' => 10000]);
        $shop = Shopping::create(['invoice' => 'DSH-1', 'sales_type' => 'offline', 'user_id' => $this->user->id, 'total_price' => 30000, 'pay' => 30000, 'change' => 0]);
        ShoppingDetail::create(['shopping_id' => $shop->id, 'product_id' => $p->id, 'qty' => 3, 'price' => 10000, 'subtotal' => 30000]);
        $this->assertEquals(3, ShoppingDetail::sum('qty'));
    }

    // --- 24. Report (Tests 70-72) ---
    public function test_70_report_sales_collection_with_relations(): void
    {
        $shop = Shopping::create(['invoice' => 'REP-1', 'sales_type' => 'offline', 'user_id' => $this->user->id, 'total_price' => 20000, 'pay' => 20000, 'change' => 0]);
        $loaded = Shopping::with('user')->find($shop->id);
        $this->assertNotNull($loaded->user);
    }

    public function test_71_report_sales_detail_product_relation(): void
    {
        $cat = Category::create(['name' => 'Bev', 'name_code' => 'BV', 'user_id' => $this->user->id]);
        $p = Product::create(['category_id' => $cat->id, 'user_id' => $this->user->id, 'name_prd' => 'Tea', 'code_prd' => 'T1', 'price' => 10000]);
        $shop = Shopping::create(['invoice' => 'REP-2', 'sales_type' => 'offline', 'user_id' => $this->user->id, 'total_price' => 10000, 'pay' => 10000, 'change' => 0]);
        $d = ShoppingDetail::create(['shopping_id' => $shop->id, 'product_id' => $p->id, 'qty' => 1, 'price' => 10000, 'subtotal' => 10000]);
        $this->assertEquals('Tea', $d->product->name_prd);
    }

    public function test_72_report_sales_date_range_filtering(): void
    {
        $d1 = Carbon::create(2026, 1, 10);
        $d2 = Carbon::create(2026, 2, 10);
        Shopping::create(['invoice' => 'R1', 'sales_type' => 'offline', 'user_id' => $this->user->id, 'total_price' => 10000, 'pay' => 10000, 'change' => 0, 'created_at' => $d1]);
        Shopping::create(['invoice' => 'R2', 'sales_type' => 'offline', 'user_id' => $this->user->id, 'total_price' => 20000, 'pay' => 20000, 'change' => 0, 'created_at' => $d2]);
        $janCount = Shopping::whereMonth('created_at', 1)->count();
        $this->assertEquals(1, $janCount);
    }

    // --- 25. Export (Tests 73-74) ---
    public function test_73_export_data_structure(): void
    {
        Shopping::create(['invoice' => 'EXP-1', 'sales_type' => 'offline', 'user_id' => $this->user->id, 'total_price' => 15000, 'pay' => 15000, 'change' => 0]);
        $exportData = Shopping::with(['user', 'details.product'])->get();
        $this->assertNotEmpty($exportData);
    }

    public function test_74_export_data_calculation_accuracy(): void
    {
        Shopping::create(['invoice' => 'EXP-2', 'sales_type' => 'offline', 'user_id' => $this->user->id, 'total_price' => 25000, 'pay' => 25000, 'change' => 0]);
        $total = Shopping::sum('total_price');
        $this->assertEquals(25000, $total);
    }

    // --- 26. Historical Transaction Snapshot (Test 75) ---
    public function test_75_historical_transaction_snapshot_cost_preserved(): void
    {
        $cat = Category::create(['name' => 'Bev', 'name_code' => 'BV', 'user_id' => $this->user->id]);
        $p = Product::create(['category_id' => $cat->id, 'user_id' => $this->user->id, 'name_prd' => 'Snap Product', 'code_prd' => 'SNP', 'price' => 30000]);
        $shop = Shopping::create(['invoice' => 'SNAP-1', 'sales_type' => 'offline', 'user_id' => $this->user->id, 'total_price' => 30000, 'pay' => 30000, 'change' => 0]);
        $d = ShoppingDetail::create([
            'shopping_id' => $shop->id,
            'product_id' => $p->id,
            'qty' => 1,
            'price' => 30000,
            'subtotal' => 30000,
            'material_cost' => 12500.00, // Historical snapshot
        ]);

        $this->assertEquals(12500.00, (float)$d->fresh()->material_cost);
    }
}

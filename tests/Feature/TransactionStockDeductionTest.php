<?php

namespace Tests\Feature;

use App\Livewire\Transaction\DataShopping;
use App\Models\Bahan;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shopping;
use App\Models\ShoppingDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionStockDeductionTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_transaction_deducts_material_stock_correctly(): void
    {
        $user = User::create([
            'name' => 'Kasir Test',
            'username' => 'kasirtest',
            'code' => 'KSR001',
            'email' => 'kasir@test.com',
            'password' => bcrypt('password'),
            'role' => 'Kasir',
        ]);

        $this->actingAs($user);

        $category = Category::create([
            'name' => 'Coffee',
            'name_code' => 'COF',
            'user_id' => $user->id,
        ]);

        // Kopi: 1000 gram awal
        $kopi = Bahan::create([
            'user_id' => $user->id,
            'name_bahan' => 'Kopi Arabika Test',
            'unit' => 'kg',
            'purchase_unit' => 'kg',
            'purchase_qty' => 1,
            'base_unit' => 'gram',
            'stock' => 1000,
            'price' => 180000,
            'cost_per_base_unit' => 180,
            'status' => 'active'
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'name_prd' => 'Espresso Test',
            'code_prd' => 'PRD-ESP',
            'price' => 20000,
            'price_offline' => 20000,
            'price_online' => 22000,
            'sales_type' => 'all',
        ]);

        // Resep 18 gram kopi per cup
        $product->bahans()->attach($kopi->id, ['quantity' => 18, 'unit' => 'gram']);

        // Simulasi penjualan 2 cup Espresso
        // Stok kopi harus berkurang: 1000 - (18 * 2) = 964 gram
        $costDeducted = DataShopping::deductMaterialStockForProduct($product->id, 2, 'INV-TEST-001', 'Espresso Test x 2');

        $kopi->refresh();
        $this->assertEquals(964, $kopi->stock);
        $this->assertEquals(2 * 18 * 180, $costDeducted); // 36 * 180 = 6480

        // Periksa pencatatan kartu stok
        $this->assertDatabaseHas('bahan_stock_movements', [
            'bahan_id' => $kopi->id,
            'user_id' => $user->id,
            'type' => 'out',
            'qty' => 36,
            'reference' => 'INV-TEST-001',
        ]);
    }

    public function test_stock_does_not_fall_below_zero_on_overconsumption(): void
    {
        $user = User::create([
            'name' => 'Kasir Test',
            'username' => 'kasirtest2',
            'code' => 'KSR002',
            'email' => 'kasir2@test.com',
            'password' => bcrypt('password'),
            'role' => 'Kasir',
        ]);

        $this->actingAs($user);

        $category = Category::create([
            'name' => 'Snack',
            'name_code' => 'SNK',
            'user_id' => $user->id,
        ]);

        // Sisa stok hanya 10 gram
        $bahan = Bahan::create([
            'user_id' => $user->id,
            'name_bahan' => 'Bahan Sedikit',
            'unit' => 'gram',
            'purchase_unit' => 'gram',
            'purchase_qty' => 10,
            'base_unit' => 'gram',
            'stock' => 10,
            'price' => 10000,
            'cost_per_base_unit' => 1000,
            'status' => 'active'
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'name_prd' => 'Kue Test',
            'code_prd' => 'PRD-KUE',
            'price' => 15000,
            'sales_type' => 'all',
        ]);

        $product->bahans()->attach($bahan->id, ['quantity' => 15, 'unit' => 'gram']);

        // Jual 1 porsi butuh 15 gram, padahal stok cuma 10 gram
        DataShopping::deductMaterialStockForProduct($product->id, 1, 'INV-TEST-002');

        $bahan->refresh();
        $this->assertEquals(0, $bahan->stock); // Dipastikan tidak minus
    }
}

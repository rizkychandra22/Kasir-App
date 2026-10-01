<?php

namespace Tests\Feature;

use App\Models\Bahan;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductRecipeTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_recipe_total_cost_calculation(): void
    {
        $user = User::create([
            'name' => 'Admin Test',
            'username' => 'admintest',
            'code' => 'ADM001',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'Admin',
        ]);

        $category = Category::create([
            'name' => 'Beverage',
            'name_code' => 'BVG',
            'user_id' => $user->id,
        ]);

        // Kopi: 1000 gram @ 180.000 -> 180 / gram
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

        // Susu: 1000 ml @ 25.000 -> 25 / ml
        $susu = Bahan::create([
            'user_id' => $user->id,
            'name_bahan' => 'Fresh Milk Test',
            'unit' => 'liter',
            'purchase_unit' => 'liter',
            'purchase_qty' => 1,
            'base_unit' => 'ml',
            'stock' => 1000,
            'price' => 25000,
            'cost_per_base_unit' => 25,
            'status' => 'active'
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $user->id,
            'name_prd' => 'Cappuccino Test',
            'code_prd' => 'PRD-CPP',
            'price' => 28000,
            'price_offline' => 28000,
            'price_online' => 32000,
            'sales_type' => 'all',
        ]);

        // Recipe: 18g Kopi (18 * 180 = 3240) + 120ml Susu (120 * 25 = 3000)
        // Total Recipe Cost = 6240
        $product->bahans()->attach($kopi->id, ['quantity' => 18, 'unit' => 'gram']);
        $product->bahans()->attach($susu->id, ['quantity' => 120, 'unit' => 'ml']);

        $totalCost = $product->calculateTotalRecipeCost();
        $this->assertEquals(6240, $totalCost);
    }
}

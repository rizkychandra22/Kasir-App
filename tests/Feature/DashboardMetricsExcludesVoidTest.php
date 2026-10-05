<?php

namespace Tests\Feature;

use App\Livewire\Dashboard\Admin as DashboardAdmin;
use App\Livewire\Dashboard\Kasir as DashboardKasir;
use App\Models\Category;
use App\Models\Product;
use App\Models\Shopping;
use App\Models\ShoppingDetail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardMetricsExcludesVoidTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::create([
            'name' => 'Admin Test',
            'username' => 'admintest',
            'code' => 'ADM001',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'Admin',
        ]);
        $this->actingAs($this->user);
    }

    public function test_dashboard_metrics_exclude_canceled_and_void_transactions(): void
    {
        $category = Category::create([
            'name' => 'Minuman',
            'name_code' => 'MNM',
            'user_id' => $this->user->id,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'user_id' => $this->user->id,
            'name_prd' => 'Kopi Susu',
            'code_prd' => 'KPS',
            'price' => 15000,
        ]);

        $now = Carbon::now();

        // 1. Valid offline transaction (10 cups = Rp150.000)
        $validOffline = Shopping::create([
            'invoice' => 'INV-VALID-OFF',
            'sales_type' => 'offline',
            'user_id' => $this->user->id,
            'total_price' => 150000,
            'pay' => 150000,
            'change' => 0,
            'status' => 'completed',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        ShoppingDetail::create([
            'shopping_id' => $validOffline->id,
            'product_id' => $product->id,
            'qty' => 10,
            'price' => 15000,
            'subtotal' => 150000,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 2. Valid online transaction (5 cups = Rp75.000)
        $validOnline = Shopping::create([
            'invoice' => 'INV-VALID-ON',
            'sales_type' => 'online',
            'user_id' => $this->user->id,
            'total_price' => 75000,
            'pay' => 75000,
            'change' => 0,
            'status' => 'paid',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        ShoppingDetail::create([
            'shopping_id' => $validOnline->id,
            'product_id' => $product->id,
            'qty' => 5,
            'price' => 15000,
            'subtotal' => 75000,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 3. Canceled transaction (8 cups = Rp120.000) - SHOULD BE EXCLUDED
        $canceledTrx = Shopping::create([
            'invoice' => 'INV-CANCELED',
            'sales_type' => 'offline',
            'user_id' => $this->user->id,
            'total_price' => 120000,
            'pay' => 120000,
            'change' => 0,
            'status' => 'canceled',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        ShoppingDetail::create([
            'shopping_id' => $canceledTrx->id,
            'product_id' => $product->id,
            'qty' => 8,
            'price' => 15000,
            'subtotal' => 120000,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // 4. Void transaction (4 cups = Rp60.000) - SHOULD BE EXCLUDED
        $voidTrx = Shopping::create([
            'invoice' => 'INV-VOID',
            'sales_type' => 'online',
            'user_id' => $this->user->id,
            'total_price' => 60000,
            'pay' => 60000,
            'change' => 0,
            'status' => 'void',
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        ShoppingDetail::create([
            'shopping_id' => $voidTrx->id,
            'product_id' => $product->id,
            'qty' => 4,
            'price' => 15000,
            'subtotal' => 60000,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Verify Admin Dashboard metrics
        Livewire::test(DashboardAdmin::class)
            ->assertViewHas('countProductSold', 15) // Only 10 + 5, excludes 8 + 4
            ->assertViewHas('countRevenue', 225000) // Only 150.000 + 75.000, excludes 120.000 + 60.000
            ->assertViewHas('revenueOffline', 150000)
            ->assertViewHas('revenueOnline', 75000)
            ->assertDontSee('INV-CANCELED')
            ->assertDontSee('INV-VOID')
            ->assertSee('INV-VALID-OFF')
            ->assertSee('INV-VALID-ON');

        // Verify Kasir Dashboard metrics
        Livewire::test(DashboardKasir::class)
            ->assertViewHas('countProductSold', 15)
            ->assertViewHas('countRevenue', 225000)
            ->assertViewHas('revenueOffline', 150000)
            ->assertViewHas('revenueOnline', 75000)
            ->assertDontSee('INV-CANCELED')
            ->assertDontSee('INV-VOID')
            ->assertSee('INV-VALID-OFF')
            ->assertSee('INV-VALID-ON');
    }
}

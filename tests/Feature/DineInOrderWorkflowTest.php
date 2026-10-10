<?php

namespace Tests\Feature;

use App\Livewire\Transaction\CreateShopping;
use App\Livewire\Transaction\DataShopping;
use App\Models\Bahan;
use App\Models\BahanStockMovement;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Shopping;
use App\Models\ShoppingDetail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;
use Tests\TestCase;

class DineInOrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected User $cashier;
    protected Category $category;
    protected Bahan $kopi;
    protected Bahan $susu;
    protected Product $latte;
    protected Product $americano;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cashier = User::create([
            'name' => 'Barista Kasir',
            'username' => 'baristakasir',
            'code' => 'KSR-01',
            'email' => 'kasir@brewisland.com',
            'password' => bcrypt('password'),
            'role' => 'Kasir',
        ]);

        $this->actingAs($this->cashier);

        $this->category = Category::create([
            'name' => 'Coffee',
            'name_code' => 'COF',
            'user_id' => $this->cashier->id,
        ]);

        // Kopi: 1000 gram awal, Rp100.000 / 1000g => Rp100/g
        $this->kopi = Bahan::create([
            'user_id' => $this->cashier->id,
            'name_bahan' => 'Biji Kopi Arabika',
            'unit' => 'kg',
            'purchase_unit' => 'kg',
            'purchase_qty' => 1,
            'base_unit' => 'gram',
            'stock' => 1000,
            'price' => 100000,
            'cost_per_base_unit' => 100,
            'status' => 'active',
        ]);

        // Susu: 2000 ml awal, Rp30.000 / 1000ml => Rp30/ml
        $this->susu = Bahan::create([
            'user_id' => $this->cashier->id,
            'name_bahan' => 'Susu Fresh Milk',
            'unit' => 'liter',
            'purchase_unit' => 'liter',
            'purchase_qty' => 1,
            'base_unit' => 'ml',
            'stock' => 2000,
            'price' => 30000,
            'cost_per_base_unit' => 30,
            'status' => 'active',
        ]);

        // Product 1: Coffee Latte (Harga 20.000, Resep: 18g Kopi + 100ml Susu)
        // HPP = (18 * 100) + (100 * 30) = 1800 + 3000 = 4800
        $this->latte = Product::create([
            'category_id' => $this->category->id,
            'user_id' => $this->cashier->id,
            'name_prd' => 'Coffee Latte',
            'code_prd' => 'LATTE',
            'sales_type' => 'all',
            'price' => 20000,
            'price_offline' => 20000,
            'price_online' => 25000,
        ]);
        $this->latte->bahans()->attach([
            $this->kopi->id => ['quantity' => 18, 'unit' => 'gram'],
            $this->susu->id => ['quantity' => 100, 'unit' => 'ml'],
        ]);

        // Product 2: Americano (Harga 18.000, Resep: 18g Kopi)
        // HPP = 18 * 100 = 1800
        $this->americano = Product::create([
            'category_id' => $this->category->id,
            'user_id' => $this->cashier->id,
            'name_prd' => 'Americano',
            'code_prd' => 'AMR',
            'sales_type' => 'all',
            'price' => 18000,
            'price_offline' => 18000,
            'price_online' => 22000,
        ]);
        $this->americano->bahans()->attach([
            $this->kopi->id => ['quantity' => 18, 'unit' => 'gram'],
        ]);
    }

    /**
     * 1. test_can_access_create_shopping_page
     */
    public function test_can_access_create_shopping_page(): void
    {
        $response = $this->get(route('kasir.shopping.create'));
        $response->assertStatus(200);
        $response->assertSee('Transaksi Baru');
        $response->assertSee('Quick Order');
        $response->assertSee('Open Bill');
    }

    /**
     * 2. test_can_create_quick_order_transaction
     */
    public function test_can_create_quick_order_transaction(): void
    {
        Livewire::test(CreateShopping::class)
            ->set('order_mode', 'quick')
            ->call('addToCart', $this->latte->id)
            ->set('pay', 50000)
            ->call('storeQuickOrder')
            ->assertHasNoErrors()
            ->assertSet('showReceiptModal', true);

        $this->assertDatabaseHas('shoppings', [
            'total_price' => 20000,
            'pay' => 50000,
            'change' => 30000,
            'sales_type' => 'offline',
            'payment_method' => 'cash',
        ]);

        $shopping = Shopping::latest('id')->first();
        $this->assertDatabaseHas('shopping_details', [
            'shopping_id' => $shopping->id,
            'product_id' => $this->latte->id,
            'qty' => 1,
            'price' => 20000,
            'material_cost' => 4800,
        ]);
    }

    /**
     * 3. test_can_create_open_bill_order
     */
    public function test_can_create_open_bill_order(): void
    {
        Livewire::test(CreateShopping::class)
            ->set('order_mode', 'open_bill')
            ->set('customer_name', 'Budi')
            ->call('addToCart', $this->latte->id)
            ->call('addToCart', $this->latte->id) // Qty 2
            ->call('storeOpenBill')
            ->assertHasNoErrors()
            ->assertSet('showSlipModal', true);

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Budi',
            'status' => 'open',
            'total_amount' => 40000,
        ]);

        $order = Order::latest('id')->first();
        $this->assertDatabaseHas('order_details', [
            'order_id' => $order->id,
            'product_id' => $this->latte->id,
            'qty' => 2,
            'unit_price' => 20000,
            'subtotal' => 40000,
            'material_cost' => 9600, // 4800 * 2
        ]);
    }

    /**
     * 4. test_open_bill_generates_unique_order_number
     */
    public function test_open_bill_generates_unique_order_number(): void
    {
        // First order for Budi
        Livewire::test(CreateShopping::class)
            ->set('order_mode', 'open_bill')
            ->set('customer_name', 'Budi')
            ->call('addToCart', $this->latte->id)
            ->call('storeOpenBill');

        // Second order also for Budi
        Livewire::test(CreateShopping::class)
            ->set('order_mode', 'open_bill')
            ->set('customer_name', 'Budi')
            ->call('addToCart', $this->americano->id)
            ->call('storeOpenBill');

        $orders = Order::orderBy('id')->get();
        $this->assertCount(2, $orders);

        $order1 = $orders[0];
        $order2 = $orders[1];

        $this->assertEquals('OB-0001', $order1->order_number);
        $this->assertEquals('OB-0002', $order2->order_number);
        $this->assertNotEquals($order1->order_number, $order2->order_number);
        $this->assertEquals('Budi', $order1->customer_name);
        $this->assertEquals('Budi', $order2->customer_name);
    }

    /**
     * 5. test_open_bill_does_not_require_table_number
     */
    public function test_open_bill_does_not_require_table_number(): void
    {
        // Must not have table_number column in orders table
        $this->assertFalse(Schema::hasColumn('orders', 'table_number'));

        // Creation succeeds without table number
        $component = Livewire::test(CreateShopping::class)
            ->set('order_mode', 'open_bill')
            ->set('customer_name', 'Siti')
            ->call('addToCart', $this->latte->id)
            ->call('storeOpenBill')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('orders', [
            'customer_name' => 'Siti',
            'status' => 'open',
        ]);
    }

    /**
     * 6. test_open_bill_deducts_material_stock_once
     */
    public function test_open_bill_deducts_material_stock_once(): void
    {
        $kopiInitial = (float)$this->kopi->stock; // 1000
        $susuInitial = (float)$this->susu->stock; // 2000

        // Create Open Bill with Coffee Latte x 2 (18g x 2 = 36g kopi, 100ml x 2 = 200ml susu)
        Livewire::test(CreateShopping::class)
            ->set('order_mode', 'open_bill')
            ->set('customer_name', 'Budi')
            ->call('addToCart', $this->latte->id)
            ->call('addToCart', $this->latte->id)
            ->call('storeOpenBill');

        $this->assertEquals($kopiInitial - 36, (float)$this->kopi->fresh()->stock);
        $this->assertEquals($susuInitial - 200, (float)$this->susu->fresh()->stock);

        $this->assertDatabaseHas('bahan_stock_movements', [
            'bahan_id' => $this->kopi->id,
            'qty' => 36,
            'type' => 'out',
            'reference' => 'OB-0001',
        ]);
        $this->assertDatabaseHas('bahan_stock_movements', [
            'bahan_id' => $this->susu->id,
            'qty' => 200,
            'type' => 'out',
            'reference' => 'OB-0001',
        ]);
    }

    /**
     * 7. test_can_add_menu_to_active_open_bill
     */
    public function test_can_add_menu_to_active_open_bill(): void
    {
        // 1. Buat Open Bill: Coffee Latte x 2 (Rp40.000)
        $order = Order::create([
            'order_number' => 'OB-0001',
            'customer_name' => 'Budi',
            'user_id' => $this->cashier->id,
            'total_amount' => 40000,
            'status' => 'open',
        ]);
        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $this->latte->id,
            'product_name' => $this->latte->name_prd,
            'qty' => 2,
            'unit_price' => 20000,
            'subtotal' => 40000,
            'material_cost' => 9600,
        ]);

        // 2. Tambah menu: Americano x 1 (Rp18.000)
        Livewire::test(DataShopping::class)
            ->call('openAddMenuModal', $order->id)
            ->call('addToAddonCart', $this->americano->id)
            ->call('saveAddMenu')
            ->assertHasNoErrors();

        $freshOrder = $order->fresh();
        $this->assertEquals(58000, $freshOrder->total_amount); // 40.000 + 18.000

        $this->assertDatabaseHas('order_details', [
            'order_id' => $order->id,
            'product_id' => $this->americano->id,
            'qty' => 1,
            'subtotal' => 18000,
            'material_cost' => 1800,
        ]);
    }

    /**
     * 8. test_add_menu_deducts_only_additional_material_stock
     */
    public function test_add_menu_deducts_only_additional_material_stock(): void
    {
        // Initial stock: Kopi 1000, Susu 2000
        // Buat Open Bill: Latte x 2 (Kopi -36, Susu -200)
        Livewire::test(CreateShopping::class)
            ->set('order_mode', 'open_bill')
            ->set('customer_name', 'Budi')
            ->call('addToCart', $this->latte->id)
            ->call('addToCart', $this->latte->id)
            ->call('storeOpenBill');

        $kopiAfterInitial = (float)$this->kopi->fresh()->stock; // 964
        $susuAfterInitial = (float)$this->susu->fresh()->stock; // 1800

        $order = Order::latest('id')->first();

        // Tambah Menu: Americano x 1 (Kopi 18g, Susu 0ml)
        Livewire::test(DataShopping::class)
            ->call('openAddMenuModal', $order->id)
            ->call('addToAddonCart', $this->americano->id)
            ->call('saveAddMenu');

        // Kopi harus berkurang hanya 18g
        $this->assertEquals($kopiAfterInitial - 18, (float)$this->kopi->fresh()->stock);
        // Susu TIDAK BOLEH berkurang lagi! (Tetap 1800)
        $this->assertEquals($susuAfterInitial, (float)$this->susu->fresh()->stock);
    }

    /**
     * 9. test_checkout_open_bill_creates_shopping_transaction
     */
    public function test_checkout_open_bill_creates_shopping_transaction(): void
    {
        $order = Order::create([
            'order_number' => 'OB-0001',
            'customer_name' => 'Budi',
            'user_id' => $this->cashier->id,
            'total_amount' => 58000,
            'status' => 'open',
        ]);
        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $this->latte->id,
            'product_name' => 'Coffee Latte',
            'qty' => 2,
            'unit_price' => 20000,
            'subtotal' => 40000,
            'material_cost' => 9600,
        ]);
        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $this->americano->id,
            'product_name' => 'Americano',
            'qty' => 1,
            'unit_price' => 18000,
            'subtotal' => 18000,
            'material_cost' => 1800,
        ]);

        Livewire::test(DataShopping::class)
            ->call('openCheckoutModal', $order->id)
            ->set('checkoutPaymentMethod', 'cash')
            ->set('checkoutPay', 60000)
            ->call('processCheckout')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('shoppings', [
            'total_price' => 58000,
            'pay' => 60000,
            'change' => 2000,
            'sales_type' => 'offline',
            'payment_method' => 'cash',
        ]);

        $shopping = Shopping::latest('id')->first();
        $this->assertEquals(2, $shopping->details()->count());
        $this->assertEquals('paid', $order->fresh()->status);
        $this->assertEquals($shopping->id, $order->fresh()->shopping_id);
    }

    /**
     * 10. test_checkout_does_not_double_deduct_stock
     */
    public function test_checkout_does_not_double_deduct_stock(): void
    {
        // Buat open bill via CreateShopping (stok dipotong di sini)
        Livewire::test(CreateShopping::class)
            ->set('order_mode', 'open_bill')
            ->set('customer_name', 'Budi')
            ->call('addToCart', $this->latte->id)
            ->call('storeOpenBill');

        $kopiBeforeCheckout = (float)$this->kopi->fresh()->stock;
        $susuBeforeCheckout = (float)$this->susu->fresh()->stock;
        $movementsBeforeCheckout = BahanStockMovement::count();

        $order = Order::latest('id')->first();

        // Checkout
        Livewire::test(DataShopping::class)
            ->call('openCheckoutModal', $order->id)
            ->set('checkoutPaymentMethod', 'cash')
            ->set('checkoutPay', 50000)
            ->call('processCheckout');

        // Stok TIDAK BOLEH berubah saat checkout
        $this->assertEquals($kopiBeforeCheckout, (float)$this->kopi->fresh()->stock);
        $this->assertEquals($susuBeforeCheckout, (float)$this->susu->fresh()->stock);

        // Tidak boleh ada penambahan record BahanStockMovement baru saat checkout
        $this->assertEquals($movementsBeforeCheckout, BahanStockMovement::count());
    }

    /**
     * 11. test_cash_checkout_calculates_change
     */
    public function test_cash_checkout_calculates_change(): void
    {
        $order = Order::create([
            'order_number' => 'OB-0001',
            'customer_name' => 'Budi',
            'user_id' => $this->cashier->id,
            'total_amount' => 58000,
            'status' => 'open',
        ]);

        Livewire::test(DataShopping::class)
            ->call('openCheckoutModal', $order->id)
            ->set('checkoutPaymentMethod', 'cash')
            ->set('checkoutPay', 100000)
            ->assertSet('checkoutChange', 42000);
    }

    /**
     * 12. test_qris_checkout_requires_manual_confirmation
     */
    public function test_qris_checkout_requires_manual_confirmation(): void
    {
        $order = Order::create([
            'order_number' => 'OB-0001',
            'customer_name' => 'Budi',
            'user_id' => $this->cashier->id,
            'total_amount' => 58000,
            'status' => 'open',
        ]);
        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $this->latte->id,
            'product_name' => 'Coffee Latte',
            'qty' => 1,
            'unit_price' => 58000,
            'subtotal' => 58000,
            'material_cost' => 4800,
        ]);

        // Attempt checkout QRIS without confirmation
        Livewire::test(DataShopping::class)
            ->call('openCheckoutModal', $order->id)
            ->call('setCheckoutPaymentMethod', 'qris')
            ->call('processCheckout')
            ->assertHasErrors(['checkoutQrisConfirmed']);

        // With confirmation
        Livewire::test(DataShopping::class)
            ->call('openCheckoutModal', $order->id)
            ->call('setCheckoutPaymentMethod', 'qris')
            ->call('confirmCheckoutQris')
            ->call('processCheckout')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('shoppings', [
            'total_price' => 58000,
            'pay' => 58000,
            'change' => 0,
            'payment_method' => 'qris',
        ]);
    }

    /**
     * 13. test_open_bill_status_becomes_paid_after_checkout
     */
    public function test_open_bill_status_becomes_paid_after_checkout(): void
    {
        $order = Order::create([
            'order_number' => 'OB-0001',
            'customer_name' => 'Budi',
            'user_id' => $this->cashier->id,
            'total_amount' => 20000,
            'status' => 'open',
        ]);
        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $this->latte->id,
            'product_name' => 'Coffee Latte',
            'qty' => 1,
            'unit_price' => 20000,
            'subtotal' => 20000,
            'material_cost' => 4800,
        ]);

        $this->assertEquals('open', $order->status);

        Livewire::test(DataShopping::class)
            ->call('openCheckoutModal', $order->id)
            ->set('checkoutPaymentMethod', 'cash')
            ->set('checkoutPay', 20000)
            ->call('processCheckout');

        $this->assertEquals('paid', $order->fresh()->status);
        $this->assertNotNull($order->fresh()->shopping_id);
    }

    /**
     * 14. test_open_bill_historical_material_cost_is_preserved
     */
    public function test_open_bill_historical_material_cost_is_preserved(): void
    {
        // 1. Open Bill dibuat dengan snapshot HPP bahan 4800
        $order = Order::create([
            'order_number' => 'OB-0001',
            'customer_name' => 'Budi',
            'user_id' => $this->cashier->id,
            'total_amount' => 20000,
            'status' => 'open',
        ]);
        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $this->latte->id,
            'product_name' => 'Coffee Latte',
            'qty' => 1,
            'unit_price' => 20000,
            'subtotal' => 20000,
            'material_cost' => 4800, // Snapshot
        ]);

        // 2. Sekarang harga bahan baku kopi naik 3x lipat
        $this->kopi->update([
            'price' => 300000,
            'cost_per_base_unit' => 300,
        ]);

        // HPP jika dihitung ulang sekarang: (18 * 300) + (100 * 30) = 5400 + 3000 = 8400
        $this->assertEquals(8400, $this->latte->calculateTotalRecipeCost());

        // 3. Checkout Open Bill
        Livewire::test(DataShopping::class)
            ->call('openCheckoutModal', $order->id)
            ->set('checkoutPaymentMethod', 'cash')
            ->set('checkoutPay', 20000)
            ->call('processCheckout');

        $shopping = Shopping::latest('id')->first();
        $detail = $shopping->details()->first();

        // HPP transaksi di shopping_details HARUS tetap 4800 (menggunakan snapshot awal)
        $this->assertEquals(4800, (float)$detail->material_cost);
        $this->assertEquals(4800, (float)$shopping->hpp);
    }

    /**
     * 15. test_quick_order_existing_behavior_is_unchanged
     */
    public function test_quick_order_existing_behavior_is_unchanged(): void
    {
        // Existing modal / flow inside DataShopping
        Livewire::test(DataShopping::class)
            ->call('addToCart', $this->latte->id)
            ->set('pay', 50000)
            ->call('store')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('shoppings', [
            'total_price' => 20000,
            'pay' => 50000,
            'change' => 30000,
        ]);
    }

    /**
     * 16. test_cancelled_or_invalid_sales_are_not_inappropriately_counted
     */
    public function test_cancelled_or_invalid_sales_are_not_inappropriately_counted(): void
    {
        $validShopping = Shopping::create([
            'invoice' => 'INV-VALID',
            'user_id' => $this->cashier->id,
            'total_price' => 20000,
            'pay' => 20000,
            'change' => 0,
            'status' => null,
        ]);

        $canceledShopping = Shopping::create([
            'invoice' => 'INV-CANCEL',
            'user_id' => $this->cashier->id,
            'total_price' => 50000,
            'pay' => 50000,
            'change' => 0,
            'status' => 'canceled',
        ]);

        // Open bill order (status open) must not be in shoppings
        $order = Order::create([
            'order_number' => 'OB-0001',
            'customer_name' => 'Budi',
            'user_id' => $this->cashier->id,
            'total_amount' => 30000,
            'status' => 'open',
        ]);

        $validSales = Shopping::validSales()->get();
        $this->assertTrue($validSales->contains('id', $validShopping->id));
        $this->assertFalse($validSales->contains('id', $canceledShopping->id));
        $this->assertEquals(20000, Shopping::validSales()->sum('total_price'));
    }

    /**
     * 17. test_open_bill_appears_in_active_orders
     */
    public function test_open_bill_appears_in_active_orders(): void
    {
        $order = Order::create([
            'order_number' => 'OB-0001',
            'customer_name' => 'Budi Santoso',
            'user_id' => $this->cashier->id,
            'total_amount' => 45000,
            'status' => 'open',
        ]);

        Livewire::test(DataShopping::class)
            ->set('activeTab', 'active_orders')
            ->assertSee('OB-0001')
            ->assertSee('Budi Santoso')
            ->assertSee('45.000');
    }

    /**
     * 18. test_paid_open_bill_appears_in_sales_history
     */
    public function test_paid_open_bill_appears_in_sales_history(): void
    {
        $order = Order::create([
            'order_number' => 'OB-0001',
            'customer_name' => 'Budi Santoso',
            'user_id' => $this->cashier->id,
            'total_amount' => 20000,
            'status' => 'open',
        ]);
        OrderDetail::create([
            'order_id' => $order->id,
            'product_id' => $this->latte->id,
            'product_name' => 'Coffee Latte',
            'qty' => 1,
            'unit_price' => 20000,
            'subtotal' => 20000,
            'material_cost' => 4800,
        ]);

        // Checkout
        Livewire::test(DataShopping::class)
            ->call('openCheckoutModal', $order->id)
            ->set('checkoutPaymentMethod', 'cash')
            ->set('checkoutPay', 20000)
            ->call('processCheckout');

        $shopping = Shopping::latest('id')->first();

        // Switch to history tab
        Livewire::test(DataShopping::class)
            ->set('activeTab', 'history')
            ->assertSee($shopping->invoice)
            ->assertSee('20.000');
    }
}

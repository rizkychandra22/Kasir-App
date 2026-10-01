<?php

require 'c:/laragon/www/Kasir-App/vendor/autoload.php';
$app = require_once 'c:/laragon/www/Kasir-App/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Bahan;
use App\Models\Product;
use App\Models\Category;
use App\Models\BahanStockMovement;
use App\Models\ShoppingDetail;
use App\Services\UnitConversionService;
use App\Livewire\Kasir\DataShopping;
use Illuminate\Support\Facades\DB;

echo "=========================================================\n";
echo "--- STARTING COMPLETE 18-TEST SUITE VERIFICATION ---\n";
echo "=========================================================\n\n";

$category = Category::first() ?? Category::create(['name' => 'Test Category', 'name_code' => 'TC001', 'user_id' => 1]);

// ----------------------------------------------------
// TEST 1: Buat Bahan Kopi Arabika 1 kg -> saves 1000 gram
// ----------------------------------------------------
echo "TEST 1: Input Bahan 1 kg -> Base Unit Conversion (1000 gram)\n";
$converted = UnitConversionService::convertToBaseUnit(1, 'kg');
$baseStock = $converted['amount'];
$baseUnit = $converted['base_unit'];

Bahan::where('name_bahan', 'Kopi Arabika Test')->delete();
$kopi = Bahan::create([
    'user_id' => 1,
    'name_bahan' => 'Kopi Arabika Test',
    'unit' => 'kg',
    'purchase_unit' => 'kg',
    'purchase_qty' => 1,
    'base_unit' => $baseUnit,
    'stock' => $baseStock,
    'price' => 180000,
    'cost_per_base_unit' => 180000 / $baseStock,
    'description' => 'Bahan Test',
    'status' => 'active'
]);

echo " -> Stock stored: {$kopi->stock} {$kopi->base_unit} (Expected: 1000 gram)\n";
assert($kopi->stock == 1000 && $kopi->base_unit === 'gram', "TEST 1 FAILED");
echo " -> TEST 1 PASSED!\n\n";

// Log initial movement for test
BahanStockMovement::create([
    'bahan_id' => $kopi->id,
    'user_id' => 1,
    'type' => 'in',
    'qty' => 1000,
    'stock_before' => 0,
    'stock_after' => 1000,
    'reference' => 'INIT-STOCK',
    'notes' => 'Stok awal',
]);

// ----------------------------------------------------
// TEST 2: Produk Coffee Latte dengan resep Kopi Arabika = 18 gram
// ----------------------------------------------------
echo "TEST 2: Product Composition Definition\n";
Product::where('name_prd', 'Coffee Latte Test')->delete();
$coffeeLatte = Product::create([
    'category_id' => $category->id,
    'user_id' => 1,
    'name_prd' => 'Coffee Latte Test',
    'code_prd' => 'PRD-CLT',
    'price' => 25000,
    'price_offline' => 25000,
    'price_online' => 28000,
    'sales_type' => 'all',
    'stock' => 100,
]);

$coffeeLatte->bahans()->attach($kopi->id, [
    'quantity' => 18,
    'unit' => 'gram'
]);

$recipe = $coffeeLatte->bahans()->first();
echo " -> Attached recipe: {$recipe->name_bahan} = {$recipe->pivot->quantity} {$recipe->pivot->unit}\n";
assert($recipe->pivot->quantity == 18 && $recipe->pivot->unit === 'gram', "TEST 2 FAILED");
echo " -> TEST 2 PASSED!\n\n";

// ----------------------------------------------------
// TEST 3 & 4: Deductions on Sale (Sell 1 -> 982g, then Sell 2 -> 946g)
// ----------------------------------------------------
echo "TEST 3: Sell Coffee Latte x 1 (Stock: 1000 - 18 = 982 gram)\n";
DataShopping::deductMaterialStockForProduct($coffeeLatte->id, 1, 'TRX-TEST-001', 'Coffee Latte Test x 1');
$kopi->refresh();
echo " -> Current Stock: {$kopi->stock} {$kopi->base_unit}\n";
assert($kopi->stock == 982, "TEST 3 FAILED");
echo " -> TEST 3 PASSED!\n\n";

echo "TEST 4: Sell Coffee Latte x 2 (Stock: 982 - (18*2) = 946 gram)\n";
DataShopping::deductMaterialStockForProduct($coffeeLatte->id, 2, 'TRX-TEST-002', 'Coffee Latte Test x 2');
$kopi->refresh();
echo " -> Current Stock: {$kopi->stock} {$kopi->base_unit}\n";
assert($kopi->stock == 946, "TEST 4 FAILED");
echo " -> TEST 4 PASSED!\n\n";

// ----------------------------------------------------
// TEST 5: Tambah Stok 2 kg -> +2000 gram (Stock: 946 + 2000 = 2946 gram)
// ----------------------------------------------------
echo "TEST 5: Add Stock 2 kg (+2000 gram)\n";
$addition = UnitConversionService::convertToBaseUnit(2, 'kg');
$additionBaseQty = $addition['amount'];
$stockBefore = $kopi->stock;
$kopi->stock += $additionBaseQty;
$kopi->save();

BahanStockMovement::create([
    'bahan_id' => $kopi->id,
    'user_id' => 1,
    'type' => 'in',
    'qty' => $additionBaseQty,
    'stock_before' => $stockBefore,
    'stock_after' => $kopi->stock,
    'reference' => 'TEST-ADDITION',
    'notes' => 'Tambah stok 2 kg',
]);

$kopi->refresh();
echo " -> Current Stock: {$kopi->stock} {$kopi->base_unit} (Expected: 2946 gram)\n";
assert($kopi->stock == 2946, "TEST 5 FAILED");
echo " -> TEST 5 PASSED!\n\n";

// ----------------------------------------------------
// TEST 6: Volume Item Susu (Input 2 liter = 2000 ml, Resep 100ml, Jual x 3 -> 1700 ml)
// ----------------------------------------------------
echo "TEST 6: Volume Item Susu (Input 2 liter = 2000 ml, Resep 100 ml, Sell x 3)\n";
Bahan::where('name_bahan', 'Susu Test')->delete();
$susuConverted = UnitConversionService::convertToBaseUnit(2, 'liter');
$susu = Bahan::create([
    'user_id' => 1,
    'name_bahan' => 'Susu Test',
    'unit' => 'liter',
    'purchase_unit' => 'liter',
    'purchase_qty' => 2,
    'base_unit' => $susuConverted['base_unit'],
    'stock' => $susuConverted['amount'],
    'price' => 30000,
    'cost_per_base_unit' => 30000 / $susuConverted['amount'],
    'status' => 'active',
]);

Product::where('name_prd', 'Milk Tea Test')->delete();
$milkTea = Product::create([
    'category_id' => $category->id,
    'user_id' => 1,
    'name_prd' => 'Milk Tea Test',
    'code_prd' => 'PRD-MTT',
    'price' => 15000,
    'price_offline' => 15000,
    'price_online' => 18000,
    'sales_type' => 'all',
    'stock' => 100,
]);
$milkTea->bahans()->attach($susu->id, ['quantity' => 100, 'unit' => 'ml']);

DataShopping::deductMaterialStockForProduct($milkTea->id, 3, 'TRX-TEST-003', 'Milk Tea Test x 3');
$susu->refresh();
echo " -> Current Susu Stock: {$susu->stock} {$susu->base_unit} (Expected: 1700 ml)\n";
assert($susu->stock == 1700, "TEST 6 FAILED");
echo " -> TEST 6 PASSED!\n\n";

// ----------------------------------------------------
// TEST 7: Pcs item Cup (Stok 100 pcs, Resep 1 pcs, Jual x 3 -> 97 pcs)
// ----------------------------------------------------
echo "TEST 7: Count Item Cup (Stok 100 pcs, Resep 1 pcs, Sell x 3)\n";
Bahan::where('name_bahan', 'Cup Test')->delete();
$cupConverted = UnitConversionService::convertToBaseUnit(100, 'pcs');
$cup = Bahan::create([
    'user_id' => 1,
    'name_bahan' => 'Cup Test',
    'unit' => 'pcs',
    'purchase_unit' => 'pcs',
    'purchase_qty' => 100,
    'base_unit' => $cupConverted['base_unit'],
    'stock' => $cupConverted['amount'],
    'price' => 50000,
    'cost_per_base_unit' => 50000 / $cupConverted['amount'],
    'status' => 'active',
]);

Product::where('name_prd', 'Cup Drink Test')->delete();
$cupDrink = Product::create([
    'category_id' => $category->id,
    'user_id' => 1,
    'name_prd' => 'Cup Drink Test',
    'code_prd' => 'PRD-CDT',
    'price' => 10000,
    'price_offline' => 10000,
    'price_online' => 12000,
    'sales_type' => 'all',
    'stock' => 100,
]);
$cupDrink->bahans()->attach($cup->id, ['quantity' => 1, 'unit' => 'pcs']);

DataShopping::deductMaterialStockForProduct($cupDrink->id, 3, 'TRX-TEST-004', 'Cup Drink Test x 3');
$cup->refresh();
echo " -> Current Cup Stock: {$cup->stock} {$cup->base_unit} (Expected: 97 pcs)\n";
assert($cup->stock == 97, "TEST 7 FAILED");
echo " -> TEST 7 PASSED!\n\n";

// ----------------------------------------------------
// TEST 8: Stock Insufficient Validation
// ----------------------------------------------------
echo "TEST 8: Pre-Checkout Insufficient Stock Validation\n";
Bahan::where('name_bahan', 'Rare Spice Test')->delete();
$rareSpiceConverted = UnitConversionService::convertToBaseUnit(10, 'gram');
$rareSpice = Bahan::create([
    'user_id' => 1,
    'name_bahan' => 'Rare Spice Test',
    'unit' => 'gram',
    'purchase_unit' => 'gram',
    'purchase_qty' => 10,
    'base_unit' => $rareSpiceConverted['base_unit'],
    'stock' => $rareSpiceConverted['amount'],
    'price' => 50000,
    'cost_per_base_unit' => 50000 / $rareSpiceConverted['amount'],
    'status' => 'active',
]);

Product::where('name_prd', 'Special Dish Test')->delete();
$specialDish = Product::create([
    'category_id' => $category->id,
    'user_id' => 1,
    'name_prd' => 'Special Dish Test',
    'code_prd' => 'PRD-SDT',
    'price' => 50000,
    'price_offline' => 50000,
    'price_online' => 55000,
    'sales_type' => 'all',
    'stock' => 100,
]);
$specialDish->bahans()->attach($rareSpice->id, ['quantity' => 18, 'unit' => 'gram']);

$cartItems = [
    [
        'product_id' => $specialDish->id,
        'qty' => 1,
        'product_name' => 'Special Dish Test',
    ]
];

$validationError = DataShopping::validateStockForCart($cartItems);
echo " -> Validation error caught: '{$validationError}'\n";
assert(!empty($validationError) && str_contains($validationError, 'tidak mencukupi'), "TEST 8 FAILED");
echo " -> TEST 8 PASSED!\n\n";

// ----------------------------------------------------
// TEST 9: Incompatible Unit Validation
// ----------------------------------------------------
echo "TEST 9: Incompatible Unit Validation\n";
$isCompatible = UnitConversionService::areUnitsCompatible('gram', 'ml');
echo " -> Are 'gram' and 'ml' compatible? " . ($isCompatible ? 'YES' : 'NO') . "\n";
assert(!$isCompatible, "TEST 9 FAILED");

$incompatibleMessage = null;
try {
    UnitConversionService::convertBetweenUnits(300, 'ml', 'gram');
} catch (\InvalidArgumentException $e) {
    $incompatibleMessage = $e->getMessage();
}
echo " -> Exception message caught: '{$incompatibleMessage}'\n";
assert(!empty($incompatibleMessage) && str_contains($incompatibleMessage, 'tidak sesuai'), "TEST 9 FAILED");
echo " -> TEST 9 PASSED!\n\n";

// ----------------------------------------------------
// TEST 10: 1 kg Kopi = Rp180.000 -> Harga/gram = Rp180
// ----------------------------------------------------
echo "TEST 10: 1 kg Kopi = Rp180.000 -> Harga/gram = Rp180\n";
$kopiCostPerGram = $kopi->cost_per_base_unit;
echo " -> Computed Cost per gram: Rp{$kopiCostPerGram}\n";
assert($kopiCostPerGram == 180, "TEST 10 FAILED");
echo " -> TEST 10 PASSED!\n\n";

// ----------------------------------------------------
// TEST 11: Resep Coffee Latte 18 gram -> Biaya = Rp3.240
// ----------------------------------------------------
echo "TEST 11: Resep Coffee Latte (18 gram Kopi) -> Biaya = Rp3.240\n";
$coffeeLatteCost = $coffeeLatte->calculateTotalRecipeCost();
echo " -> Computed Coffee Latte Recipe Cost: Rp{$coffeeLatteCost}\n";
assert($coffeeLatteCost == 3240, "TEST 11 FAILED");
echo " -> TEST 11 PASSED!\n\n";

// ----------------------------------------------------
// TEST 12: 1 liter Fresh Milk = Rp25.000 -> Harga/ml = Rp25
// ----------------------------------------------------
echo "TEST 12: 1 liter Fresh Milk = Rp25.000 -> Harga/ml = Rp25\n";
Bahan::where('name_bahan', 'Fresh Milk Test')->delete();
$freshMilkConverted = UnitConversionService::convertToBaseUnit(1, 'liter');
$freshMilk = Bahan::create([
    'user_id' => 1,
    'name_bahan' => 'Fresh Milk Test',
    'unit' => 'liter',
    'purchase_unit' => 'liter',
    'purchase_qty' => 1,
    'base_unit' => $freshMilkConverted['base_unit'],
    'stock' => $freshMilkConverted['amount'],
    'price' => 25000,
    'status' => 'active',
]);
$freshMilkCostPerMl = $freshMilk->cost_per_base_unit;
echo " -> Computed Cost per ml: Rp{$freshMilkCostPerMl}\n";
assert($freshMilkCostPerMl == 25, "TEST 12 FAILED");
echo " -> TEST 12 PASSED!\n\n";

// ----------------------------------------------------
// TEST 13: Resep 100 ml Fresh Milk -> Biaya = Rp2.500
// ----------------------------------------------------
echo "TEST 13: Resep 100 ml Fresh Milk -> Biaya = Rp2.500\n";
Product::where('name_prd', 'Fresh Milk Drink Test')->delete();
$milkDrink = Product::create([
    'category_id' => $category->id,
    'user_id' => 1,
    'name_prd' => 'Fresh Milk Drink Test',
    'code_prd' => 'PRD-FMD',
    'price' => 15000,
    'price_offline' => 15000,
    'price_online' => 18000,
    'sales_type' => 'all',
    'stock' => 100,
]);
$milkDrink->bahans()->attach($freshMilk->id, ['quantity' => 100, 'unit' => 'ml']);
$milkDrinkCost = $milkDrink->calculateTotalRecipeCost();
echo " -> Computed Milk Drink Recipe Cost: Rp{$milkDrinkCost}\n";
assert($milkDrinkCost == 2500, "TEST 13 FAILED");
echo " -> TEST 13 PASSED!\n\n";

// ----------------------------------------------------
// TEST 14: 50 Cup = Rp30.000 -> Harga/pcs = Rp600
// ----------------------------------------------------
echo "TEST 14: 50 Cup = Rp30.000 -> Harga/pcs = Rp600\n";
Bahan::where('name_bahan', 'Cup 50 Pcs Test')->delete();
$cup50Converted = UnitConversionService::convertToBaseUnit(50, 'pcs');
$cup50 = Bahan::create([
    'user_id' => 1,
    'name_bahan' => 'Cup 50 Pcs Test',
    'unit' => 'pcs',
    'purchase_unit' => 'pcs',
    'purchase_qty' => 50,
    'base_unit' => $cup50Converted['base_unit'],
    'stock' => $cup50Converted['amount'],
    'price' => 30000,
    'status' => 'active',
]);
$cupCostPerPcs = $cup50->cost_per_base_unit;
echo " -> Computed Cost per pcs: Rp{$cupCostPerPcs}\n";
assert($cupCostPerPcs == 600, "TEST 14 FAILED");
echo " -> TEST 14 PASSED!\n\n";

// ----------------------------------------------------
// TEST 15: Resep 1 Cup -> Biaya = Rp600
// ----------------------------------------------------
echo "TEST 15: Resep 1 Cup -> Biaya = Rp600\n";
Product::where('name_prd', 'Single Cup Item Test')->delete();
$singleCupItem = Product::create([
    'category_id' => $category->id,
    'user_id' => 1,
    'name_prd' => 'Single Cup Item Test',
    'code_prd' => 'PRD-SCI',
    'price' => 5000,
    'price_offline' => 5000,
    'price_online' => 6000,
    'sales_type' => 'all',
    'stock' => 100,
]);
$singleCupItem->bahans()->attach($cup50->id, ['quantity' => 1, 'unit' => 'pcs']);
$singleCupCost = $singleCupItem->calculateTotalRecipeCost();
echo " -> Computed Single Cup Recipe Cost: Rp{$singleCupCost}\n";
assert($singleCupCost == 600, "TEST 15 FAILED");
echo " -> TEST 15 PASSED!\n\n";

// ----------------------------------------------------
// TEST 16: Multi bahan (Kopi Rp3.240 + Susu Rp2.500 + Gula Rp700 + Cup Rp600 = Total Rp7.040)
// ----------------------------------------------------
echo "TEST 16: Multi Bahan Product Recipe Total Cost (Target: Rp7.040)\n";
Bahan::where('name_bahan', 'Gula Aren Test')->delete();
$gulaConverted = UnitConversionService::convertToBaseUnit(1, 'kg');
$gula = Bahan::create([
    'user_id' => 1,
    'name_bahan' => 'Gula Aren Test',
    'unit' => 'kg',
    'purchase_unit' => 'kg',
    'purchase_qty' => 1,
    'base_unit' => $gulaConverted['base_unit'],
    'stock' => $gulaConverted['amount'],
    'price' => 35000, // Rp35.000 / 1000g = Rp35/g
    'status' => 'active',
]);

Product::where('name_prd', 'Full Latte Special Test')->delete();
$fullLatte = Product::create([
    'category_id' => $category->id,
    'user_id' => 1,
    'name_prd' => 'Full Latte Special Test',
    'code_prd' => 'PRD-FLS',
    'price' => 35000,
    'price_offline' => 35000,
    'price_online' => 40000,
    'sales_type' => 'all',
    'stock' => 100,
]);

$fullLatte->bahans()->attach([
    $kopi->id      => ['quantity' => 18,  'unit' => 'gram'], // 18 * 180 = 3240
    $freshMilk->id => ['quantity' => 100, 'unit' => 'ml'],   // 100 * 25 = 2500
    $gula->id      => ['quantity' => 20,  'unit' => 'gram'], // 20 * 35 = 700
    $cup50->id     => ['quantity' => 1,   'unit' => 'pcs'],  // 1 * 600 = 600
]);

$fullLatteCost = $fullLatte->calculateTotalRecipeCost();
echo " -> Computed Full Latte Recipe Total Cost: Rp{$fullLatteCost} (Expected: Rp7.040)\n";
assert($fullLatteCost == 7040, "TEST 16 FAILED");
echo " -> TEST 16 PASSED!\n\n";

// ----------------------------------------------------
// TEST 17: Penjualan 2 Coffee Latte: stok Kopi berkurang 36 gram & biaya bahan = Rp6.480
// ----------------------------------------------------
echo "TEST 17: Sale of 2 Coffee Latte -> Stock -36g & Total Material Cost = Rp6.480\n";
$kopiBeforeSale = $kopi->fresh()->stock;
$totalMaterialCost = DataShopping::deductMaterialStockForProduct($coffeeLatte->id, 2, 'TRX-TEST-17', 'Sale 2 Coffee Latte');
$kopiAfterSale = $kopi->fresh()->stock;
$stockDeduction = $kopiBeforeSale - $kopiAfterSale;

echo " -> Stock deducted: {$stockDeduction} gram (Expected: 36 gram)\n";
echo " -> Total transaction material cost calculated: Rp{$totalMaterialCost} (Expected: Rp6.480)\n";
assert($stockDeduction == 36, "TEST 17 STOCK DEDUCTION FAILED");
assert($totalMaterialCost == 6480, "TEST 17 COST CALCULATION FAILED");
echo " -> TEST 17 PASSED!\n\n";

// ----------------------------------------------------
// TEST 18: Perubahan harga bahan tidak mengubah histori transaksi lama.
// ----------------------------------------------------
echo "TEST 18: Price Change Does Not Affect Historical Transaction Snapshot\n";

// Get latest movement snapshot created during TEST 17
$lastMovement = BahanStockMovement::where('reference', 'TRX-TEST-17')->first();
$oldSnapshotCost = $lastMovement->cost_per_base_unit;
$oldTotalCost = $lastMovement->total_cost;

echo " -> Historical Movement Snapshot: cost_per_base_unit = Rp{$oldSnapshotCost}, total_cost = Rp{$oldTotalCost}\n";

// Now update Kopi Arabika purchase price from Rp180.000 to Rp200.000 (/kg)
echo " -> Updating Kopi Arabika price from Rp180.000 to Rp200.000\n";
$kopi->update([
    'price' => 200000,
]);
$kopi->refresh();
echo " -> New Kopi Arabika cost per gram: Rp{$kopi->cost_per_base_unit}/gram\n";

// Verify that historical movement record still retains old snapshot cost (180/g and 6480)
$lastMovementAfter = BahanStockMovement::where('reference', 'TRX-TEST-17')->first();
echo " -> Re-checking Historical Snapshot: cost_per_base_unit = Rp{$lastMovementAfter->cost_per_base_unit}, total_cost = Rp{$lastMovementAfter->total_cost}\n";

assert($lastMovementAfter->cost_per_base_unit == 180, "TEST 18 SNAPSHOT COST FAILED");
assert($lastMovementAfter->total_cost == 6480, "TEST 18 SNAPSHOT TOTAL COST FAILED");
echo " -> TEST 18 PASSED!\n\n";

echo "=========================================================\n";
echo "=== ALL 18 MANDATORY TEST CASES PASSED SUCCESSFULLY! ===\n";
echo "=========================================================\n";

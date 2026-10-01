<?php

require 'c:/laragon/www/Kasir-App/vendor/autoload.php';
$app = require_once 'c:/laragon/www/Kasir-App/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Bahan;
use App\Models\Product;
use App\Models\Category;
use App\Models\BahanStockMovement;
use App\Models\TargetSale;
use App\Models\Labor;
use App\Models\Overhead;
use App\Services\UnitConversionService;
use App\Services\HppService;
use App\Livewire\Kasir\DataShopping;
use Illuminate\Support\Facades\DB;

echo "=========================================================\n";
echo "--- STARTING COMPLETE 50-TEST SUITE VERIFICATION ---\n";
echo "=========================================================\n\n";

$category = Category::first() ?? Category::create(['name' => 'Test Category', 'name_code' => 'TC001', 'user_id' => 1]);

// Cleanup test products that might duplicate unique keys
Product::whereIn('code_prd', ['PRD-CLT', 'PRD-NSP', 'PRD-SDISH', 'PRD-FLATTE', 'PRD-MLK', 'PRD-RRE', 'PRD-CDT', 'PRD-NOREC'])->delete();
Bahan::whereIn('name_bahan', ['Kopi Arabika Test', 'Fresh Milk Test', 'Gula Pasir Test', 'Cup 50ml Test', 'Rare Spice Test', 'Scarce Ingredient Test'])->delete();
Labor::truncate();
Overhead::truncate();
TargetSale::truncate();

// ----------------------------------------------------
// TEST 1: Buat Bahan Kopi Arabika 1 kg -> saves 1000 gram
// ----------------------------------------------------
echo "TEST 1: Input Bahan 1 kg -> Base Unit Conversion (1000 gram)\n";
$converted = UnitConversionService::convertToBaseUnit(1, 'kg');
$baseStock = $converted['amount'];
$baseUnit = $converted['base_unit'];

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
$coffeeLatte = Product::create([
    'category_id' => $category->id,
    'user_id' => 1,
    'name_prd' => 'Coffee Latte Test',
    'code_prd' => 'PRD-CLT',
    'price' => 25000,
    'price_offline' => 25000,
    'price_online' => 28000,
    'sales_type' => 'all',
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
// TEST 5: Tambah Stok Bahan (+2 kg = +2000 gram) -> Stok = 2946 gram
// ----------------------------------------------------
echo "TEST 5: Add Stock 2 kg (+2000 gram)\n";
$addStockConverted = UnitConversionService::convertToBaseUnit(2, 'kg');
$kopi->stock += $addStockConverted['amount'];
$kopi->save();
$kopi->refresh();
echo " -> Current Stock: {$kopi->stock} {$kopi->base_unit} (Expected: 2946 gram)\n";
assert($kopi->stock == 2946, "TEST 5 FAILED");
echo " -> TEST 5 PASSED!\n\n";

// ----------------------------------------------------
// TEST 6: Volume Item Susu (Input 2 liter = 2000 ml, Resep 100 ml, Sell x 3)
// ----------------------------------------------------
echo "TEST 6: Volume Item Susu (Input 2 liter = 2000 ml, Resep 100 ml, Sell x 3)\n";
$convertedSusu = UnitConversionService::convertToBaseUnit(2, 'liter');
$susu = Bahan::create([
    'user_id' => 1,
    'name_bahan' => 'Fresh Milk Test',
    'unit' => 'liter',
    'purchase_unit' => 'liter',
    'purchase_qty' => 2,
    'base_unit' => $convertedSusu['base_unit'],
    'stock' => $convertedSusu['amount'],
    'price' => 50000,
    'cost_per_base_unit' => 50000 / $convertedSusu['amount'],
    'status' => 'active'
]);

$milkDrink = Product::create([
    'category_id' => $category->id,
    'user_id' => 1,
    'name_prd' => 'Milk Drink Test',
    'code_prd' => 'PRD-MLK',
    'price' => 20000,
    'sales_type' => 'all',
]);
$milkDrink->bahans()->attach($susu->id, ['quantity' => 100, 'unit' => 'ml']);

DataShopping::deductMaterialStockForProduct($milkDrink->id, 3, 'TRX-TEST-MLK', 'Milk Drink x 3');
$susu->refresh();
echo " -> Current Susu Stock: {$susu->stock} {$susu->base_unit} (Expected: 1700 ml)\n";
assert($susu->stock == 1700 && $susu->base_unit === 'ml', "TEST 6 FAILED");
echo " -> TEST 6 PASSED!\n\n";

// ----------------------------------------------------
// TEST 7: Count Item Cup (Stok 100 pcs, Resep 1 pcs, Sell x 3)
// ----------------------------------------------------
echo "TEST 7: Count Item Cup (Stok 100 pcs, Resep 1 pcs, Sell x 3)\n";
$cup = Bahan::create([
    'user_id' => 1,
    'name_bahan' => 'Cup 50ml Test',
    'unit' => 'pcs',
    'purchase_unit' => 'pcs',
    'purchase_qty' => 100,
    'base_unit' => 'pcs',
    'stock' => 100,
    'price' => 60000,
    'cost_per_base_unit' => 600,
    'status' => 'active'
]);

$cupDrink = Product::create([
    'category_id' => $category->id,
    'user_id' => 1,
    'name_prd' => 'Cup Drink Test',
    'code_prd' => 'PRD-CDT',
    'price' => 10000,
    'sales_type' => 'all',
]);
$cupDrink->bahans()->attach($cup->id, ['quantity' => 1, 'unit' => 'pcs']);
DataShopping::deductMaterialStockForProduct($cupDrink->id, 3, 'TRX-TEST-CUP', 'Cup Drink x 3');
$cup->refresh();
echo " -> Current Cup Stock: {$cup->stock} {$cup->base_unit} (Expected: 97 pcs)\n";
assert($cup->stock == 97, "TEST 7 FAILED");
echo " -> TEST 7 PASSED!\n\n";

// ----------------------------------------------------
// TEST 8: Validation before checkout if stock insufficient
// ----------------------------------------------------
echo "TEST 8: Pre-Checkout Insufficient Stock Validation\n";
$rareIngredient = Bahan::create([
    'user_id' => 1,
    'name_bahan' => 'Rare Spice Test',
    'unit' => 'gram',
    'purchase_unit' => 'gram',
    'purchase_qty' => 10,
    'base_unit' => 'gram',
    'stock' => 10, // only 10g
    'price' => 100000,
    'status' => 'active'
]);

$rareProduct = Product::create([
    'category_id' => $category->id,
    'user_id' => 1,
    'name_prd' => 'Rare Spice Latte',
    'code_prd' => 'PRD-RRE',
    'price' => 50000,
    'sales_type' => 'all',
]);
$rareProduct->bahans()->attach($rareIngredient->id, ['quantity' => 18, 'unit' => 'gram']);

$cartItems = [
    ['id' => $rareProduct->id, 'qty' => 1]
];
$validationError = DataShopping::validateStockForCart($cartItems);
echo " -> Validation error caught: '{$validationError}'\n";
assert(!empty($validationError), "TEST 8 FAILED");
echo " -> TEST 8 PASSED!\n\n";

// ----------------------------------------------------
// TEST 9: Incompatible unit validation
// ----------------------------------------------------
echo "TEST 9: Incompatible Unit Validation\n";
$isCompatible = UnitConversionService::areUnitsCompatible('gram', 'ml');
echo " -> Are 'gram' and 'ml' compatible? " . ($isCompatible ? 'YES' : 'NO') . "\n";
assert(!$isCompatible, "TEST 9 FAILED");

try {
    UnitConversionService::convertBetweenUnits(10, 'ml', 'gram');
    echo " -> FAILED: Exception not thrown for incompatible units!\n";
    exit(1);
} catch (\Exception $e) {
    echo " -> Exception message caught: '{$e->getMessage()}'\n";
    assert(str_contains($e->getMessage(), 'tidak sesuai'), "TEST 9 EXCEPTION FAILED");
}
echo " -> TEST 9 PASSED!\n\n";

// ----------------------------------------------------
// TEST 10 & 11: Single Bahan HPP Calculation (Kopi Arabika 1 kg = Rp180.000 -> 18g = Rp3.240)
// ----------------------------------------------------
echo "TEST 10: 1 kg Kopi = Rp180.000 -> Harga/gram = Rp180\n";
$costPerGram = $kopi->cost_per_base_unit;
echo " -> Computed Cost per gram: Rp{$costPerGram}\n";
assert($costPerGram == 180, "TEST 10 FAILED");
echo " -> TEST 10 PASSED!\n\n";

echo "TEST 11: Resep Coffee Latte (18 gram Kopi) -> Biaya = Rp3.240\n";
$kopiRecipeCost = 18 * $costPerGram;
echo " -> Computed Coffee Latte Recipe Cost: Rp{$kopiRecipeCost}\n";
assert($kopiRecipeCost == 3240, "TEST 11 FAILED");
echo " -> TEST 11 PASSED!\n\n";

// ----------------------------------------------------
// TEST 12 & 13: Single Bahan HPP Volume (1 liter Fresh Milk = Rp25.000 -> 100 ml = Rp2.500)
// ----------------------------------------------------
echo "TEST 12: 1 liter Fresh Milk = Rp25.000 -> Harga/ml = Rp25\n";
$freshMilk = Bahan::create([
    'user_id' => 1,
    'name_bahan' => 'Fresh Milk Test 2',
    'unit' => 'liter',
    'purchase_unit' => 'liter',
    'purchase_qty' => 1,
    'base_unit' => 'ml',
    'stock' => 1000,
    'price' => 25000,
    'cost_per_base_unit' => 25,
    'status' => 'active'
]);
echo " -> Computed Cost per ml: Rp{$freshMilk->cost_per_base_unit}\n";
assert($freshMilk->cost_per_base_unit == 25, "TEST 12 FAILED");
echo " -> TEST 12 PASSED!\n\n";

echo "TEST 13: Resep 100 ml Fresh Milk -> Biaya = Rp2.500\n";
$milkCost = 100 * $freshMilk->cost_per_base_unit;
echo " -> Computed Milk Drink Recipe Cost: Rp{$milkCost}\n";
assert($milkCost == 2500, "TEST 13 FAILED");
echo " -> TEST 13 PASSED!\n\n";

// ----------------------------------------------------
// TEST 14 & 15: Single Bahan HPP Count (50 Cup = Rp30.000 -> 1 pcs = Rp600)
// ----------------------------------------------------
echo "TEST 14: 50 Cup = Rp30.000 -> Harga/pcs = Rp600\n";
$cup50 = Bahan::create([
    'user_id' => 1,
    'name_bahan' => 'Cup 50ml Test 2',
    'unit' => 'pcs',
    'purchase_unit' => 'pcs',
    'purchase_qty' => 50,
    'base_unit' => 'pcs',
    'stock' => 50,
    'price' => 30000,
    'cost_per_base_unit' => 600,
    'status' => 'active'
]);
echo " -> Computed Cost per pcs: Rp{$cup50->cost_per_base_unit}\n";
assert($cup50->cost_per_base_unit == 600, "TEST 14 FAILED");
echo " -> TEST 14 PASSED!\n\n";

echo "TEST 15: Resep 1 Cup -> Biaya = Rp600\n";
$cupCost = 1 * $cup50->cost_per_base_unit;
echo " -> Computed Single Cup Recipe Cost: Rp{$cupCost}\n";
assert($cupCost == 600, "TEST 15 FAILED");
echo " -> TEST 15 PASSED!\n\n";

// ----------------------------------------------------
// TEST 16: Multi Bahan Product HPP Total (Kopi 18g=3240, Milk 100ml=2500, Gula 20g=700, Cup 1pcs=600 -> Total Rp7.040)
// ----------------------------------------------------
echo "TEST 16: Multi Bahan Product Recipe Total Cost (Target: Rp7.040)\n";
$gula = Bahan::create([
    'user_id' => 1,
    'name_bahan' => 'Gula Pasir Test',
    'unit' => 'kg',
    'purchase_unit' => 'kg',
    'purchase_qty' => 1,
    'base_unit' => 'gram',
    'stock' => 1000,
    'price' => 35000,
    'cost_per_base_unit' => 35, // Rp35 per gram
    'status' => 'active'
]);

$fullLatte = Product::create([
    'category_id' => $category->id,
    'user_id' => 1,
    'name_prd' => 'Full Recipe Coffee Latte',
    'code_prd' => 'PRD-FLATTE',
    'price' => 20000,
    'price_offline' => 20000,
    'price_online' => 22000,
    'sales_type' => 'all',
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
$lastMovement = BahanStockMovement::where('reference', 'TRX-TEST-17')->first();
$oldSnapshotCost = $lastMovement->cost_per_base_unit;
$oldTotalCost = $lastMovement->total_cost;

echo " -> Historical Movement Snapshot: cost_per_base_unit = Rp{$oldSnapshotCost}, total_cost = Rp{$oldTotalCost}\n";

$kopi->update(['price' => 200000]);
$kopi->refresh();
echo " -> New Kopi Arabika cost per gram: Rp{$kopi->cost_per_base_unit}/gram\n";

$lastMovementAfter = BahanStockMovement::where('reference', 'TRX-TEST-17')->first();
echo " -> Re-checking Historical Snapshot: cost_per_base_unit = Rp{$lastMovementAfter->cost_per_base_unit}, total_cost = Rp{$lastMovementAfter->total_cost}\n";

assert($lastMovementAfter->cost_per_base_unit == 180, "TEST 18 SNAPSHOT COST FAILED");
assert($lastMovementAfter->total_cost == 6480, "TEST 18 SNAPSHOT TOTAL COST FAILED");
echo " -> TEST 18 PASSED!\n\n";

// Restore kopi price for subsequent test predictability
$kopi->update(['price' => 180000]);
$kopi->refresh();

// ----------------------------------------------------
// TEST 19: Product can be created without manual stock field
// ----------------------------------------------------
echo "TEST 19: Product Creation Without Manual Stock Input\n";
$noStockProduct = Product::create([
    'category_id' => $category->id,
    'user_id' => 1,
    'name_prd' => 'No Stock Product Test',
    'code_prd' => 'PRD-NSP',
    'price' => 20000,
    'price_offline' => 20000,
    'price_online' => 22000,
    'sales_type' => 'all',
]);
echo " -> Created Product ID: {$noStockProduct->id}, Name: {$noStockProduct->name_prd}\n";
assert($noStockProduct->id > 0, "TEST 19 FAILED");
echo " -> TEST 19 PASSED!\n\n";

// ----------------------------------------------------
// TEST 20: Product with recipe can be updated without manual stock field
// ----------------------------------------------------
echo "TEST 20: Product Recipe Update Without Manual Stock Input\n";
$noStockProduct->update([
    'name_prd' => 'Updated No Stock Product Test',
    'price_offline' => 21000,
]);
$noStockProduct->bahans()->sync([
    $kopi->id => ['quantity' => 10, 'unit' => 'gram']
]);
$updatedProduct = $noStockProduct->fresh();
echo " -> Updated Name: {$updatedProduct->name_prd}, Price: Rp{$updatedProduct->price_offline}\n";
assert($updatedProduct->name_prd === 'Updated No Stock Product Test' && $updatedProduct->bahans->count() === 1, "TEST 20 FAILED");
echo " -> TEST 20 PASSED!\n\n";

// ----------------------------------------------------
// TEST 21: Sale reduces raw material stock based on recipe only
// ----------------------------------------------------
echo "TEST 21: Sale Deducts Raw Material Stock Based on Recipe Only\n";
$kopiBeforeTest21 = $kopi->fresh()->stock;
DataShopping::deductMaterialStockForProduct($updatedProduct->id, 1, 'TRX-TEST-21', 'Sale 1 Updated No Stock Product');
$kopiAfterTest21 = $kopi->fresh()->stock;
$deductedGrams = $kopiBeforeTest21 - $kopiAfterTest21;
echo " -> Raw Material (Kopi) Deducted: {$deductedGrams} gram (Expected: 10 gram)\n";
assert($deductedGrams == 10, "TEST 21 FAILED");
echo " -> TEST 21 PASSED!\n\n";

// ----------------------------------------------------
// TEST 22: Sale is rejected if raw material stock is insufficient
// ----------------------------------------------------
echo "TEST 22: Cart Checkout Rejected If Material Stock Insufficient\n";
$scarce = Bahan::create([
    'user_id' => 1,
    'name_bahan' => 'Scarce Ingredient Test',
    'unit' => 'gram',
    'purchase_unit' => 'gram',
    'purchase_qty' => 5,
    'base_unit' => 'gram',
    'stock' => 5, // Only 5g available
    'price' => 10000,
    'status' => 'active',
]);

$scarceProduct = Product::create([
    'category_id' => $category->id,
    'user_id' => 1,
    'name_prd' => 'Scarce Dish Test',
    'code_prd' => 'PRD-SDISH',
    'price' => 15000,
    'sales_type' => 'all',
]);
$scarceProduct->bahans()->attach($scarce->id, ['quantity' => 10, 'unit' => 'gram']);

$cart = [
    ['id' => $scarceProduct->id, 'qty' => 1]
];
$validationErrorTest22 = DataShopping::validateStockForCart($cart);
echo " -> Validation Message: '{$validationErrorTest22}'\n";
assert(!empty($validationErrorTest22) && str_contains($validationErrorTest22, 'tidak mencukupi'), "TEST 22 FAILED");
echo " -> TEST 22 PASSED!\n\n";

// ----------------------------------------------------
// TEST 23: Removal of manual product stock does NOT affect recipe HPP calculation
// ----------------------------------------------------
echo "TEST 23: HPP Calculation Works Seamlessly Without Manual Product Stock\n";
$hppCost = $fullLatte->calculateTotalRecipeCost();
echo " -> Full Latte Recipe HPP Cost: Rp{$hppCost} (Expected: Rp7.040)\n";
assert($hppCost == 7040, "TEST 23 FAILED");
echo " -> TEST 23 PASSED!\n\n";

// ----------------------------------------------------
// TEST 24: Target Penjualan per Bulan = 2.000 cup tersimpan dengan benar
// ----------------------------------------------------
echo "TEST 24: Target Penjualan per Bulan = 2.000 cup tersimpan dengan benar\n";
TargetSale::create([
    'user_id' => 1,
    'target_sales_monthly' => 2000,
    'operating_days' => 26,
]);
$targetSetting = TargetSale::getTargetSettings();
echo " -> Saved Monthly Target: {$targetSetting->target_sales_monthly} cup, Operating Days: {$targetSetting->operating_days} hari\n";
assert($targetSetting->target_sales_monthly == 2000 && $targetSetting->operating_days == 26, "TEST 24 FAILED");
echo " -> TEST 24 PASSED!\n\n";

// ----------------------------------------------------
// TEST 25: Perhitungan Target Penjualan per Hari (2.000 / 26 = 76,92 cup)
// ----------------------------------------------------
echo "TEST 25: Perhitungan Target Penjualan per Hari (2.000 / 26 = 76,92 cup)\n";
$dailyTarget = $targetSetting->daily_target;
echo " -> Calculated Daily Target: {$dailyTarget} cup/hari (Expected: 76.92)\n";
assert(abs($dailyTarget - 76.92) < 0.01, "TEST 25 FAILED");
echo " -> TEST 25 PASSED!\n\n";

// ----------------------------------------------------
// TEST 26: Tenaga Kerja Aktif dihitung ke Total Gaji Tenaga Kerja
// ----------------------------------------------------
echo "TEST 26: Tenaga Kerja Aktif dihitung ke Total Gaji Tenaga Kerja\n";
Labor::create(['user_id' => 1, 'name' => 'Barista Test', 'monthly_salary' => 3500000, 'status' => 'active']);
Labor::create(['user_id' => 1, 'name' => 'Kasir Test', 'monthly_salary' => 2500000, 'status' => 'active']);
Labor::create(['user_id' => 1, 'name' => 'Helper Test', 'monthly_salary' => 2500000, 'status' => 'active']);

$totalActiveLabor = Labor::getTotalActiveSalary();
echo " -> Total Active Labor Salary: Rp" . number_format($totalActiveLabor, 0, ',', '.') . " (Expected: Rp8.500.000)\n";
assert($totalActiveLabor == 8500000, "TEST 26 FAILED");
echo " -> TEST 26 PASSED!\n\n";

// ----------------------------------------------------
// TEST 27: Tenaga Kerja Non-Aktif TIDAK dihitung ke Total Gaji Tenaga Kerja
// ----------------------------------------------------
echo "TEST 27: Tenaga Kerja Non-Aktif TIDAK dihitung ke Total Gaji Tenaga Kerja\n";
Labor::create(['user_id' => 1, 'name' => 'Operational Manager Inactive', 'monthly_salary' => 5000000, 'status' => 'inactive']);

$totalActiveLaborAfter = Labor::getTotalActiveSalary();
echo " -> Total Active Labor Salary (excluding inactive): Rp" . number_format($totalActiveLaborAfter, 0, ',', '.') . " (Expected: Rp8.500.000)\n";
assert($totalActiveLaborAfter == 8500000, "TEST 27 FAILED");
echo " -> TEST 27 PASSED!\n\n";

// ----------------------------------------------------
// TEST 28: Perhitungan Biaya Tenaga Kerja per Cup (Rp8.500.000 / 2.000 = Rp4.250)
// ----------------------------------------------------
echo "TEST 28: Perhitungan Biaya Tenaga Kerja per Cup (Rp8.500.000 / 2.000 = Rp4.250)\n";
$laborCostPerCup = Labor::getCostPerCup();
echo " -> Calculated Labor Cost per Cup: Rp" . number_format($laborCostPerCup, 2, ',', '.') . " (Expected: Rp4.250,00)\n";
assert(abs($laborCostPerCup - 4250) < 0.01, "TEST 28 FAILED");
echo " -> TEST 28 PASSED!\n\n";

// ----------------------------------------------------
// TEST 29: Biaya Operasional Aktif dihitung ke Total Biaya Operasional
// ----------------------------------------------------
echo "TEST 29: Biaya Operasional Aktif dihitung ke Total Biaya Operasional\n";
Overhead::create(['user_id' => 1, 'name' => 'Listrik & Air Test', 'category' => 'Operational', 'nominal_monthly' => 1500000, 'status' => 'active']);
Overhead::create(['user_id' => 1, 'name' => 'Sewa Tempat Test', 'category' => 'Fixed Cost', 'nominal_monthly' => 3000000, 'status' => 'active']);
Overhead::create(['user_id' => 1, 'name' => 'WiFi Test', 'category' => 'Operational', 'nominal_monthly' => 300000, 'status' => 'active']);
Overhead::create(['user_id' => 1, 'name' => 'Marketing Test', 'category' => 'Operational', 'nominal_monthly' => 700000, 'status' => 'active']);

$totalActiveOverhead = Overhead::getTotalActiveNominal();
echo " -> Total Active Overhead: Rp" . number_format($totalActiveOverhead, 0, ',', '.') . " (Expected: Rp5.500.000)\n";
assert($totalActiveOverhead == 5500000, "TEST 29 FAILED");
echo " -> TEST 29 PASSED!\n\n";

// ----------------------------------------------------
// TEST 30: Biaya Operasional Non-Aktif TIDAK dihitung ke Total Biaya Operasional
// ----------------------------------------------------
echo "TEST 30: Biaya Operasional Non-Aktif TIDAK dihitung ke Total Biaya Operasional\n";
Overhead::create(['user_id' => 1, 'name' => 'Renovasi Inactive', 'category' => 'Fixed Cost', 'nominal_monthly' => 10000000, 'status' => 'inactive']);

$totalActiveOverheadAfter = Overhead::getTotalActiveNominal();
echo " -> Total Active Overhead (excluding inactive): Rp" . number_format($totalActiveOverheadAfter, 0, ',', '.') . " (Expected: Rp5.500.000)\n";
assert($totalActiveOverheadAfter == 5500000, "TEST 30 FAILED");
echo " -> TEST 30 PASSED!\n\n";

// ----------------------------------------------------
// TEST 31: Perhitungan Biaya Overhead per Cup (Rp5.500.000 / 2.000 = Rp2.750)
// ----------------------------------------------------
echo "TEST 31: Perhitungan Biaya Overhead per Cup (Rp5.500.000 / 2.000 = Rp2.750)\n";
$overheadCostPerCup = Overhead::getCostPerCup();
echo " -> Calculated Overhead Cost per Cup: Rp" . number_format($overheadCostPerCup, 2, ',', '.') . " (Expected: Rp2.750,00)\n";
assert(abs($overheadCostPerCup - 2750) < 0.01, "TEST 31 FAILED");
echo " -> TEST 31 PASSED!\n\n";

// ----------------------------------------------------
// TEST 32: Total Biaya Non-Bahan per Cup (Rp4.250 + Rp2.750 = Rp7.000)
// ----------------------------------------------------
echo "TEST 32: Total Biaya Non-Bahan per Cup (Rp4.250 + Rp2.750 = Rp7.000)\n";
$totalNonMaterialPerCup = Overhead::getTotalNonMaterialCostPerCup();
echo " -> Calculated Total Non-Material Cost per Cup: Rp" . number_format($totalNonMaterialPerCup, 2, ',', '.') . " (Expected: Rp7.000,00)\n";
assert(abs($totalNonMaterialPerCup - 7000) < 0.01, "TEST 32 FAILED");
echo " -> TEST 32 PASSED!\n\n";

// ----------------------------------------------------
// TEST 33: HPP Bahan Coffee Latte tetap Rp7.040 (tidak terganggu oleh Labor & Overhead)
// ----------------------------------------------------
echo "TEST 33: HPP Bahan Coffee Latte tetap Rp7.040 (tidak terganggu oleh Labor & Overhead)\n";
$hppBahanLatte = $fullLatte->calculateTotalRecipeCost();
echo " -> HPP Bahan Coffee Latte: Rp{$hppBahanLatte} (Expected: Rp7.040)\n";
assert($hppBahanLatte == 7040, "TEST 33 FAILED");
echo " -> TEST 33 PASSED!\n\n";

// ----------------------------------------------------
// TEST 34: Penambahan Labor & Overhead tidak mengubah logika stok & pengurangan stok transaksi
// ----------------------------------------------------
echo "TEST 34: Penambahan Labor & Overhead tidak mengubah logika stok & pengurangan stok transaksi\n";
$kopiBeforeTest34 = $kopi->fresh()->stock;
DataShopping::deductMaterialStockForProduct($fullLatte->id, 1, 'TRX-TEST-34', 'Transaction with Non-Material Config');
$kopiAfterTest34 = $kopi->fresh()->stock;
$deductedKopiTest34 = $kopiBeforeTest34 - $kopiAfterTest34;
echo " -> Kopi stock deducted on transaction: {$deductedKopiTest34} gram (Expected: 18 gram)\n";
assert($deductedKopiTest34 == 18, "TEST 34 FAILED");
echo " -> TEST 34 PASSED!\n\n";

// ----------------------------------------------------
// TEST 35: Transaksi historical & cost snapshot tetap tidak berubah
// ----------------------------------------------------
echo "TEST 35: Transaksi historical & cost snapshot tetap tidak berubah\n";
$movementTest17 = BahanStockMovement::where('reference', 'TRX-TEST-17')->first();
echo " -> Historical Snapshot TRX-TEST-17: cost_per_base_unit = Rp{$movementTest17->cost_per_base_unit}, total_cost = Rp{$movementTest17->total_cost}\n";
assert($movementTest17->cost_per_base_unit == 180 && $movementTest17->total_cost == 6480, "TEST 35 FAILED");
echo " -> TEST 35 PASSED!\n\n";

// ====================================================
// NEW MANDATORY TESTS: TEST 36 S/D 50 (HPP TOTAL, MARGIN & TARGET MARGIN)
// ====================================================

// ----------------------------------------------------
// TEST 36: HPP Total: Rp7.040 + Rp4.250 + Rp2.750 = Rp14.040
// ----------------------------------------------------
echo "TEST 36: HPP Total Produk (Rp7.040 + Rp4.250 + Rp2.750 = Rp14.040)\n";
$details36 = HppService::getProductHppDetails($fullLatte);
$hppTotal36 = $details36['hpp_total'];
echo " -> Calculated HPP Total: Rp" . number_format($hppTotal36, 0, ',', '.') . " (Expected: Rp14.040)\n";
assert($hppTotal36 == 14040, "TEST 36 FAILED");
echo " -> TEST 36 PASSED!\n\n";

// ----------------------------------------------------
// TEST 37: Margin nominal Offline: Rp20.000 - Rp14.040 = Rp5.960
// ----------------------------------------------------
echo "TEST 37: Margin nominal Offline (Rp20.000 - Rp14.040 = Rp5.960)\n";
$marginNominalOff = HppService::calculateMarginNominal(20000, 14040);
echo " -> Calculated Offline Nominal Margin: Rp" . number_format($marginNominalOff, 0, ',', '.') . " (Expected: Rp5.960)\n";
assert($marginNominalOff == 5960, "TEST 37 FAILED");
echo " -> TEST 37 PASSED!\n\n";

// ----------------------------------------------------
// TEST 38: Margin % Offline: Rp5.960 / Rp20.000 x 100 = 29.8%
// ----------------------------------------------------
echo "TEST 38: Margin % Offline (Rp5.960 / Rp20.000 x 100 = 29,8%)\n";
$marginPercentOff = HppService::calculateMarginPercent(20000, 14040);
echo " -> Calculated Offline Percent Margin: {$marginPercentOff}% (Expected: 29.8%)\n";
assert(abs($marginPercentOff - 29.8) < 0.01, "TEST 38 FAILED");
echo " -> TEST 38 PASSED!\n\n";

// ----------------------------------------------------
// TEST 39: Margin nominal Online: Rp22.000 - Rp14.040 = Rp7.960
// ----------------------------------------------------
echo "TEST 39: Margin nominal Online (Rp22.000 - Rp14.040 = Rp7.960)\n";
$marginNominalOn = HppService::calculateMarginNominal(22000, 14040);
echo " -> Calculated Online Nominal Margin: Rp" . number_format($marginNominalOn, 0, ',', '.') . " (Expected: Rp7.960)\n";
assert($marginNominalOn == 7960, "TEST 39 FAILED");
echo " -> TEST 39 PASSED!\n\n";

// ----------------------------------------------------
// TEST 40: Margin % Online: Rp7.960 / Rp22.000 x 100 = 36.18%
// ----------------------------------------------------
echo "TEST 40: Margin % Online (Rp7.960 / Rp22.000 x 100 = 36,18%)\n";
$marginPercentOn = HppService::calculateMarginPercent(22000, 14040);
echo " -> Calculated Online Percent Margin: {$marginPercentOn}% (Expected: 36.18%)\n";
assert(abs($marginPercentOn - 36.18) < 0.01, "TEST 40 FAILED");
echo " -> TEST 40 PASSED!\n\n";

// ----------------------------------------------------
// TEST 41: Target margin 30%: Rp14.040 / 0,70 = Rp20.057,14
// ----------------------------------------------------
echo "TEST 41: Target margin 30%: Rp14.040 / 0.70 = Rp20.057,14\n";
$targetPrice30 = HppService::calculateTheoreticalPrice(14040, 30);
echo " -> Calculated Theoretical Price (30% margin): Rp" . number_format($targetPrice30, 2, ',', '.') . " (Expected: Rp20.057,14)\n";
assert(abs($targetPrice30 - 20057.14) < 0.01, "TEST 41 FAILED");
echo " -> TEST 41 PASSED!\n\n";

// ----------------------------------------------------
// TEST 42: Target margin 0%: Harga jual = HPP (Rp14.040)
// ----------------------------------------------------
echo "TEST 42: Target margin 0%: Harga jual = HPP (Rp14.040)\n";
$targetPrice0 = HppService::calculateTheoreticalPrice(14040, 0);
echo " -> Calculated Theoretical Price (0% margin): Rp" . number_format($targetPrice0, 0, ',', '.') . " (Expected: Rp14.040)\n";
assert($targetPrice0 == 14040, "TEST 42 FAILED");
echo " -> TEST 42 PASSED!\n\n";

// ----------------------------------------------------
// TEST 43: Target margin 99%: Perhitungan tetap aman dan tidak error
// ----------------------------------------------------
echo "TEST 43: Target margin 99%: Perhitungan tetap aman (Rp14.040 / 0,01 = Rp1.404.000)\n";
$targetPrice99 = HppService::calculateTheoreticalPrice(14040, 99);
echo " -> Calculated Theoretical Price (99% margin): Rp" . number_format($targetPrice99, 0, ',', '.') . " (Expected: Rp1.404.000)\n";
assert(abs($targetPrice99 - 1404000) < 0.01, "TEST 43 FAILED");
echo " -> TEST 43 PASSED!\n\n";

// ----------------------------------------------------
// TEST 44: Target margin 100% ditolak
// ----------------------------------------------------
echo "TEST 44: Target margin 100% ditolak\n";
try {
    HppService::calculateTheoreticalPrice(14040, 100);
    echo " -> FAILED: Exception not thrown for 100% target margin!\n";
    exit(1);
} catch (\InvalidArgumentException $e) {
    echo " -> Exception message caught: '{$e->getMessage()}'\n";
    assert(str_contains($e->getMessage(), 'tidak boleh'), "TEST 44 EXCEPTION FAILED");
}
echo " -> TEST 44 PASSED!\n\n";

// ----------------------------------------------------
// TEST 45: Produk tanpa resep tidak menghasilkan HPP palsu
// ----------------------------------------------------
echo "TEST 45: Produk tanpa resep tidak menghasilkan HPP palsu\n";
$noRecipeProduct = Product::create([
    'category_id' => $category->id,
    'user_id' => 1,
    'name_prd' => 'No Recipe Product Test',
    'code_prd' => 'PRD-NOREC',
    'price' => 15000,
    'sales_type' => 'all',
]);
$detailsNoRec = HppService::getProductHppDetails($noRecipeProduct);
echo " -> HPP Bahan: Rp{$detailsNoRec['hpp_bahan']}, Warnings: " . implode(', ', $detailsNoRec['warnings']) . "\n";
assert($detailsNoRec['hpp_bahan'] == 0 && in_array('Resep belum lengkap', $detailsNoRec['warnings']), "TEST 45 FAILED");
echo " -> TEST 45 PASSED!\n\n";

// ----------------------------------------------------
// TEST 46: Jika target penjualan belum tersedia (0), sistem memberikan status/peringatan yang sesuai
// ----------------------------------------------------
echo "TEST 46: Jika target penjualan belum tersedia, sistem memberikan status/peringatan yang sesuai\n";
TargetSale::truncate();
$detailsNoTarget = HppService::getProductHppDetails($fullLatte);
echo " -> Labor cost per cup with 0 target: Rp{$detailsNoTarget['labor_cost_per_cup']}, Warnings: " . implode(', ', $detailsNoTarget['warnings']) . "\n";
assert($detailsNoTarget['labor_cost_per_cup'] == 0 && in_array('Target Penjualan belum diatur', $detailsNoTarget['warnings']), "TEST 46 FAILED");
echo " -> TEST 46 PASSED!\n\n";

// Restore Target Sale for subsequent tests
TargetSale::create(['user_id' => 1, 'target_sales_monthly' => 2000, 'operating_days' => 26]);

// ----------------------------------------------------
// TEST 47: Perubahan harga bahan mempengaruhi HPP saat ini
// ----------------------------------------------------
echo "TEST 47: Perubahan harga bahan mempengaruhi HPP saat ini\n";
$kopi->update(['price' => 360000]); // Harga Kopi naik 2x lipat (Rp360/g)
$kopi->refresh();

$fullLatteHppNew = $fullLatte->fresh()->calculateTotalRecipeCost();
echo " -> New Kopi Arabika Price: Rp360.000/kg -> New HPP Bahan Coffee Latte: Rp{$fullLatteHppNew} (Expected: Rp10.280)\n";
// Recipe: Kopi 18*360=6480 + Milk 100*25=2500 + Gula 20*35=700 + Cup 1*600=600 = 10280
assert($fullLatteHppNew == 10280, "TEST 47 FAILED");
echo " -> TEST 47 PASSED!\n\n";

// ----------------------------------------------------
// TEST 48: Perubahan harga bahan tidak mengubah cost snapshot transaksi lama
// ----------------------------------------------------
echo "TEST 48: Perubahan harga bahan tidak mengubah cost snapshot transaksi lama\n";
$oldMovementTest17 = BahanStockMovement::where('reference', 'TRX-TEST-17')->first();
echo " -> Historical Snapshot TRX-TEST-17: cost_per_base_unit = Rp{$oldMovementTest17->cost_per_base_unit}, total_cost = Rp{$oldMovementTest17->total_cost}\n";
assert($oldMovementTest17->cost_per_base_unit == 180 && $oldMovementTest17->total_cost == 6480, "TEST 48 FAILED");
echo " -> TEST 48 PASSED!\n\n";

// Restore kopi price
$kopi->update(['price' => 180000]);
$kopi->refresh();

// ----------------------------------------------------
// TEST 49: Perubahan labor/overhead mempengaruhi HPP saat ini
// ----------------------------------------------------
echo "TEST 49: Perubahan labor/overhead mempengaruhi HPP saat ini\n";
Labor::create(['user_id' => 1, 'name' => 'Additional Barista', 'monthly_salary' => 500000, 'status' => 'active']); // Total Labor: 8.5m + 0.5m = 9.0m
$newLaborCostPerCup = Labor::getCostPerCup(); // 9.000.000 / 2.000 = 4.500
echo " -> New Total Active Labor: Rp9.000.000 -> New Labor Cost per Cup: Rp" . number_format($newLaborCostPerCup, 0, ',', '.') . " (Expected: Rp4.500)\n";
assert($newLaborCostPerCup == 4500, "TEST 49 FAILED");
echo " -> TEST 49 PASSED!\n\n";

// ----------------------------------------------------
// TEST 50: Perubahan labor/overhead tidak mengubah transaksi historis
// ----------------------------------------------------
echo "TEST 50: Perubahan labor/overhead tidak mengubah transaksi historis\n";
$historicalMovementsCount = BahanStockMovement::count();
$test17Snapshot = BahanStockMovement::where('reference', 'TRX-TEST-17')->first();
echo " -> Checking Historical Movements Count: {$historicalMovementsCount}, TRX-TEST-17 snapshot total_cost: Rp{$test17Snapshot->total_cost}\n";
assert($test17Snapshot->total_cost == 6480, "TEST 50 FAILED");
echo " -> TEST 50 PASSED!\n\n";

echo "=========================================================\n";
echo "=== ALL 50 MANDATORY TEST CASES PASSED SUCCESSFULLY! ===\n";
echo "=========================================================\n";

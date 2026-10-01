<?php

namespace Tests\Feature;

use App\Models\Bahan;
use App\Models\BahanStockMovement;
use App\Models\User;
use App\Services\UnitConversionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaterialStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_material_with_base_unit_conversion(): void
    {
        $user = User::create([
            'name' => 'Admin Test',
            'username' => 'admintest',
            'code' => 'ADM001',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'Admin',
        ]);

        $converted = UnitConversionService::convertToBaseUnit(1, 'kg');

        $bahan = Bahan::create([
            'user_id' => $user->id,
            'name_bahan' => 'Kopi Arabika Test',
            'unit' => 'kg',
            'purchase_unit' => 'kg',
            'purchase_qty' => 1,
            'base_unit' => $converted['base_unit'],
            'stock' => $converted['amount'],
            'price' => 180000,
            'cost_per_base_unit' => 180000 / $converted['amount'],
            'description' => 'Bahan Test',
            'status' => 'active'
        ]);

        $this->assertEquals(1000, $bahan->stock);
        $this->assertEquals('gram', $bahan->base_unit);
        $this->assertEquals(180, $bahan->cost_per_base_unit);
    }

    public function test_stock_movement_recording(): void
    {
        $user = User::create([
            'name' => 'Admin Test',
            'username' => 'admintest2',
            'code' => 'ADM002',
            'email' => 'admin2@test.com',
            'password' => bcrypt('password'),
            'role' => 'Admin',
        ]);

        $bahan = Bahan::create([
            'user_id' => $user->id,
            'name_bahan' => 'Fresh Milk Test',
            'unit' => 'liter',
            'purchase_unit' => 'liter',
            'purchase_qty' => 2,
            'base_unit' => 'ml',
            'stock' => 2000,
            'price' => 50000,
            'cost_per_base_unit' => 25,
            'status' => 'active'
        ]);

        $movement = BahanStockMovement::create([
            'bahan_id' => $bahan->id,
            'user_id' => $user->id,
            'type' => 'in',
            'qty' => 2000,
            'stock_before' => 0,
            'stock_after' => 2000,
            'reference' => 'INIT-STOCK',
            'notes' => 'Stok awal 2 liter',
        ]);

        $this->assertDatabaseHas('bahan_stock_movements', [
            'id' => $movement->id,
            'bahan_id' => $bahan->id,
            'type' => 'in',
            'qty' => 2000,
        ]);
    }
}

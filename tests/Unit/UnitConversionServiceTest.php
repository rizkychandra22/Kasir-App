<?php

namespace Tests\Unit;

use App\Services\UnitConversionService;
use PHPUnit\Framework\TestCase;

class UnitConversionServiceTest extends TestCase
{
    public function test_get_base_unit_correctly_identifies_base(): void
    {
        $this->assertEquals('gram', UnitConversionService::getBaseUnit('kg'));
        $this->assertEquals('gram', UnitConversionService::getBaseUnit('gram'));
        $this->assertEquals('ml', UnitConversionService::getBaseUnit('liter'));
        $this->assertEquals('ml', UnitConversionService::getBaseUnit('ml'));
        $this->assertEquals('pcs', UnitConversionService::getBaseUnit('cup'));
        $this->assertEquals('pcs', UnitConversionService::getBaseUnit('pcs'));
    }

    public function test_convert_to_base_unit_weight(): void
    {
        $convertedKg = UnitConversionService::convertToBaseUnit(1.5, 'kg');
        $this->assertEquals(1500, $convertedKg['amount']);
        $this->assertEquals('gram', $convertedKg['base_unit']);
        $this->assertEquals('weight', $convertedKg['group']);

        $convertedMg = UnitConversionService::convertToBaseUnit(500, 'mg');
        $this->assertEquals(0.5, $convertedMg['amount']);
        $this->assertEquals('gram', $convertedMg['base_unit']);
    }

    public function test_convert_to_base_unit_volume(): void
    {
        $convertedLiter = UnitConversionService::convertToBaseUnit(2, 'liter');
        $this->assertEquals(2000, $convertedLiter['amount']);
        $this->assertEquals('ml', $convertedLiter['base_unit']);
        $this->assertEquals('volume', $convertedLiter['group']);
    }

    public function test_convert_to_base_unit_count(): void
    {
        $convertedCount = UnitConversionService::convertToBaseUnit(50, 'cup');
        $this->assertEquals(50, $convertedCount['amount']);
        $this->assertEquals('pcs', $convertedCount['base_unit']);
        $this->assertEquals('count', $convertedCount['group']);
    }

    public function test_convert_between_compatible_units(): void
    {
        // 2 kg to gram = 2000
        $result = UnitConversionService::convertBetweenUnits(2, 'kg', 'gram');
        $this->assertEquals(2000, $result);

        // 1500 ml to liter = 1.5
        $resultVolume = UnitConversionService::convertBetweenUnits(1500, 'ml', 'liter');
        $this->assertEquals(1.5, $resultVolume);
    }

    public function test_incompatible_units_throw_invalid_argument_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        UnitConversionService::convertBetweenUnits(1, 'kg', 'liter');
    }
}

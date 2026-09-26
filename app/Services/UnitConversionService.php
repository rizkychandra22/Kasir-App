<?php

namespace App\Services;

class UnitConversionService
{
    /**
     * Map of unit to group and conversion factor relative to base unit of group.
     */
    protected static $units = [
        // BERAT (Group: weight, Base Unit: gram)
        'kg'     => ['group' => 'weight', 'base' => 'gram', 'factor' => 1000],
        'gram'   => ['group' => 'weight', 'base' => 'gram', 'factor' => 1],
        'g'      => ['group' => 'weight', 'base' => 'gram', 'factor' => 1],
        'mg'     => ['group' => 'weight', 'base' => 'gram', 'factor' => 0.001],

        // VOLUME (Group: volume, Base Unit: ml)
        'liter'  => ['group' => 'volume', 'base' => 'ml', 'factor' => 1000],
        'l'      => ['group' => 'volume', 'base' => 'ml', 'factor' => 1000],
        'ml'     => ['group' => 'volume', 'base' => 'ml', 'factor' => 1],

        // JUMLAH (Group: count, Base Unit: pcs)
        'pcs'    => ['group' => 'count', 'base' => 'pcs', 'factor' => 1],
        'unit'   => ['group' => 'count', 'base' => 'pcs', 'factor' => 1],
        'cup'    => ['group' => 'count', 'base' => 'pcs', 'factor' => 1],
        'botol'  => ['group' => 'count', 'base' => 'pcs', 'factor' => 1],
        'sachet' => ['group' => 'count', 'base' => 'pcs', 'factor' => 1],
        'sendok' => ['group' => 'count', 'base' => 'pcs', 'factor' => 1],
        'buah'   => ['group' => 'count', 'base' => 'pcs', 'factor' => 1],
        'siung'  => ['group' => 'count', 'base' => 'pcs', 'factor' => 1],
        'potong' => ['group' => 'count', 'base' => 'pcs', 'factor' => 1],
        'porsi'  => ['group' => 'count', 'base' => 'pcs', 'factor' => 1],
    ];

    /**
     * Get group of a given unit
     */
    public static function getUnitGroup($unit)
    {
        $unitLower = strtolower(trim($unit));
        return self::$units[$unitLower]['group'] ?? 'count';
    }

    /**
     * Get base unit of a given unit
     */
    public static function getBaseUnit($unit)
    {
        $unitLower = strtolower(trim($unit));
        return self::$units[$unitLower]['base'] ?? $unitLower;
    }

    /**
     * Check if two units belong to the same group
     */
    public static function areUnitsCompatible($unit1, $unit2)
    {
        return self::getUnitGroup($unit1) === self::getUnitGroup($unit2);
    }

    /**
     * Convert an amount from a given unit to the base unit of its group
     */
    public static function convertToBaseUnit($amount, $unit)
    {
        $unitLower = strtolower(trim($unit));
        $factor = self::$units[$unitLower]['factor'] ?? 1;
        $baseUnit = self::$units[$unitLower]['base'] ?? $unitLower;

        return [
            'amount' => (float)$amount * $factor,
            'base_unit' => $baseUnit,
            'group' => self::getUnitGroup($unitLower),
        ];
    }

    /**
     * Convert an amount from unitA to unitB (must be in same group)
     */
    public static function convertBetweenUnits($amount, $fromUnit, $toUnit)
    {
        if (!self::areUnitsCompatible($fromUnit, $toUnit)) {
            throw new \InvalidArgumentException("Satuan pemakaian '{$fromUnit}' tidak sesuai dengan satuan dasar bahan '{$toUnit}'.");
        }

        $fromLower = strtolower(trim($fromUnit));
        $toLower   = strtolower(trim($toUnit));

        $fromFactor = self::$units[$fromLower]['factor'] ?? 1;
        $toFactor   = self::$units[$toLower]['factor'] ?? 1;

        // amount * fromFactor = baseAmount; baseAmount / toFactor = targetAmount
        return ((float)$amount * $fromFactor) / $toFactor;
    }
}

<?php

namespace App\Models;

use App\Services\UnitConversionService;
use Illuminate\Database\Eloquent\Model;

class Bahan extends Model
{
    protected $fillable = [
        'user_id',
        'name_bahan',
        'unit',
        'purchase_unit',
        'purchase_qty',
        'base_unit',
        'stock',
        'price',
        'cost_per_base_unit',
        'description',
        'status',
    ];

    protected static function booted()
    {
        static::saving(function ($bahan) {
            $purchaseQty = (float)($bahan->purchase_qty ?? 1);
            $purchaseUnit = $bahan->purchase_unit ?? $bahan->unit ?? 'pcs';
            $converted = UnitConversionService::convertToBaseUnit($purchaseQty, $purchaseUnit);
            $basePurchaseQty = (float)$converted['amount'];

            if ($basePurchaseQty > 0 && !is_null($bahan->price) && (float)$bahan->price > 0) {
                $bahan->attributes['cost_per_base_unit'] = (float)$bahan->price / $basePurchaseQty;
            } else {
                $bahan->attributes['cost_per_base_unit'] = 0;
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_bahan')
                    ->withPivot('quantity', 'unit')
                    ->withTimestamps();
    }

    public function stockMovements()
    {
        return $this->hasMany(BahanStockMovement::class)->latest();
    }

    /**
     * Check if material has a valid price specified
     */
    public function hasValidPrice()
    {
        return !is_null($this->price) && (float)$this->price > 0;
    }

    /**
     * Calculate cost per base unit automatically relative to purchase_qty and purchase_unit.
     * Formula: price / (purchase_qty converted to base_unit)
     */
    public function getCostPerBaseUnitAttribute()
    {
        $purchaseQty = (float)($this->purchase_qty ?? 1);
        $purchaseUnit = $this->purchase_unit ?? $this->unit ?? 'pcs';
        $converted = UnitConversionService::convertToBaseUnit($purchaseQty, $purchaseUnit);
        $basePurchaseQty = (float)$converted['amount'];

        if ($basePurchaseQty > 0 && !is_null($this->price) && (float)$this->price > 0) {
            return (float)$this->price / $basePurchaseQty;
        }
        return 0;
    }

    /**
     * Helper to compute and sync cost_per_base_unit column in DB
     */
    public function updateCostPerBaseUnit()
    {
        $cost = $this->cost_per_base_unit;
        $this->attributes['cost_per_base_unit'] = $cost;
        return $cost;
    }
}

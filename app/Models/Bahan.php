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
     * Calculate cost per base unit automatically
     */
    public function calculateCostPerBaseUnit()
    {
        if ((float)$this->stock > 0 && (float)$this->price > 0) {
            $this->cost_per_base_unit = (float)$this->price / (float)$this->stock;
        } else {
            $this->cost_per_base_unit = 0;
        }
    }
}

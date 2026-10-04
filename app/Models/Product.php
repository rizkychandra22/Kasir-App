<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id',
        'user_id',
        'name_prd',
        'code_prd',
        'description_prd',
        'sales_type',
        'price_online',
        'price_offline',
        'price',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function shoppingDetails()
    {
        return $this->hasMany(ShoppingDetail::class);
    }

    public function bahans()
    {
        return $this->belongsToMany(Bahan::class, 'product_bahan')->withPivot('quantity', 'unit')->withTimestamps();
    }

    /**
     * Check if product has explicit price configured for salesType
     */
    public function hasPriceForSalesType($salesType = 'offline')
    {
        if ($salesType === 'online') {
            return !is_null($this->price_online) && $this->price_online !== '';
        }
        if ($salesType === 'offline') {
            return !is_null($this->price_offline) && $this->price_offline !== '';
        }
        return true;
    }

    /**
     * Helper to get effective price based on transaction sales_type
     */
    public function getPriceForSalesType($salesType = 'offline')
    {
        if ($salesType === 'online' && !is_null($this->price_online) && $this->price_online !== '') {
            return (int)$this->price_online;
        }
        if ($salesType === 'offline' && !is_null($this->price_offline) && $this->price_offline !== '') {
            return (int)$this->price_offline;
        }
        return (int)$this->price;
    }

    /**
     * Alias for getPriceForSalesType
     */
    public function getPrice($salesType = 'offline')
    {
        return $this->getPriceForSalesType($salesType);
    }

    /**
     * Calculate total recipe material cost (HPP Bahan) for 1 portion of this product
     */
    public function calculateTotalRecipeCost()
    {
        $totalCost = 0;
        $bahans = $this->relationLoaded('bahans') && $this->bahans->isNotEmpty()
            ? $this->bahans
            : $this->bahans()->get();

        foreach ($bahans as $b) {
            $recipeQtyInBaseUnit = (float)($b->pivot->quantity ?? 1);
            $costPerBaseUnit = (float)$b->cost_per_base_unit;
            $totalCost += ($recipeQtyInBaseUnit * $costPerBaseUnit);
        }
        return $totalCost;
    }

    /**
     * Get detailed breakdown of recipe material costs
     */
    public function getRecipeCostDetails()
    {
        $details = [];
        $total = 0;

        foreach ($this->bahans as $b) {
            $recipeQtyInBaseUnit = (float)($b->pivot->quantity ?? 1);
            $recipeUnit = $b->pivot->unit ?? $b->base_unit;
            $costPerBaseUnit = (float)$b->cost_per_base_unit;
            $itemCost = $recipeQtyInBaseUnit * $costPerBaseUnit;
            $total += $itemCost;

            // Unit cost in recipe unit
            $recipeUnitConversion = \App\Services\UnitConversionService::convertToBaseUnit(1, $recipeUnit)['amount'];
            $costPerRecipeUnit = $recipeUnitConversion * $costPerBaseUnit;

            $details[] = [
                'bahan_id' => $b->id,
                'name_bahan' => $b->name_bahan,
                'quantity' => (float)($b->pivot->quantity ?? 1),
                'unit' => $recipeUnit,
                'base_unit' => $b->base_unit,
                'cost_per_base_unit' => $costPerBaseUnit,
                'cost_per_unit' => $costPerRecipeUnit,
                'item_cost' => $itemCost,
                'has_valid_price' => $b->hasValidPrice(),
            ];
        }

        return [
            'details' => $details,
            'total_cost' => $total,
        ];
    }

    /**
     * Get complete HPP details including labor and overhead
     */
    public function getHppDetails()
    {
        return \App\Services\HppService::getProductHppDetails($this);
    }

    /**
     * Calculate HPP Total for 1 cup/portion of this product
     */
    public function calculateHppTotal()
    {
        return $this->getHppDetails()['hpp_total'];
    }
}

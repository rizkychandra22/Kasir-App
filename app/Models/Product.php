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
        'stock',
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
}

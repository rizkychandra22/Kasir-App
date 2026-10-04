<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShoppingDetail extends Model
{
    protected $fillable = [
        'shopping_id',
        'product_id',
        'qty',
        'price',
        'subtotal',
        'material_cost',
        'created_at',
        'updated_at',
    ];

    public function shopping()
    {
        return $this->belongsTo(Shopping::class, 'shopping_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }  
}

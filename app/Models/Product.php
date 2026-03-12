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
}

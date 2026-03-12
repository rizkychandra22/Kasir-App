<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shopping extends Model
{
    protected $fillable = [
        'invoice',
        'user_id',
        'total_price',
        'pay',
        'change',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }  

    public function details()
    {
        return $this->hasMany(ShoppingDetail::class, 'shopping_id');
    }
}

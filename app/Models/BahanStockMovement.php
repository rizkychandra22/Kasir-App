<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BahanStockMovement extends Model
{
    protected $fillable = [
        'bahan_id',
        'user_id',
        'type',
        'qty',
        'stock_before',
        'stock_after',
        'reference',
        'notes',
    ];

    public function bahan()
    {
        return $this->belongsTo(Bahan::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

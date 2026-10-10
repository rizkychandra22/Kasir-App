<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'customer_name',
        'user_id',
        'shopping_id',
        'total_amount',
        'status',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function shopping()
    {
        return $this->belongsTo(Shopping::class, 'shopping_id');
    }

    public function details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Generate unique sequential order number with format OB-0001, OB-0002, etc.
     */
    public static function generateOrderNumber(): string
    {
        $lastOrder = static::where('order_number', 'like', 'OB-%')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOrder && preg_match('/^OB-(\d+)$/', $lastOrder->order_number, $matches)) {
            $nextNum = (int)$matches[1] + 1;
        } else {
            $nextNum = static::count() + 1;
        }

        $number = 'OB-' . str_pad((string)$nextNum, 4, '0', STR_PAD_LEFT);

        while (static::where('order_number', $number)->exists()) {
            $nextNum++;
            $number = 'OB-' . str_pad((string)$nextNum, 4, '0', STR_PAD_LEFT);
        }

        return $number;
    }
}

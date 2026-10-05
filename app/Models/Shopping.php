<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shopping extends Model
{
    protected $fillable = [
        'invoice',
        'sales_type',
        'payment_method',
        'user_id',
        'total_price',
        'pay',
        'change',
        'status',
        'created_at',
        'updated_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }  

    public function details()
    {
        return $this->hasMany(ShoppingDetail::class, 'shopping_id');
    }

    /**
     * Scope to filter out canceled/void transactions
     */
    public function scopeValidSales($query)
    {
        if (\Illuminate\Support\Facades\Schema::hasColumn('shoppings', 'status')) {
            $query->where(function ($q) {
                $q->whereNull('status')
                  ->orWhereNotIn('status', ['canceled', 'cancelled', 'batal', 'void']);
            });
        }
        return $query;
    }

    /**
     * Get transaction HPP based on stored snapshot in details
     */
    public function getHppAttribute(): float
    {
        if (isset($this->attributes['hpp'])) {
            return (float)$this->attributes['hpp'];
        }

        if ($this->relationLoaded('details')) {
            return (float)$this->details->sum(function ($detail) {
                return (float)($detail->material_cost ?? 0);
            });
        }

        return (float)$this->details()->sum('material_cost');
    }

    /**
     * Get transaction Gross Profit: Total Penjualan - HPP Transaksi
     */
    public function getGrossProfitAttribute(): float
    {
        return (float)$this->total_price - (float)$this->hpp;
    }

    /**
     * Presentation label for sales type and payment method:
     * - OFFLINE (CASH)
     * - OFFLINE (QRIS)
     * - ONLINE
     */
    public function getSalesTypeLabelAttribute(): string
    {
        $salesType = strtoupper($this->sales_type ?? 'OFFLINE');
        $method = strtoupper($this->payment_method ?? 'CASH');
        return "{$salesType} ({$method})";
    }

    /**
     * Scope for offline sales
     */
    public function scopeOffline($query)
    {
        return $query->where(function ($q) {
            $q->where('sales_type', 'offline')
              ->orWhereNull('sales_type');
        });
    }

    /**
     * Scope for online sales
     */
    public function scopeOnline($query)
    {
        return $query->where('sales_type', 'online');
    }

    /**
     * Scope for offline cash transactions (enter physical cash drawer)
     */
    public function scopeCash($query)
    {
        return $query->offline()->where(function ($q) {
            $q->where('payment_method', 'cash')
              ->orWhereNull('payment_method');
        });
    }

    /**
     * Scope for offline QRIS transactions (do NOT enter physical cash drawer)
     */
    public function scopeQris($query)
    {
        return $query->offline()->where('payment_method', 'qris');
    }

    /**
     * Physical cash sales that enter the cash drawer
     */
    public static function getCashDrawerSales($date = null): float
    {
        $query = static::validSales()->cash();
        if ($date) {
            $query->whereDate('created_at', \Carbon\Carbon::parse($date)->toDateString());
        }
        return (float)$query->sum('total_price');
    }

    /**
     * Non-cash QRIS sales (does NOT enter physical cash drawer)
     */
    public static function getQrisSales($date = null): float
    {
        $query = static::validSales()->qris();
        if ($date) {
            $query->whereDate('created_at', \Carbon\Carbon::parse($date)->toDateString());
        }
        return (float)$query->sum('total_price');
    }
}


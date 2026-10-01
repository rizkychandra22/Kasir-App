<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetSale extends Model
{
    use HasFactory;

    protected $table = 'target_sales';

    protected $fillable = [
        'user_id',
        'target_sales_monthly',
        'operating_days',
    ];

    protected $casts = [
        'target_sales_monthly' => 'decimal:2',
        'operating_days' => 'integer',
    ];

    /**
     * Get the current active target sale settings or return default instance.
     */
    public static function getTargetSettings()
    {
        return static::first() ?? new static([
            'target_sales_monthly' => 0.00,
            'operating_days' => 26,
        ]);
    }

    /**
     * Calculate Daily Target Sales
     */
    public function getDailyTargetAttribute()
    {
        if ($this->operating_days > 0) {
            return round($this->target_sales_monthly / $this->operating_days, 2);
        }
        return 0;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Overhead extends Model
{
    use HasFactory;

    protected $table = 'overheads';

    protected $fillable = [
        'user_id',
        'name',
        'category',
        'nominal_monthly',
        'status',
        'notes',
    ];

    protected $casts = [
        'nominal_monthly' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get Total Nominal of Active Overheads
     */
    public static function getTotalActiveNominal()
    {
        return static::active()->sum('nominal_monthly');
    }

    /**
     * Get Overhead Cost per Cup based on active monthly target sales
     */
    public static function getCostPerCup()
    {
        $target = TargetSale::getTargetSettings();
        if ($target->target_sales_monthly > 0) {
            return round(static::getTotalActiveNominal() / $target->target_sales_monthly, 4);
        }
        return 0;
    }

    /**
     * Get Total Non-Material Cost per Cup (Labor per Cup + Overhead per Cup)
     */
    public static function getTotalNonMaterialCostPerCup()
    {
        return Labor::getCostPerCup() + static::getCostPerCup();
    }
}

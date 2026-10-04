<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Labor extends Model
{
    use HasFactory;

    protected $table = 'labors';

    protected $fillable = [
        'user_id',
        'name',
        'monthly_salary',
        'status',
        'notes',
    ];

    protected $casts = [
        'monthly_salary' => 'decimal:2',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Get Total Salary of Active Labors
     */
    public static function getTotalActiveSalary()
    {
        return static::active()->sum('monthly_salary');
    }

    /**
     * Get Labor Cost per Cup based on active monthly target cup sales
     */
    public static function getCostPerCup($year = null)
    {
        $target = TargetSale::getTargetSettings($year);
        $targetCups = $target->getTargetCupsMonthly();
        if ($targetCups > 0) {
            return round(static::getTotalActiveSalary() / $targetCups, 4);
        }
        return 0;
    }
}

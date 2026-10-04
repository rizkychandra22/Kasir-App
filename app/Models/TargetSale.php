<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class TargetSale extends Model
{
    use HasFactory;

    protected $table = 'target_sales';

    protected $fillable = [
        'user_id',
        'year',
        'annual_sales_target',
        'average_selling_price',
        'annual_target_cups',
        'monthly_sales_target',
        'monthly_target_cups',
        'target_sales_monthly',
        'operating_days',
    ];

    protected $casts = [
        'year' => 'integer',
        'annual_sales_target' => 'decimal:2',
        'average_selling_price' => 'decimal:2',
        'annual_target_cups' => 'decimal:2',
        'monthly_sales_target' => 'decimal:2',
        'monthly_target_cups' => 'decimal:2',
        'target_sales_monthly' => 'decimal:2',
        'operating_days' => 'integer',
    ];

    /**
     * Get target sale settings for a given year or current/latest.
     */
    public static function getTargetSettings($year = null)
    {
        if ($year) {
            $found = static::where('year', (int)$year)->first();
            if ($found) {
                return $found;
            }
        }

        $currentYear = (int)date('Y');
        $current = static::where('year', $currentYear)->first();
        if ($current) {
            return $current;
        }

        $latest = static::orderBy('year', 'desc')->first();
        if ($latest) {
            return $latest;
        }

        $any = static::first();
        if ($any) {
            return $any;
        }

        return new static([
            'year' => $year ?? $currentYear,
            'annual_sales_target' => 0.00,
            'average_selling_price' => 0.00,
            'annual_target_cups' => 0.00,
            'monthly_sales_target' => 0.00,
            'monthly_target_cups' => 0.00,
            'target_sales_monthly' => 0.00,
            'operating_days' => 26,
        ]);
    }

    /**
     * Get monthly target cups for Labor & Overhead calculations.
     */
    public function getTargetCupsMonthly()
    {
        if ($this->monthly_target_cups > 0) {
            return (float)$this->monthly_target_cups;
        }

        if ($this->target_sales_monthly > 0) {
            return (float)$this->target_sales_monthly;
        }

        if ($this->annual_target_cups > 0) {
            return round((float)$this->annual_target_cups / 12, 4);
        }

        return 0.00;
    }

    /**
     * Calculate Daily Target Sales based on monthly cup target
     */
    public function getDailyTargetAttribute()
    {
        $monthlyCups = $this->getTargetCupsMonthly();
        if ($this->operating_days > 0 && $monthlyCups > 0) {
            return round($monthlyCups / $this->operating_days, 2);
        }
        return 0;
    }

    /**
     * Calculate monthly breakdown and annual performance
     */
    public function calculateMonthlyBreakdown($targetYear = null)
    {
        $year = (int)($targetYear ?? $this->year ?? date('Y'));
        $monthNames = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember',
        ];

        $annualSales = (float)$this->annual_sales_target;
        $annualCups = (float)$this->annual_target_cups;

        $monthlySalesBase = $annualSales > 0 ? round($annualSales / 12, 2) : 0.00;

        // Distribute cups across 12 months with integer rounding preserving annual total
        $baseCup = $annualCups > 0 ? (int)floor($annualCups / 12) : 0;
        $remainderCups = $annualCups > 0 ? (int)round($annualCups - ($baseCup * 12)) : 0;

        $months = [];
        $totalActualRevenue = 0.00;
        $totalActualCups = 0;

        for ($m = 1; $m <= 12; $m++) {
            $monthTargetCups = $m <= $remainderCups ? ($baseCup + 1) : $baseCup;
            $monthTargetSales = $monthlySalesBase;

            // Actual revenue query using standard Shopping::total_price
            $revenueQuery = Shopping::whereYear('created_at', $year)
                ->whereMonth('created_at', $m);

            if (Schema::hasColumn('shoppings', 'status')) {
                $revenueQuery->whereNotIn('status', ['canceled', 'cancelled', 'batal', 'void']);
            }

            $actualRevenue = (float)$revenueQuery->sum('total_price');

            // Actual cups sold query from ShoppingDetail
            $cupsQuery = ShoppingDetail::whereHas('shopping', function ($q) use ($year, $m) {
                $q->whereYear('created_at', $year)
                  ->whereMonth('created_at', $m);
                if (Schema::hasColumn('shoppings', 'status')) {
                    $q->whereNotIn('status', ['canceled', 'cancelled', 'batal', 'void']);
                }
            });

            $actualCups = (int)$cupsQuery->sum('qty');

            // Percentage calculations
            $salesAchievementPercent = $monthTargetSales > 0
                ? round(($actualRevenue / $monthTargetSales) * 100, 2)
                : 0.00;

            $cupsAchievementPercent = $monthTargetCups > 0
                ? round(($actualCups / $monthTargetCups) * 100, 2)
                : 0.00;

            $totalActualRevenue += $actualRevenue;
            $totalActualCups += $actualCups;

            $months[$m] = [
                'month_number' => $m,
                'month_name' => $monthNames[$m],
                'target_sales' => $monthTargetSales,
                'actual_sales' => $actualRevenue,
                'sales_achievement_percent' => $salesAchievementPercent,
                'target_cups' => $monthTargetCups,
                'actual_cups' => $actualCups,
                'cups_achievement_percent' => $cupsAchievementPercent,
            ];
        }

        $remainingSales = max(0, $annualSales - $totalActualRevenue);
        $remainingCups = max(0, $annualCups - $totalActualCups);

        $annualSalesPercent = $annualSales > 0
            ? round(($totalActualRevenue / $annualSales) * 100, 2)
            : 0.00;

        $annualCupsPercent = $annualCups > 0
            ? round(($totalActualCups / $annualCups) * 100, 2)
            : 0.00;

        return [
            'year' => $year,
            'annual_sales_target' => $annualSales,
            'annual_sales_actual' => $totalActualRevenue,
            'annual_sales_remaining' => $remainingSales,
            'annual_sales_percent' => $annualSalesPercent,
            'annual_cups_target' => $annualCups,
            'annual_cups_actual' => $totalActualCups,
            'annual_cups_remaining' => $remainingCups,
            'annual_cups_percent' => $annualCupsPercent,
            'months' => $months,
        ];
    }
}

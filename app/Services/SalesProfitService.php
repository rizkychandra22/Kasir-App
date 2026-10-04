<?php

namespace App\Services;

use App\Models\Overhead;
use App\Models\Shopping;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class SalesProfitService
{
    /**
     * Get HPP snapshot for a single transaction.
     * Strictly uses the saved transaction detail snapshot (material_cost).
     * Does NOT recalculate using current material prices or current recipes.
     */
    public static function getTransactionHpp(Shopping $shopping): float
    {
        return (float)$shopping->hpp;
    }

    /**
     * Calculate Gross Profit for a single transaction:
     * Laba Kotor = Total Penjualan - HPP Transaksi
     */
    public static function getTransactionGrossProfit(Shopping $shopping): float
    {
        return (float)$shopping->gross_profit;
    }

    /**
     * Helper to parse flexible date format (Y-m-d, d/m/Y, d-m-Y, etc.)
     */
    public static function parseDate($dateString): ?Carbon
    {
        if (empty($dateString)) {
            return null;
        }

        if ($dateString instanceof Carbon) {
            return $dateString->copy();
        }

        $dateString = trim($dateString);

        // Check if format is d/m/Y or d-m-Y (e.g. 04/10/2026 or 04-10-2026)
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})$/', $dateString, $matches)) {
            $day = (int)$matches[1];
            $month = (int)$matches[2];
            $year = (int)$matches[3];
            if (checkdate($month, $day, $year)) {
                return Carbon::create($year, $month, $day, 0, 0, 0);
            }
        }

        // Check if format is Y-m-d or Y/m/d (e.g. 2026-10-04 or 2026/10/04)
        if (preg_match('/^(\d{4})[\/\-](\d{1,2})[\/\-](\d{1,2})$/', $dateString, $matches)) {
            $year = (int)$matches[1];
            $month = (int)$matches[2];
            $day = (int)$matches[3];
            if (checkdate($month, $day, $year)) {
                return Carbon::create($year, $month, $day, 0, 0, 0);
            }
        }

        try {
            return Carbon::parse($dateString);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Build base query for valid sales transactions within optional period
     */
    public static function getSalesQuery($startDate = null, $endDate = null): Builder
    {
        $query = Shopping::with(['user', 'details.product'])
            ->validSales()
            ->latest('created_at')
            ->latest('id');

        $start = self::parseDate($startDate);
        if ($start) {
            $query->where('created_at', '>=', $start->copy()->startOfDay());
        }

        $end = self::parseDate($endDate);
        if ($end) {
            $query->where('created_at', '<=', $end->copy()->endOfDay());
        }

        return $query;
    }

    /**
     * Get sales collection within period
     */
    public static function getSalesData($startDate = null, $endDate = null): Collection
    {
        return self::getSalesQuery($startDate, $endDate)->get();
    }

    /**
     * Calculate period expenses based on existing Overhead model definition.
     * Respects the export period filter so expenses outside the period are not counted.
     */
    public static function getPeriodExpense($startDate = null, $endDate = null): float
    {
        $start = self::parseDate($startDate);
        $end = self::parseDate($endDate);

        // If a date filter is applied
        if ($start || $end) {
            $query = Overhead::active();
            if ($start) {
                $query->where('created_at', '>=', $start->copy()->startOfDay());
            }
            if ($end) {
                $query->where('created_at', '<=', $end->copy()->endOfDay());
            }

            return (float)$query->sum('nominal_monthly');
        }

        // If no filter is applied, use all active monthly overhead expenses
        return (float)Overhead::getTotalActiveNominal();
    }

    /**
     * Calculate Net Profit avoiding double-counting:
     * Laba Bersih = Laba Kotor - (Pengeluaran yang belum termasuk dalam HPP)
     * If an expense was already included in HPP (e.g. through Overhead/Cup),
     * it will not be deducted again.
     */
    public static function calculateNetProfit(float $labaKotor, float $totalExpense, float $overheadInHpp = 0.0): float
    {
        $unallocatedExpense = max(0.0, $totalExpense - $overheadInHpp);
        return $labaKotor - $unallocatedExpense;
    }

    /**
     * Generate complete summary data for the export period
     */
    public static function getSummaryData($shoppings, $startDate = null, $endDate = null): array
    {
        if (!($shoppings instanceof Collection)) {
            $shoppings = collect($shoppings);
        }

        $totalTransactions = $shoppings->count();
        $totalOmzet = (float)$shoppings->sum('total_price');
        $totalBayar = (float)$shoppings->sum('pay');
        $totalChange = (float)$shoppings->sum('change');

        $totalHpp = (float)$shoppings->sum(function ($item) {
            return self::getTransactionHpp($item);
        });

        $penjualanCash = (float)$shoppings->filter(function ($item) {
            $st = strtolower($item->sales_type ?? 'offline');
            $pm = strtolower($item->payment_method ?? 'cash');
            return $st === 'offline' && $pm === 'cash';
        })->sum('total_price');

        $penjualanQris = (float)$shoppings->filter(function ($item) {
            $st = strtolower($item->sales_type ?? 'offline');
            $pm = strtolower($item->payment_method ?? 'cash');
            return $st === 'offline' && $pm === 'qris';
        })->sum('total_price');

        $penjualanOnline = (float)$shoppings->filter(function ($item) {
            return strtolower($item->sales_type ?? 'offline') === 'online';
        })->sum('total_price');

        $labaKotor = $totalOmzet - $totalHpp;

        $totalPengeluaran = self::getPeriodExpense($startDate, $endDate);

        // Check if any overhead was already included in transaction HPP
        $overheadInHpp = 0.0;
        foreach ($shoppings as $shop) {
            if (!empty($shop->overhead_cost_in_hpp)) {
                $overheadInHpp += (float)$shop->overhead_cost_in_hpp;
            }
        }

        $labaBersih = self::calculateNetProfit($labaKotor, $totalPengeluaran, $overheadInHpp);

        // Calculate margins safely without division by zero
        $marginLabaKotor = $totalOmzet > 0
            ? round(($labaKotor / $totalOmzet) * 100, 2)
            : 0.00;

        $marginLabaBersih = $totalOmzet > 0
            ? round(($labaBersih / $totalOmzet) * 100, 2)
            : 0.00;

        return [
            'total_transactions' => $totalTransactions,
            'total_omzet'        => $totalOmzet,
            'penjualan_cash'     => $penjualanCash,
            'penjualan_qris'     => $penjualanQris,
            'penjualan_online'   => $penjualanOnline,
            'total_bayar'        => $totalBayar,
            'total_kembalian'    => $totalChange,
            'total_hpp'          => $totalHpp,
            'laba_kotor'         => $labaKotor,
            'margin_laba_kotor'  => $marginLabaKotor,
            'total_pengeluaran'  => $totalPengeluaran,
            'laba_bersih'        => $labaBersih,
            'margin_laba_bersih' => $marginLabaBersih,
            'start_date'         => $startDate,
            'end_date'           => $endDate,
        ];
    }
}

<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Labor;
use App\Models\Overhead;
use App\Models\TargetSale;

class HppService
{
    /**
     * Calculate complete HPP details for a product
     */
    public static function getProductHppDetails(Product $product, $year = null)
    {
        $target = TargetSale::getTargetSettings($year);
        $targetCups = $target->getTargetCupsMonthly();
        $isTargetConfigured = $targetCups > 0;

        $hasRecipe = $product->bahans()->count() > 0;
        $hppBahan = (float)$product->calculateTotalRecipeCost();

        $laborCostPerCup = $isTargetConfigured ? (float)Labor::getCostPerCup($year) : 0.00;
        $overheadCostPerCup = $isTargetConfigured ? (float)Overhead::getCostPerCup($year) : 0.00;
        $hppTotal = $hppBahan + $laborCostPerCup + $overheadCostPerCup;

        // Warnings / Statuses
        $warnings = [];
        if (!$hasRecipe) {
            $warnings[] = 'Resep belum lengkap';
        }
        if (!$isTargetConfigured) {
            $warnings[] = 'Target Penjualan belum diatur';
        }

        // Percentage contribution of each component relative to HPP Total
        $contribBahan = $hppTotal > 0 ? round(($hppBahan / $hppTotal) * 100, 2) : 0;
        $contribLabor = $hppTotal > 0 ? round(($laborCostPerCup / $hppTotal) * 100, 2) : 0;
        $contribOverhead = $hppTotal > 0 ? round(($overheadCostPerCup / $hppTotal) * 100, 2) : 0;

        return [
            'has_recipe' => $hasRecipe,
            'is_target_configured' => $isTargetConfigured,
            'hpp_bahan' => $hppBahan,
            'labor_cost_per_cup' => $laborCostPerCup,
            'overhead_cost_per_cup' => $overheadCostPerCup,
            'hpp_total' => $hppTotal,
            'contrib_bahan' => $contribBahan,
            'contrib_labor' => $contribLabor,
            'contrib_overhead' => $contribOverhead,
            'warnings' => $warnings,
        ];
    }

    /**
     * Calculate nominal margin: Price - HPP
     */
    public static function calculateMarginNominal($price, $hppTotal)
    {
        return (float)$price - (float)$hppTotal;
    }

    /**
     * Calculate percent margin: ((Price - HPP) / Price) * 100
     */
    public static function calculateMarginPercent($price, $hppTotal)
    {
        $price = (float)$price;
        if ($price <= 0) {
            return 0.00;
        }
        $marginNominal = self::calculateMarginNominal($price, $hppTotal);
        return round(($marginNominal / $price) * 100, 2);
    }

    /**
     * Calculate theoretical price: HPP / (1 - TargetMargin/100)
     */
    public static function calculateTheoreticalPrice($hppTotal, $targetMarginPercent)
    {
        $targetMarginPercent = (float)$targetMarginPercent;
        if ($targetMarginPercent < 0 || $targetMarginPercent >= 100) {
            throw new \InvalidArgumentException("Target margin tidak boleh < 0% atau >= 100%.");
        }

        $hppTotal = (float)$hppTotal;
        if ($hppTotal <= 0) {
            return 0.00;
        }

        $divisor = 1 - ($targetMarginPercent / 100);
        if ($divisor <= 0) {
            throw new \InvalidArgumentException("Target margin tidak boleh >= 100%.");
        }

        return round($hppTotal / $divisor, 4);
    }

    /**
     * Get margin status string based on actual vs target margin
     */
    public static function getMarginStatus($actualMarginPercent, $targetMarginPercent)
    {
        $actual = round((float)$actualMarginPercent, 2);
        $target = round((float)$targetMarginPercent, 2);

        if ($actual > $target) {
            return 'Di atas target margin';
        }
        if ($actual == $target) {
            return 'Sesuai target';
        }
        return 'Di bawah target margin';
    }

    /**
     * Get suggested price rounded to nearest step (e.g. 100, 500, 1000)
     */
    public static function getSuggestedPrice($theoreticalPrice, $rounding = 100)
    {
        $price = (float)$theoreticalPrice;
        if ($price <= 0) {
            return 0;
        }
        $step = (int)$rounding;
        if ($step <= 0) {
            $step = 100;
        }

        return (int)(ceil($price / $step) * $step);
    }
}

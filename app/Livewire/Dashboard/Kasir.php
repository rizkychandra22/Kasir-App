<?php

namespace App\Livewire\Dashboard;

use App\Models\Category;
use App\Models\Product;
use App\Models\Shopping;
use App\Models\ShoppingDetail;
use Carbon\Carbon;
use Livewire\Component;

class Kasir extends Component
{
    public $subpage = 'Dashboard';
    public $content = 'Overview Kasir';
    public $linkSubpage;

    public function mount()
    {
        $this->linkSubpage = route('kasir.dashboard');
    }

    public function render()
    {
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $target = \App\Models\TargetSale::getTargetSettings();
        $targetMonthly = (float)$target->target_sales_monthly;
        $totalActiveLabor = \App\Models\Labor::getTotalActiveSalary();
        $laborCostPerCup = \App\Models\Labor::getCostPerCup();
        $totalActiveOverhead = \App\Models\Overhead::getTotalActiveNominal();
        $overheadCostPerCup = \App\Models\Overhead::getCostPerCup();
        $totalNonMaterialPerCup = \App\Models\Overhead::getTotalNonMaterialCostPerCup();

        return view('livewire.dashboard.kasir', [
            'countCategory' => Category::count(),
            'countProductReady' => Product::count(),
            'totalStockReady' => 0,
            'countProductSold' => ShoppingDetail::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('qty'),
            'countRevenue' => Shopping::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('total_price'),
            'revenueOffline' => Shopping::whereBetween('created_at', [$startOfMonth, $endOfMonth])->where(function($q) { $q->where('sales_type', 'offline')->orWhereNull('sales_type'); })->sum('total_price'),
            'revenueOnline' => Shopping::whereBetween('created_at', [$startOfMonth, $endOfMonth])->where('sales_type', 'online')->sum('total_price'),
            'currentMonth' => Carbon::now()->locale('id')->translatedFormat('F Y'),
            'targetMonthly' => $targetMonthly,
            'totalActiveLabor' => $totalActiveLabor,
            'laborCostPerCup' => $laborCostPerCup,
            'totalActiveOverhead' => $totalActiveOverhead,
            'overheadCostPerCup' => $overheadCostPerCup,
            'totalNonMaterialPerCup' => $totalNonMaterialPerCup,
        ])->layout('layouts.app', [
            'subpage' => 'Dashboard',    
            'content' => 'Overview Kasir', 
        ]);
    }
}

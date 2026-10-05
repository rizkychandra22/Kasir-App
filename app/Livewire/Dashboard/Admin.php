<?php

namespace App\Livewire\Dashboard;

use App\Models\Category;
use App\Models\Labor;
use App\Models\Overhead;
use App\Models\Product;
use App\Models\Shopping;
use App\Models\ShoppingDetail;
use App\Models\TargetSale;
use Carbon\Carbon;
use Livewire\Component;

class Admin extends Component
{
    public $subpage = 'Dashboard';
    public $content = 'Overview Admin';
    public $linkSubpage;
    public $selectedYear;

    public function mount()
    {
        $this->linkSubpage = route('admin.dashboard');
        $this->selectedYear = (int)date('Y');
    }

    public function updatedSelectedYear()
    {
        // Livewire updates component automatically
    }

    public function render()
    {
        $year = (int)($this->selectedYear ?? date('Y'));

        // If selected year is current year, show current month metrics, else show whole year metrics
        $isCurrentYear = ($year === (int)date('Y'));
        $startOfPeriod = $isCurrentYear ? Carbon::now()->startOfMonth() : Carbon::create($year, 1, 1)->startOfDay();
        $endOfPeriod = $isCurrentYear ? Carbon::now()->endOfMonth() : Carbon::create($year, 12, 31)->endOfDay();

        $target = TargetSale::getTargetSettings($year);
        $targetMonthly = $target->getTargetCupsMonthly();
        $totalActiveLabor = Labor::getTotalActiveSalary();
        $laborCostPerCup = Labor::getCostPerCup($year);
        $totalActiveOverhead = Overhead::getTotalActiveNominal();
        $overheadCostPerCup = Overhead::getCostPerCup($year);
        $totalNonMaterialPerCup = Overhead::getTotalNonMaterialCostPerCup($year);

        $breakdown = $target->calculateMonthlyBreakdown($year);

        // List available years for filter
        $existingYears = TargetSale::whereNotNull('year')->pluck('year')->toArray();
        $baseYears = [(int)date('Y') - 1, (int)date('Y'), (int)date('Y') + 1, 2027, 2028];
        $availableYears = array_unique(array_merge($existingYears, $baseYears));
        sort($availableYears);

        // Recent transactions for Admin overview (excluding void/canceled transactions)
        $recentTransactions = Shopping::validSales()
            ->with(['user', 'details'])
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.dashboard.admin', [
            'countCategory' => Category::count(),
            'countProductReady' => Product::count(),
            'totalStockReady' => 0,
            'countProductSold' => ShoppingDetail::whereHas('shopping', function ($q) use ($startOfPeriod, $endOfPeriod) {
                $q->validSales()->whereBetween('created_at', [$startOfPeriod, $endOfPeriod]);
            })->sum('qty'),
            'countRevenue' => Shopping::validSales()
                ->whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
                ->sum('total_price'),
            'revenueOffline' => Shopping::validSales()
                ->whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
                ->where(function ($q) {
                    $q->where('sales_type', 'offline')->orWhereNull('sales_type');
                })
                ->sum('total_price'),
            'revenueOnline' => Shopping::validSales()
                ->whereBetween('created_at', [$startOfPeriod, $endOfPeriod])
                ->where('sales_type', 'online')
                ->sum('total_price'),
            'currentMonth' => $isCurrentYear ? Carbon::now()->locale('id')->translatedFormat('F Y') : "Tahun $year",
            'targetMonthly' => $targetMonthly,
            'totalActiveLabor' => $totalActiveLabor,
            'laborCostPerCup' => $laborCostPerCup,
            'totalActiveOverhead' => $totalActiveOverhead,
            'overheadCostPerCup' => $overheadCostPerCup,
            'totalNonMaterialPerCup' => $totalNonMaterialPerCup,
            'selectedYear' => $year,
            'annualBreakdown' => $breakdown,
            'availableYears' => $availableYears,
            'recentTransactions' => $recentTransactions,
        ])->layout('layouts.app', [
            'subpage' => $this->subpage,    
            'content' => $this->content, 
        ]);
    }
}


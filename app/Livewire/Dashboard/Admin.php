<?php

namespace App\Livewire\Dashboard;

use App\Models\Bahan;
use App\Models\Product;
use App\Models\Shopping;
use App\Models\ShoppingDetail;
use App\Models\TargetSale;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;

class Admin extends Component
{
    public $subpage = 'Dashboard';
    public $content = 'Overview Admin';
    public $linkSubpage;

    // Filters
    public $selectedPeriod = 'today'; // 'today', 'week', 'month', 'year'
    public $selectedYear;

    public function mount()
    {
        $this->linkSubpage = route('admin.dashboard');
        $this->selectedYear = (int)date('Y');
    }

    public function updatedSelectedPeriod()
    {
        // Livewire auto re-renders
    }

    public function updatedSelectedYear()
    {
        // Livewire auto re-renders
    }

    public function setPeriod($period)
    {
        if (in_array($period, ['today', 'week', 'month', 'year'])) {
            $this->selectedPeriod = $period;
        }
    }

    /**
     * Computed helpers for easy programmatic and test access
     */
    public function getPenjualanHariIniProperty(): float
    {
        $start = Carbon::today()->startOfDay();
        $end = Carbon::today()->endOfDay();
        return (float)Shopping::validSales()->whereBetween('created_at', [$start, $end])->sum('total_price');
    }

    public function getTransaksiHariIniProperty(): int
    {
        $start = Carbon::today()->startOfDay();
        $end = Carbon::today()->endOfDay();
        return (int)Shopping::validSales()->whereBetween('created_at', [$start, $end])->count();
    }

    public function getProdukTerjualHariIniProperty(): int
    {
        $start = Carbon::today()->startOfDay();
        $end = Carbon::today()->endOfDay();
        return (int)ShoppingDetail::whereHas('shopping', function ($q) use ($start, $end) {
            $q->validSales()->whereBetween('created_at', [$start, $end]);
        })->sum('qty');
    }

    public function getLabaKotorHariIniProperty(): float
    {
        $start = Carbon::today()->startOfDay();
        $end = Carbon::today()->endOfDay();
        $sales = (float)Shopping::validSales()->whereBetween('created_at', [$start, $end])->sum('total_price');
        $hpp = (float)ShoppingDetail::whereHas('shopping', function ($q) use ($start, $end) {
            $q->validSales()->whereBetween('created_at', [$start, $end]);
        })->sum('material_cost');
        return $sales - $hpp;
    }

    public function render()
    {
        $year = (int)($this->selectedYear ?? date('Y'));
        $now = Carbon::now();

        // 1. Determine period boundaries
        switch ($this->selectedPeriod) {
            case 'week':
                $periodStart = $now->copy()->startOfWeek();
                $periodEnd = $now->copy()->endOfWeek();
                $periodLabel = 'Minggu Ini';
                break;
            case 'month':
                $periodStart = $now->copy()->startOfMonth();
                $periodEnd = $now->copy()->endOfMonth();
                $periodLabel = 'Bulan Ini';
                break;
            case 'year':
                $periodStart = Carbon::create($year, 1, 1)->startOfDay();
                $periodEnd = Carbon::create($year, 12, 31)->endOfDay();
                $periodLabel = "Tahun $year";
                break;
            case 'today':
            default:
                $periodStart = $now->copy()->startOfDay();
                $periodEnd = $now->copy()->endOfDay();
                $periodLabel = 'Hari Ini';
                break;
        }

        // Today metrics
        $penjualanHariIni = $this->penjualanHariIni;
        $transaksiHariIni = $this->transaksiHariIni;
        $produkTerjualHariIni = $this->produkTerjualHariIni;
        $labaKotorHariIni = $this->labaKotorHariIni;

        // Selected Period metrics
        if ($this->selectedPeriod === 'today') {
            $periodSales = $penjualanHariIni;
            $periodTransactions = $transaksiHariIni;
            $periodSoldQty = $produkTerjualHariIni;
            $periodGrossProfit = $labaKotorHariIni;
        } else {
            $periodSales = (float)Shopping::validSales()
                ->whereBetween('created_at', [$periodStart, $periodEnd])
                ->sum('total_price');

            $periodTransactions = (int)Shopping::validSales()
                ->whereBetween('created_at', [$periodStart, $periodEnd])
                ->count();

            $periodSoldQty = (int)ShoppingDetail::whereHas('shopping', function ($q) use ($periodStart, $periodEnd) {
                $q->validSales()->whereBetween('created_at', [$periodStart, $periodEnd]);
            })->sum('qty');

            $periodHpp = (float)ShoppingDetail::whereHas('shopping', function ($q) use ($periodStart, $periodEnd) {
                $q->validSales()->whereBetween('created_at', [$periodStart, $periodEnd]);
            })->sum('material_cost');

            $periodGrossProfit = $periodSales - $periodHpp;
        }

        // 2. Diagram 1 & 2: Penjualan Per Bulan & Target vs Realisasi (Annual Target & Transaction Data)
        $target = TargetSale::getTargetSettings($year);
        $annualBreakdown = $target->calculateMonthlyBreakdown($year);

        $monthShortNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $monthlyActualSales = [];
        $monthlyTargetSales = [];

        foreach ($annualBreakdown['months'] as $m) {
            $monthlyActualSales[] = (float)$m['actual_sales'];
            $monthlyTargetSales[] = (float)$m['target_sales'];
        }

        // 3. Diagram 3: Metode Pembayaran (Selected Period)
        $cashSales = (float)Shopping::validSales()
            ->cash()
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->sum('total_price');

        $qrisSales = (float)Shopping::validSales()
            ->qris()
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->sum('total_price');

        $onlineSales = (float)Shopping::validSales()
            ->online()
            ->whereBetween('created_at', [$periodStart, $periodEnd])
            ->sum('total_price');

        // 4. Diagram 4: Produk Terlaris (Selected Period, Top 5 by Quantity)
        $topProducts = ShoppingDetail::query()
            ->select('products.name_prd', DB::raw('SUM(shopping_details.qty) as total_qty'))
            ->join('products', 'products.id', '=', 'shopping_details.product_id')
            ->join('shoppings', 'shoppings.id', '=', 'shopping_details.shopping_id')
            ->where(function ($q) {
                if (Schema::hasColumn('shoppings', 'status')) {
                    $q->whereNull('shoppings.status')
                      ->orWhereNotIn('shoppings.status', ['canceled', 'cancelled', 'batal', 'void']);
                }
            })
            ->whereBetween('shoppings.created_at', [$periodStart, $periodEnd])
            ->groupBy('products.id', 'products.name_prd')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get();

        $topProductLabels = $topProducts->pluck('name_prd')->toArray();
        $topProductData = $topProducts->pluck('total_qty')->map(fn($q) => (int)$q)->toArray();

        // 5. Section G: Peringatan Stok Bahan (Master Data Bahan Aktual)
        $materialsAttention = Bahan::where('status', 'active')
            ->orderBy('stock', 'asc')
            ->limit(5)
            ->get();

        // 6. Available years list
        $baseYears = [(int)date('Y') - 1, (int)date('Y'), (int)date('Y') + 1, 2027, 2028];
        $existingTargetYears = TargetSale::whereNotNull('year')->pluck('year')->map(fn($y) => (int)$y)->toArray();
        $availableYears = array_unique(array_merge($baseYears, $existingTargetYears));
        sort($availableYears);

        // Chart Payload for frontend JS
        $chartPayload = [
            'monthlySales' => [
                'labels' => $monthShortNames,
                'data' => $monthlyActualSales,
            ],
            'targetVsActual' => [
                'labels' => $monthShortNames,
                'targetData' => $monthlyTargetSales,
                'actualData' => $monthlyActualSales,
            ],
            'paymentMethod' => [
                'labels' => ['Cash', 'QRIS', 'Online'],
                'data' => [$cashSales, $qrisSales, $onlineSales],
            ],
            'topProducts' => [
                'labels' => $topProductLabels,
                'data' => $topProductData,
            ],
        ];

        return view('livewire.dashboard.admin', [
            'penjualanHariIni' => $penjualanHariIni,
            'transaksiHariIni' => $transaksiHariIni,
            'produkTerjualHariIni' => $produkTerjualHariIni,
            'labaKotorHariIni' => $labaKotorHariIni,
            'periodSales' => $periodSales,
            'periodTransactions' => $periodTransactions,
            'periodSoldQty' => $periodSoldQty,
            'periodGrossProfit' => $periodGrossProfit,
            'periodLabel' => $periodLabel,
            'selectedPeriod' => $this->selectedPeriod,
            'selectedYear' => $year,
            'availableYears' => $availableYears,
            'annualBreakdown' => $annualBreakdown,
            'monthlyActualSales' => $monthlyActualSales,
            'monthlyTargetSales' => $monthlyTargetSales,
            'cashSales' => $cashSales,
            'qrisSales' => $qrisSales,
            'onlineSales' => $onlineSales,
            'topProducts' => $topProducts,
            'materialsAttention' => $materialsAttention,
            'chartPayload' => $chartPayload,
        ])->layout('layouts.app', [
            'subpage' => $this->subpage,
            'content' => $this->content,
        ]);
    }
}

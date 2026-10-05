<?php

namespace App\Livewire\Operational;

use App\Models\Category;
use App\Models\Product;
use App\Services\HppService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class HppProduct extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $title = 'Dashboard';
    public $subpage = 'HPP & Margin Produk';
    public $linkTitle;
    public $linkSubpage;
    public $content = 'Index Data';

    public function mount()
    {
        $isAdmin = Auth::user()->role == 'Admin';
        $this->linkTitle = ($isAdmin) ? route('admin.dashboard') : route('kasir.dashboard');
        $this->subpage = 'HPP & Margin Produk';
        $this->linkSubpage = route('kasir.hpp-product');
        $this->content = 'Index Data';
    }

    public $search = '';
    public $selectedCategoryId = '';
    public $selectedProductId = null;

    // Target margin input for dynamic analysis (default 30%)
    public $targetMarginPercent = 30;
    public $roundingStep = 100; // 100, 500, 1000

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingSelectedCategoryId()
    {
        $this->resetPage();
    }

    public function selectProduct($id)
    {
        $this->selectedProductId = $id;
    }

    public function closeDetail()
    {
        $this->selectedProductId = null;
    }

    public function applySuggestedPriceToProduct($productId, $type = 'offline', $price = 0)
    {
        $product = Product::findOrFail($productId);
        $price = (int)$price;

        if ($type === 'online') {
            $product->price_online = $price;
        } elseif ($type === 'offline') {
            $product->price_offline = $price;
        }

        // Keep main price sync
        if ($product->sales_type === 'online') {
            $product->price = (int)($product->price_online ?? $price);
        } elseif ($product->sales_type === 'offline') {
            $product->price = (int)($product->price_offline ?? $price);
        } else {
            $product->price = (int)($product->price_offline ?? $product->price_online ?? $price);
        }

        $product->save();

        session()->flash('success', "Harga {$type} produk {$product->name_prd} berhasil diperbarui menjadi Rp" . number_format($price, 0, ',', '.'));
    }

    public function render()
    {
        // Validate target margin percent safely
        $validatedTargetMargin = (float)$this->targetMarginPercent;
        if ($validatedTargetMargin < 0) {
            $validatedTargetMargin = 0;
        } elseif ($validatedTargetMargin >= 100) {
            $validatedTargetMargin = 99;
        }

        $products = Product::with(['bahans', 'category'])
            ->when($this->search, function ($query) {
                $query->where('name_prd', 'like', '%' . $this->search . '%')
                      ->orWhere('code_prd', 'like', '%' . $this->search . '%');
            })
            ->when($this->selectedCategoryId, function ($query) {
                $query->where('category_id', $this->selectedCategoryId);
            })
            ->orderBy('name_prd', 'asc')
            ->paginate(10);

        $selectedProduct = null;
        $selectedHppDetails = null;
        $offlineAnalysis = null;
        $onlineAnalysis = null;
        $theoreticalPrice = 0;
        $suggestedPrice = 0;

        if ($this->selectedProductId) {
            $selectedProduct = Product::with(['bahans', 'category'])->find($this->selectedProductId);
            if ($selectedProduct) {
                $selectedHppDetails = HppService::getProductHppDetails($selectedProduct);
                $hppTotal = $selectedHppDetails['hpp_total'];

                // Offline Analysis
                $priceOffline = $selectedProduct->price_offline ?? $selectedProduct->price;
                $marginOfflineNominal = HppService::calculateMarginNominal($priceOffline, $hppTotal);
                $marginOfflinePercent = HppService::calculateMarginPercent($priceOffline, $hppTotal);
                $statusOffline = HppService::getMarginStatus($marginOfflinePercent, $validatedTargetMargin);

                $offlineAnalysis = [
                    'price' => $priceOffline,
                    'margin_nominal' => $marginOfflineNominal,
                    'margin_percent' => $marginOfflinePercent,
                    'status' => $statusOffline,
                ];

                // Online Analysis
                $priceOnline = $selectedProduct->price_online ?? $selectedProduct->price;
                $marginOnlineNominal = HppService::calculateMarginNominal($priceOnline, $hppTotal);
                $marginOnlinePercent = HppService::calculateMarginPercent($priceOnline, $hppTotal);
                $statusOnline = HppService::getMarginStatus($marginOnlinePercent, $validatedTargetMargin);

                $onlineAnalysis = [
                    'price' => $priceOnline,
                    'margin_nominal' => $marginOnlineNominal,
                    'margin_percent' => $marginOnlinePercent,
                    'status' => $statusOnline,
                ];

                // Target Margin Price Calculation
                $theoreticalPrice = HppService::calculateTheoreticalPrice($hppTotal, $validatedTargetMargin);
                $suggestedPrice = HppService::getSuggestedPrice($theoreticalPrice, $this->roundingStep);
            }
        }

        return view('livewire.operational.hpp-product', [
            'products' => $products,
            'categories' => Category::all(),
            'selectedProduct' => $selectedProduct,
            'selectedHppDetails' => $selectedHppDetails,
            'offlineAnalysis' => $offlineAnalysis,
            'onlineAnalysis' => $onlineAnalysis,
            'theoreticalPrice' => $theoreticalPrice,
            'suggestedPrice' => $suggestedPrice,
            'validatedTargetMargin' => $validatedTargetMargin,
        ])->layout('layouts.app', [
            'subpage' => $this->subpage,
            'content' => $this->content,
        ]);
    }
}

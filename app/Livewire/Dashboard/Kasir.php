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

        return view('livewire.dashboard.kasir', [
            'countCategory' => Category::count(),
            'countProductReady' => Product::where('stock', '>', 0)->count(),
            'totalStockReady' => Product::where('stock', '>', 0)->sum('stock'),
            'countProductSold' => ShoppingDetail::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('qty'),
            'countRevenue' => Shopping::whereBetween('created_at', [$startOfMonth, $endOfMonth])->sum('total_price'),
            'currentMonth' => Carbon::now()->locale('id')->translatedFormat('F Y'),
        ])->layout('layouts.app', [
            'subpage' => 'Dashboard',    
            'content' => 'Overview Kasir', 
        ]);
    }
}

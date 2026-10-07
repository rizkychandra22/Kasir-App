<?php

namespace App\Livewire\Product;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PreviewPrintProduct extends Component
{
    public $title = 'Dashboard';
    public $subpage = 'Data Produk';
    public $page = 'Export Data Produk';
    public $content = 'Index Export';
    public $linkTitle;
    public $linkSubpage;
    public $linkPage;

    public function mount()
    {
        $isAdmin = Auth::user()->role == 'Admin';
        $this->linkTitle = ($isAdmin) ? route('admin.dashboard') : route('kasir.dashboard');
        $this->subpage = 'Data Produk';
        $this->linkSubpage = route('kasir.product');
        $this->page = 'Export Data Produk';
        $this->linkPage = route('kasir.product.export');
        $this->content = 'Index Export';
    }

    public function render()
    {
        return view('livewire.product.preview-print-product', [
            'products' => Product::with('category')->latest()->get()
        ])->layout('layouts.app', [
            'subpage' => $this->subpage,
            'page'    => $this->page,
            'content' => $this->content,
        ]);
    }
}

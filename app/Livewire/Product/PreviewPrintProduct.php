<?php

namespace App\Livewire\Product;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PreviewPrintProduct extends Component
{
    public $title = 'Dashboard';
    public $subpage = 'Overview Kasir';
    public $linkTitle;
    public $linkSubpage;
    public $content = 'Export Data Produk';

    public function mount()
    {
        $isAdmin = Auth::user()->role == 'Admin';
        $this->linkTitle = ($isAdmin) ? route('admin.dashboard') : route('kasir.dashboard');
        $this->subpage = ($isAdmin) ? 'Overview Admin' : 'Overview Kasir';
        $this->linkSubpage = route('kasir.product.export');
    }

    public function render()
    {
        return view('livewire.product.preview-print-product', [
            'products' => Product::with('category')->latest()->get()
        ])->layout('layouts.app', [
            'subpage' => $this->subpage,
            'content' => $this->content,
        ]);
    }
}

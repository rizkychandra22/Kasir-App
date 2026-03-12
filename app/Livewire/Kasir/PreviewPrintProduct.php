<?php

namespace App\Livewire\Kasir;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;

class PreviewPrintProduct extends Component
{
    public $title = 'Dashboard';
    public $subpage = 'Overview Kasir';
    public $linkTitle;
    public $linkSubpage;
    public $content = 'Export Data';

    public function mount()
    {
        $this->linkTitle = route('kasir.dashboard');
        $this->linkSubpage = route('kasir.product.export');
    }

    public function render()
    {
        return view('livewire.kasir.preview-print-product', [
            'products' => Product::with('user', 'category')->latest()->get(),
            'categories' => Category::with('user')->latest()->get(),
        ])->layout('layouts.app', [
            'subpage' => $this->subpage,
            'content' => $this->content,
        ]);
    }
}

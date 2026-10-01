<?php

namespace App\Livewire\Transaction;

use App\Models\Shopping;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PreviewPrintShopping extends Component
{
    public $title = 'Dashboard';
    public $subpage = 'Overview Kasir';
    public $linkTitle;
    public $linkSubpage;
    public $content = 'Export Data Penjualan';

    public function mount()
    {
        $isAdmin = Auth::user()->role == 'Admin';
        $this->linkTitle = ($isAdmin) ? route('admin.dashboard') : route('kasir.dashboard');
        $this->subpage = ($isAdmin) ? 'Overview Admin' : 'Overview Kasir';
        $this->linkSubpage = route('kasir.shopping.export');
    }

    public function render()
    {
        return view('livewire.transaction.preview-print-shopping', [
            'shoppings' => Shopping::with(['user', 'details.product'])->latest()->get()
        ])->layout('layouts.app', [
            'subpage' => $this->subpage,
            'content' => $this->content,
        ]);
    }
}

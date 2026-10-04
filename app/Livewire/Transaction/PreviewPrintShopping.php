<?php

namespace App\Livewire\Transaction;

use App\Services\SalesProfitService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class PreviewPrintShopping extends Component
{
    public $title = 'Dashboard';
    public $subpage = 'Overview Kasir';
    public $linkTitle;
    public $linkSubpage;
    public $content = 'Export Data Penjualan';

    public $start_date;
    public $end_date;

    protected $queryString = [
        'start_date' => ['except' => ''],
        'end_date'   => ['except' => ''],
    ];

    public function mount()
    {
        $isAdmin = Auth::user()->role == 'Admin';
        $this->linkTitle = ($isAdmin) ? route('admin.dashboard') : route('kasir.dashboard');
        $this->subpage = ($isAdmin) ? 'Overview Admin' : 'Overview Kasir';
        $this->linkSubpage = route('kasir.shopping.export');

        $this->start_date = request()->query('start_date') ?? request()->query('from_date') ?? $this->start_date;
        $this->end_date = request()->query('end_date') ?? request()->query('to_date') ?? $this->end_date;
    }

    public function resetFilter()
    {
        $this->start_date = null;
        $this->end_date = null;
    }

    public function render()
    {
        $shoppings = SalesProfitService::getSalesData($this->start_date, $this->end_date);
        $summary = SalesProfitService::getSummaryData($shoppings, $this->start_date, $this->end_date);

        return view('livewire.transaction.preview-print-shopping', [
            'shoppings' => $shoppings,
            'summary'   => $summary,
        ])->layout('layouts.app', [
            'subpage' => $this->subpage,
            'content' => $this->content,
        ]);
    }
}

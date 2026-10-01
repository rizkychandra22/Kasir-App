<?php

namespace App\Livewire\Operational;

use App\Models\Labor;
use App\Models\Overhead;
use App\Models\TargetSale;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class TargetPenjualan extends Component
{
    public $subpage = 'Target Penjualan';
    public $content = 'Pengaturan Target Penjualan Bulanan & Ringkasan Biaya';

    public $target_sales_monthly = 2000;
    public $operating_days = 26;

    protected $rules = [
        'target_sales_monthly' => 'required|numeric|min:1',
        'operating_days' => 'required|integer|min:1|max:31',
    ];

    protected $messages = [
        'target_sales_monthly.required' => 'Target penjualan per bulan harus diisi.',
        'target_sales_monthly.numeric' => 'Target penjualan harus berupa angka.',
        'target_sales_monthly.min' => 'Target penjualan minimal 1 cup.',
        'operating_days.required' => 'Jumlah hari kerja harus diisi.',
        'operating_days.integer' => 'Jumlah hari kerja harus berupa angka bulat.',
        'operating_days.min' => 'Jumlah hari kerja minimal 1 hari.',
        'operating_days.max' => 'Jumlah hari kerja maksimal 31 hari.',
    ];

    public function mount()
    {
        $target = TargetSale::first();
        if ($target) {
            $this->target_sales_monthly = (float)$target->target_sales_monthly;
            $this->operating_days = (int)$target->operating_days;
        }
    }

    public function saveTarget()
    {
        $this->validate();

        $target = TargetSale::first();
        if (!$target) {
            $target = new TargetSale();
            $target->user_id = Auth::user()->id;
        }

        $target->target_sales_monthly = $this->target_sales_monthly;
        $target->operating_days = $this->operating_days;
        $target->save();

        session()->flash('success', 'Target penjualan bulanan berhasil disimpan!');
    }

    public function render()
    {
        $dailyTarget = $this->operating_days > 0 ? round($this->target_sales_monthly / $this->operating_days, 2) : 0;
        
        $totalActiveLabor = Labor::getTotalActiveSalary();
        $laborCostPerCup = $this->target_sales_monthly > 0 ? round($totalActiveLabor / $this->target_sales_monthly, 2) : 0;

        $totalActiveOverhead = Overhead::getTotalActiveNominal();
        $overheadCostPerCup = $this->target_sales_monthly > 0 ? round($totalActiveOverhead / $this->target_sales_monthly, 2) : 0;

        $totalNonMaterialPerCup = $laborCostPerCup + $overheadCostPerCup;

        return view('livewire.operational.target-penjualan', [
            'dailyTarget' => $dailyTarget,
            'totalActiveLabor' => $totalActiveLabor,
            'laborCostPerCup' => $laborCostPerCup,
            'totalActiveOverhead' => $totalActiveOverhead,
            'overheadCostPerCup' => $overheadCostPerCup,
            'totalNonMaterialPerCup' => $totalNonMaterialPerCup,
        ])->layout('layouts.app', [
            'subpage' => $this->subpage,
            'content' => $this->content,
        ]);
    }
}

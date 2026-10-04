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
    public $content = 'Target Penjualan Tahunan + Target Bulanan & Pencapaian';

    public $year;
    public $annual_sales_target = 100000000;
    public $average_selling_price = 20000;
    public $operating_days = 26;

    protected function rules()
    {
        return [
            'year' => 'required|integer|min:2000|max:2100',
            'annual_sales_target' => 'required|numeric|min:1',
            'average_selling_price' => 'required|numeric|min:1',
            'operating_days' => 'required|integer|min:1|max:31',
        ];
    }

    protected function messages()
    {
        return [
            'year.required' => 'Tahun target penjualan wajib diisi.',
            'year.integer' => 'Tahun harus berupa angka.',
            'annual_sales_target.required' => 'Target penjualan tahunan harus diisi.',
            'annual_sales_target.numeric' => 'Target penjualan tahunan harus berupa angka.',
            'annual_sales_target.min' => 'Target penjualan tahunan minimal Rp1.',
            'average_selling_price.required' => 'Estimasi harga jual rata-rata per cup harus diisi.',
            'average_selling_price.numeric' => 'Harga jual rata-rata per cup harus berupa angka.',
            'average_selling_price.min' => 'Harga jual rata-rata per cup minimal Rp1.',
            'operating_days.required' => 'Jumlah hari kerja harus diisi.',
            'operating_days.integer' => 'Jumlah hari kerja harus berupa angka bulat.',
            'operating_days.min' => 'Jumlah hari kerja minimal 1 hari.',
            'operating_days.max' => 'Jumlah hari kerja maksimal 31 hari.',
        ];
    }

    public function mount()
    {
        $this->year = (int)date('Y');
        $this->loadTargetForYear();
    }

    public function updatedYear()
    {
        $this->loadTargetForYear();
    }

    public function selectYear($newYear)
    {
        $this->year = (int)$newYear;
        $this->loadTargetForYear();
    }

    public function loadTargetForYear()
    {
        $target = TargetSale::where('year', (int)$this->year)->first();
        if ($target) {
            $this->annual_sales_target = (float)$target->annual_sales_target;
            $this->average_selling_price = (float)$target->average_selling_price;
            $this->operating_days = (int)$target->operating_days;
        } else {
            // Check if there is an existing legacy row without a year
            $legacy = TargetSale::whereNull('year')->first();
            if ($legacy && (float)$legacy->target_sales_monthly > 0) {
                // If legacy had monthly cup target, infer values
                $this->annual_sales_target = (float)($legacy->annual_sales_target > 0 ? $legacy->annual_sales_target : 100000000);
                $this->average_selling_price = (float)($legacy->average_selling_price > 0 ? $legacy->average_selling_price : 20000);
                $this->operating_days = (int)$legacy->operating_days;
            } else {
                $this->annual_sales_target = 100000000;
                $this->average_selling_price = 20000;
                $this->operating_days = 26;
            }
        }
    }

    public function saveTarget()
    {
        $this->validate();

        $price = (float)$this->average_selling_price;
        if ($price <= 0) {
            $this->addError('average_selling_price', 'Harga jual rata-rata/cup tidak boleh 0 atau negatif.');
            return;
        }

        $annualSales = (float)$this->annual_sales_target;
        $annualCups = round($annualSales / $price, 2);
        $monthlySales = round($annualSales / 12, 2);
        $monthlyCups = round($annualCups / 12, 2);

        $target = TargetSale::where('year', (int)$this->year)->first();
        if (!$target) {
            $target = new TargetSale();
            $target->user_id = Auth::id() ?? 1;
            $target->year = (int)$this->year;
        }

        $target->annual_sales_target = $annualSales;
        $target->average_selling_price = $price;
        $target->annual_target_cups = $annualCups;
        $target->monthly_sales_target = $monthlySales;
        $target->monthly_target_cups = $monthlyCups;
        $target->target_sales_monthly = $monthlyCups;
        $target->operating_days = (int)$this->operating_days;
        $target->save();

        session()->flash('success', "Target penjualan tahun {$this->year} berhasil disimpan!");
    }

    public function render()
    {
        $price = (float)$this->average_selling_price;
        $annualSales = (float)$this->annual_sales_target;

        $annualTargetCups = $price > 0 ? round($annualSales / $price, 2) : 0;
        $monthlyTargetSales = round($annualSales / 12, 2);
        $monthlyTargetCups = $price > 0 ? round($annualTargetCups / 12, 2) : 0;
        $dailyTarget = ($this->operating_days > 0 && $monthlyTargetCups > 0)
            ? round($monthlyTargetCups / $this->operating_days, 2)
            : 0;

        $targetModel = TargetSale::where('year', (int)$this->year)->first() ?? new TargetSale([
            'year' => (int)$this->year,
            'annual_sales_target' => $annualSales,
            'average_selling_price' => $price,
            'annual_target_cups' => $annualTargetCups,
            'monthly_sales_target' => $monthlyTargetSales,
            'monthly_target_cups' => $monthlyTargetCups,
            'target_sales_monthly' => $monthlyTargetCups,
            'operating_days' => (int)$this->operating_days,
        ]);

        $breakdown = $targetModel->calculateMonthlyBreakdown((int)$this->year);

        $totalActiveLabor = Labor::getTotalActiveSalary();
        $laborCostPerCup = Labor::getCostPerCup((int)$this->year);

        $totalActiveOverhead = Overhead::getTotalActiveNominal();
        $overheadCostPerCup = Overhead::getCostPerCup((int)$this->year);

        $totalNonMaterialPerCup = Overhead::getTotalNonMaterialCostPerCup((int)$this->year);

        // List available years for quick filter
        $existingYears = TargetSale::whereNotNull('year')->pluck('year')->toArray();
        $baseYears = [(int)date('Y') - 1, (int)date('Y'), (int)date('Y') + 1, (int)date('Y') + 2, 2027, 2028];
        $allYears = array_unique(array_merge($existingYears, $baseYears));
        sort($allYears);

        return view('livewire.operational.target-penjualan', [
            'annualTargetCups' => $annualTargetCups,
            'monthlyTargetSales' => $monthlyTargetSales,
            'monthlyTargetCups' => $monthlyTargetCups,
            'dailyTarget' => $dailyTarget,
            'breakdown' => $breakdown,
            'totalActiveLabor' => $totalActiveLabor,
            'laborCostPerCup' => $laborCostPerCup,
            'totalActiveOverhead' => $totalActiveOverhead,
            'overheadCostPerCup' => $overheadCostPerCup,
            'totalNonMaterialPerCup' => $totalNonMaterialPerCup,
            'availableYears' => $allYears,
        ])->layout('layouts.app', [
            'subpage' => $this->subpage,
            'content' => $this->content,
        ]);
    }
}

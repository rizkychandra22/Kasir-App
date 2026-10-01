<?php

namespace App\Livewire\Kasir;

use App\Models\Labor;
use App\Models\TargetSale;
use Livewire\Component;
use Livewire\WithPagination;

class DataLabor extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $subpage = 'Data Tenaga Kerja';
    public $content = 'Kelola Gaji Tenaga Kerja & Alokasi Biaya Labor per Cup';

    public $laborId;
    public $name;
    public $monthly_salary;
    public $status = 'active';
    public $notes;

    public $isEdit = false;
    public $search = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'monthly_salary' => 'required|numeric|min:0',
        'status' => 'required|in:active,inactive',
        'notes' => 'nullable|string',
    ];

    protected $messages = [
        'name.required' => 'Nama Karyawan / Posisi harus diisi.',
        'monthly_salary.required' => 'Gaji / Upah per bulan harus diisi.',
        'monthly_salary.numeric' => 'Gaji / Upah harus berupa angka.',
        'monthly_salary.min' => 'Gaji / Upah tidak boleh kurang dari 0.',
        'status.required' => 'Status harus dipilih.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetInput()
    {
        $this->laborId = null;
        $this->name = '';
        $this->monthly_salary = '';
        $this->status = 'active';
        $this->notes = '';
        $this->isEdit = false;
        $this->resetValidation();
    }

    public function store()
    {
        $this->validate();

        Labor::create([
            'user_id' => auth()->id() ?? 1,
            'name' => $this->name,
            'monthly_salary' => $this->monthly_salary,
            'status' => $this->status,
            'notes' => $this->notes,
        ]);

        session()->flash('success', 'Data tenaga kerja berhasil ditambahkan!');
        $this->resetInput();
    }

    public function edit($id)
    {
        $labor = Labor::findOrFail($id);
        $this->laborId = $labor->id;
        $this->name = $labor->name;
        $this->monthly_salary = (float)$labor->monthly_salary;
        $this->status = $labor->status;
        $this->notes = $labor->notes;
        $this->isEdit = true;
    }

    public function update()
    {
        $this->validate();

        $labor = Labor::findOrFail($this->laborId);
        $labor->update([
            'name' => $this->name,
            'monthly_salary' => $this->monthly_salary,
            'status' => $this->status,
            'notes' => $this->notes,
        ]);

        session()->flash('success', 'Data tenaga kerja berhasil diperbarui!');
        $this->resetInput();
    }

    public function delete($id)
    {
        Labor::findOrFail($id)->delete();
        session()->flash('success', 'Data tenaga kerja berhasil dihapus!');
        $this->resetInput();
    }

    public function toggleStatus($id)
    {
        $labor = Labor::findOrFail($id);
        $labor->status = $labor->status === 'active' ? 'inactive' : 'active';
        $labor->save();
        session()->flash('success', 'Status tenaga kerja ' . $labor->name . ' berhasil diubah!');
    }

    public function render()
    {
        $target = TargetSale::getTargetSettings();
        $targetMonthly = (float)$target->target_sales_monthly;

        $totalActiveSalary = Labor::getTotalActiveSalary();
        $laborCostPerCup = $targetMonthly > 0 ? round($totalActiveSalary / $targetMonthly, 2) : 0;

        $labors = Labor::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('notes', 'like', '%' . $this->search . '%');
        })->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.kasir.data-labor', [
            'labors' => $labors,
            'targetMonthly' => $targetMonthly,
            'totalActiveSalary' => $totalActiveSalary,
            'laborCostPerCup' => $laborCostPerCup,
        ])->layout('layouts.app', [
            'subpage' => $this->subpage,
            'content' => $this->content,
        ]);
    }
}

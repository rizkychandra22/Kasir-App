<?php

namespace App\Livewire\Operational;

use App\Models\Labor;
use App\Models\TargetSale;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class DataLabor extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $title = 'Dashboard';
    public $subpage = 'Data Tenaga Kerja';
    public $linkTitle;
    public $linkSubpage;
    public $content = 'Index Data';

    public $laborId;
    public $name;
    public $monthly_salary;
    public $status = 'active';
    public $notes;

    public function mount()
    {
        $isAdmin = Auth::user()->role == 'Admin';
        $this->linkTitle = ($isAdmin) ? route('admin.dashboard') : route('kasir.dashboard');
        $this->subpage = 'Data Tenaga Kerja';
        $this->linkSubpage = route('kasir.labor');
        $this->content = 'Index Data';
    }

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
            'user_id' => Auth::user()->id,
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
        $targetMonthly = $target->getTargetCupsMonthly();

        $totalActiveSalary = Labor::getTotalActiveSalary();
        $laborCostPerCup = Labor::getCostPerCup();

        $labors = Labor::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('notes', 'like', '%' . $this->search . '%');
        })->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.operational.data-labor', [
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

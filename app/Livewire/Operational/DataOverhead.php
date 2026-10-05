<?php

namespace App\Livewire\Operational;

use App\Models\Overhead;
use App\Models\TargetSale;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class DataOverhead extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $title = 'Dashboard';
    public $subpage = 'Biaya Operasional';
    public $linkTitle;
    public $linkSubpage;
    public $content = 'View Data Biaya Operasional';

    public $overheadId;
    public $name;
    public $category = 'Operational';
    public $nominal_monthly;
    public $status = 'active';
    public $notes;

    public function mount()
    {
        $isAdmin = Auth::user()->role == 'Admin';
        $this->linkTitle = ($isAdmin) ? route('admin.dashboard') : route('kasir.dashboard');
        $this->subpage = 'Biaya Operasional';
        $this->linkSubpage = route('kasir.overhead');
        $this->content = 'View Data Biaya Operasional';
    }

    public $isEdit = false;
    public $search = '';

    protected $rules = [
        'name' => 'required|string|max:255',
        'category' => 'required|string|max:255',
        'nominal_monthly' => 'required|numeric|min:0',
        'status' => 'required|in:active,inactive',
        'notes' => 'nullable|string',
    ];

    protected $messages = [
        'name.required' => 'Nama biaya operasional harus diisi.',
        'category.required' => 'Kategori biaya harus dipilih/diisi.',
        'nominal_monthly.required' => 'Nominal biaya per bulan harus diisi.',
        'nominal_monthly.numeric' => 'Nominal biaya harus berupa angka.',
        'nominal_monthly.min' => 'Nominal biaya tidak boleh kurang dari 0.',
        'status.required' => 'Status harus dipilih.',
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function resetInput()
    {
        $this->overheadId = null;
        $this->name = '';
        $this->category = 'Operational';
        $this->nominal_monthly = '';
        $this->status = 'active';
        $this->notes = '';
        $this->isEdit = false;
        $this->resetValidation();
    }

    public function store()
    {
        $this->validate();

        Overhead::create([
            'user_id' => Auth::user()->id,
            'name' => $this->name,
            'category' => $this->category,
            'nominal_monthly' => $this->nominal_monthly,
            'status' => $this->status,
            'notes' => $this->notes,
        ]);

        session()->flash('success', 'Data biaya operasional berhasil ditambahkan!');
        $this->resetInput();
    }

    public function edit($id)
    {
        $item = Overhead::findOrFail($id);
        $this->overheadId = $item->id;
        $this->name = $item->name;
        $this->category = $item->category;
        $this->nominal_monthly = (float)$item->nominal_monthly;
        $this->status = $item->status;
        $this->notes = $item->notes;
        $this->isEdit = true;
    }

    public function update()
    {
        $this->validate();

        $item = Overhead::findOrFail($this->overheadId);
        $item->update([
            'name' => $this->name,
            'category' => $this->category,
            'nominal_monthly' => $this->nominal_monthly,
            'status' => $this->status,
            'notes' => $this->notes,
        ]);

        session()->flash('success', 'Data biaya operasional berhasil diperbarui!');
        $this->resetInput();
    }

    public function delete($id)
    {
        Overhead::findOrFail($id)->delete();
        session()->flash('success', 'Data biaya operasional berhasil dihapus!');
        $this->resetInput();
    }

    public function toggleStatus($id)
    {
        $item = Overhead::findOrFail($id);
        $item->status = $item->status === 'active' ? 'inactive' : 'active';
        $item->save();
        session()->flash('success', 'Status biaya ' . $item->name . ' berhasil diubah!');
    }

    public function render()
    {
        $target = TargetSale::getTargetSettings();
        $targetMonthly = $target->getTargetCupsMonthly();

        $totalActiveNominal = Overhead::getTotalActiveNominal();
        $overheadCostPerCup = Overhead::getCostPerCup();

        $overheads = Overhead::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('category', 'like', '%' . $this->search . '%')
                  ->orWhere('notes', 'like', '%' . $this->search . '%');
        })->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.operational.data-overhead', [
            'overheads' => $overheads,
            'targetMonthly' => $targetMonthly,
            'totalActiveNominal' => $totalActiveNominal,
            'overheadCostPerCup' => $overheadCostPerCup,
        ])->layout('layouts.app', [
            'subpage' => $this->subpage,
            'content' => $this->content,
        ]);
    }
}

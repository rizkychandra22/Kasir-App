<?php

namespace App\Livewire\Kasir;

use App\Models\Overhead;
use App\Models\TargetSale;
use Livewire\Component;
use Livewire\WithPagination;

class DataOverhead extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $subpage = 'Data Biaya Operasional / Overhead';
    public $content = 'Kelola Biaya Operasional & Alokasi Overhead per Cup';

    public $overheadId;
    public $name;
    public $category = 'Operational';
    public $nominal_monthly;
    public $status = 'active';
    public $notes;

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
            'user_id' => auth()->id() ?? 1,
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
        $targetMonthly = (float)$target->target_sales_monthly;

        $totalActiveNominal = Overhead::getTotalActiveNominal();
        $overheadCostPerCup = $targetMonthly > 0 ? round($totalActiveNominal / $targetMonthly, 2) : 0;

        $overheads = Overhead::when($this->search, function ($query) {
            $query->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('category', 'like', '%' . $this->search . '%')
                  ->orWhere('notes', 'like', '%' . $this->search . '%');
        })->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.kasir.data-overhead', [
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

<?php

namespace App\Livewire\Material;

use App\Models\Bahan;
use App\Models\BahanStockMovement;
use App\Services\UnitConversionService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DataBahan extends Component
{
    public $name_bahan, $purchase_unit = 'kg', $purchase_qty = 1, $base_unit = 'gram', $stock = 1000, $price = 0, $description, $status = 'active', $bahanId;
    public $isEdit = false;

    // Stock adjustment properties
    public $adjustBahanId, $adjustBahanName, $adjustType = 'in', $adjustQty = 1, $adjustUnit = 'kg', $adjustNotes;
    
    // History & Detail properties
    public $selectedBahanForHistory, $selectedBahanForDetail;

    public $title = 'Dashboard';
    public $subpage = 'Data Master Bahan';
    public $linkTitle;
    public $linkSubpage;
    public $content = 'Index Data';

    public function mount()
    {
        $isAdmin = Auth::user()->role == 'Admin';
        $this->linkTitle = ($isAdmin) ? route('admin.dashboard') : route('kasir.dashboard');
        $this->subpage = 'Data Master Bahan';
        $this->linkSubpage = route('kasir.bahan');
        $this->content = 'Index Data';
        $this->calculateStockFromPurchase();
    }

    public function updatedPurchaseUnit()
    {
        $this->base_unit = UnitConversionService::getBaseUnit($this->purchase_unit);
        $this->calculateStockFromPurchase();
    }

    public function updatedPurchaseQty()
    {
        $this->calculateStockFromPurchase();
    }

    public function calculateStockFromPurchase()
    {
        if (is_numeric($this->purchase_qty) && (float)$this->purchase_qty > 0) {
            $converted = UnitConversionService::convertToBaseUnit($this->purchase_qty, $this->purchase_unit);
            $this->stock = $converted['amount'];
            $this->base_unit = $converted['base_unit'];
        }
    }

    public function resetInput()
    {
        $this->name_bahan = '';
        $this->purchase_unit = 'kg';
        $this->purchase_qty = 1;
        $this->base_unit = 'gram';
        $this->stock = 1000;
        $this->price = 0;
        $this->description = '';
        $this->status = 'active';
        $this->bahanId = null;
        $this->isEdit = false;
        $this->resetValidation();
        $this->calculateStockFromPurchase();
    }

    public function store()
    {
        $this->validate([
            'name_bahan' => 'required|min:2',
            'purchase_unit' => 'required|string',
            'purchase_qty' => 'required|numeric|gt:0',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $converted = UnitConversionService::convertToBaseUnit($this->purchase_qty, $this->purchase_unit);
        $baseStock = $converted['amount'];
        $baseUnit = $converted['base_unit'];

        $costPerBase = $baseStock > 0 ? ((float)$this->price / $baseStock) : 0;

        $bahan = Bahan::create([
            'user_id' => Auth::user()->id,
            'name_bahan' => $this->name_bahan,
            'unit' => $this->purchase_unit,
            'purchase_unit' => $this->purchase_unit,
            'purchase_qty' => (float)$this->purchase_qty,
            'base_unit' => $baseUnit,
            'stock' => $baseStock,
            'price' => (float)$this->price,
            'cost_per_base_unit' => $costPerBase,
            'description' => $this->description,
            'status' => $this->status,
        ]);

        if ($baseStock > 0) {
            BahanStockMovement::create([
                'bahan_id' => $bahan->id,
                'user_id' => Auth::user()->id,
                'type' => 'in',
                'qty' => $baseStock,
                'stock_before' => 0,
                'stock_after' => $baseStock,
                'reference' => 'INIT-STOCK',
                'notes' => "Stok awal {$this->purchase_qty} {$this->purchase_unit} ({$baseStock} {$baseUnit})",
            ]);
        }

        session()->flash('success', "Master Bahan {$this->name_bahan} berhasil ditambahkan! Stok awal: {$baseStock} {$baseUnit}.");
        $this->resetInput();
        $this->dispatch('close-modal');
    }

    public function edit($id)
    {
        $bahan = Bahan::findOrFail($id);
        $this->bahanId = $id;
        $this->name_bahan = $bahan->name_bahan;
        $this->purchase_unit = $bahan->purchase_unit ?? $bahan->unit;
        $this->purchase_qty = (float)($bahan->purchase_qty ?? 1);
        $this->base_unit = $bahan->base_unit ?? UnitConversionService::getBaseUnit($this->purchase_unit);
        $this->stock = (float)$bahan->stock;
        $this->price = (float)$bahan->price;
        $this->description = $bahan->description;
        $this->status = $bahan->status ?? 'active';
        $this->isEdit = true;
    }

    public function update()
    {
        $this->validate([
            'name_bahan' => 'required|min:2',
            'purchase_unit' => 'required|string',
            'stock' => 'required|numeric|min:0',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $bahan = Bahan::findOrFail($this->bahanId);
        $oldStock = (float)$bahan->stock;
        $newStock = (float)$this->stock;
        $baseUnit = UnitConversionService::getBaseUnit($this->purchase_unit);
        
        $purchaseQty = (float)($this->purchase_qty ?? 1);
        $convertedPurchase = UnitConversionService::convertToBaseUnit($purchaseQty, $this->purchase_unit);
        $purchaseQtyInBase = (float)$convertedPurchase['amount'];
        $costPerBase = $purchaseQtyInBase > 0 ? ((float)$this->price / $purchaseQtyInBase) : 0;

        $bahan->update([
            'name_bahan' => $this->name_bahan,
            'unit' => $this->purchase_unit,
            'purchase_unit' => $this->purchase_unit,
            'purchase_qty' => $purchaseQty,
            'base_unit' => $baseUnit,
            'stock' => $newStock,
            'price' => (float)$this->price,
            'cost_per_base_unit' => $costPerBase,
            'description' => $this->description,
            'status' => $this->status,
        ]);

        if ($oldStock != $newStock) {
            $diff = $newStock - $oldStock;
            BahanStockMovement::create([
                'bahan_id' => $bahan->id,
                'user_id' => Auth::user()->id,
                'type' => $diff > 0 ? 'in' : 'out',
                'qty' => abs($diff),
                'stock_before' => $oldStock,
                'stock_after' => $newStock,
                'reference' => 'EDIT-ADJ',
                'notes' => 'Penyesuaian stok manual form edit',
            ]);
        }

        session()->flash('success', "Master Bahan {$this->name_bahan} berhasil diperbarui!");
        $this->resetInput();
        $this->dispatch('close-modal');
    }

    public function viewDetail($id)
    {
        $this->selectedBahanForDetail = Bahan::with('user')->findOrFail($id);
    }

    public function openAdjustStock($id)
    {
        $bahan = Bahan::findOrFail($id);
        $this->adjustBahanId = $bahan->id;
        $this->adjustBahanName = $bahan->name_bahan;
        $this->adjustType = 'in';
        $this->adjustQty = 1;
        $this->adjustUnit = $bahan->purchase_unit ?? $bahan->unit;
        $this->adjustNotes = 'Penambahan stok bahan';
    }

    public function saveAdjustStock()
    {
        $this->validate([
            'adjustQty' => 'required|numeric|gt:0',
            'adjustUnit' => 'required|string',
            'adjustType' => 'required|in:in,out,adjustment',
            'adjustNotes' => 'nullable|string',
        ]);

        $bahan = Bahan::findOrFail($this->adjustBahanId);
        $oldStock = (float)$bahan->stock;

        // Check unit compatibility
        if (!UnitConversionService::areUnitsCompatible($this->adjustUnit, $bahan->base_unit)) {
            session()->flash('danger', "Satuan '{$this->adjustUnit}' tidak kompatibel dengan satuan dasar bahan '{$bahan->base_unit}'.");
            return;
        }

        $converted = UnitConversionService::convertToBaseUnit($this->adjustQty, $this->adjustUnit);
        $qtyInBaseUnit = $converted['amount'];

        if ($this->adjustType === 'in') {
            $newStock = $oldStock + $qtyInBaseUnit;
        } elseif ($this->adjustType === 'out') {
            if ($oldStock < $qtyInBaseUnit) {
                session()->flash('danger', "Pengurangan ({$qtyInBaseUnit} {$bahan->base_unit}) melebihi stok yang tersedia ({$oldStock} {$bahan->base_unit})!");
                return;
            }
            $newStock = $oldStock - $qtyInBaseUnit;
        } else { // adjustment
            $newStock = $qtyInBaseUnit;
        }

        $bahan->update(['stock' => $newStock]);

        BahanStockMovement::create([
            'bahan_id' => $bahan->id,
            'user_id' => Auth::user()->id,
            'type' => $this->adjustType,
            'qty' => $qtyInBaseUnit,
            'stock_before' => $oldStock,
            'stock_after' => $newStock,
            'reference' => 'ADJ-' . time(),
            'notes' => ($this->adjustNotes ? $this->adjustNotes . " " : "") . "({$this->adjustQty} {$this->adjustUnit} = {$qtyInBaseUnit} {$bahan->base_unit})",
        ]);

        session()->flash('success', "Stok bahan {$bahan->name_bahan} berhasil diperbarui! Stok sekarang: {$newStock} {$bahan->base_unit}.");
        $this->dispatch('close-modal');
    }

    public function viewHistory($id)
    {
        $this->selectedBahanForHistory = Bahan::with('stockMovements.user')->findOrFail($id);
    }

    public function toggleStatus($id)
    {
        $bahan = Bahan::findOrFail($id);
        $newStatus = $bahan->status === 'active' ? 'inactive' : 'active';
        $bahan->update(['status' => $newStatus]);
        session()->flash('success', "Status bahan {$bahan->name_bahan} diubah menjadi " . strtoupper($newStatus));
    }

    public function delete($id)
    {
        $bahan = Bahan::withCount('products')->findOrFail($id);

        if ($bahan->products_count > 0) {
            $bahan->update(['status' => 'inactive']);
            session()->flash('danger', "Bahan '{$bahan->name_bahan}' sedang digunakan oleh {$bahan->products_count} produk. Status bahan diubah menjadi NONAKTIF alih-alih dihapus agar data produk tetap aman.");
            return;
        }

        $bahan->delete();
        session()->flash('danger', 'Master Bahan berhasil dihapus.');
    }

    public function render()
    {
        return view('livewire.material.data-bahan', [
            'bahans' => Bahan::with('user')->withCount('products')->latest()->get(),
        ])->layout('layouts.app', [
            'subpage' => $this->subpage,
            'content' => $this->content,
        ]);
    }
}

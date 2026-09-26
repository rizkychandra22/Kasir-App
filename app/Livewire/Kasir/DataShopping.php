<?php

namespace App\Livewire\Kasir;

use App\Models\Bahan;
use App\Models\BahanStockMovement;
use App\Models\Product;
use App\Models\Shopping;
use App\Models\ShoppingDetail;
use App\Services\UnitConversionService;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DataShopping extends Component
{
    public $cart = []; 
    public $search_prd;
    public $sales_type = 'offline'; // 'offline' or 'online'
    public $pay = 0;
    public $total_price = 0;
    public $change = 0;
    public $selectedShopping = null;

    public $title = 'Dashboard';
    public $subpage = 'Overview Kasir';
    public $linkTitle;
    public $linkSubpage;
    public $content = 'Data Penjualan';

    public function mount()
    {
        $this->linkTitle = route('kasir.dashboard');
        $this->linkSubpage = route('kasir.shopping');
    }

    public function resetInput()
    {
        $this->cart = [];
        $this->search_prd = '';
        $this->pay = 0;
        $this->total_price = 0;
        $this->change = 0;
        $this->resetValidation();
    }

    public function setSalesType($type)
    {
        if (!in_array($type, ['offline', 'online'])) return;
        if ($this->sales_type === $type) return;

        if ($type === 'online') {
            foreach ($this->cart as $productId => $item) {
                $product = Product::find($productId);
                if ($product && !$product->hasPriceForSalesType('online')) {
                    session()->flash('danger', "Harga online untuk produk {$product->name_prd} belum diatur.");
                }
            }
        }

        $this->sales_type = $type;

        foreach ($this->cart as $productId => $item) {
            $product = Product::find($productId);
            if ($product) {
                $itemPrice = $product->getPriceForSalesType($this->sales_type);
                $this->cart[$productId]['price'] = $itemPrice;
            }
        }
        $this->calculateTotal();
    }

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        
        if (!$product || $product->stock <= 0) {
            session()->flash('danger', 'Stok produk tidak mencukupi!');
            return;
        }

        if ($product->sales_type !== 'all' && $product->sales_type !== $this->sales_type) {
            session()->flash('danger', "Produk {$product->name_prd} hanya dijual secara " . strtoupper($product->sales_type));
            return;
        }

        if ($this->sales_type === 'online' && !$product->hasPriceForSalesType('online')) {
            session()->flash('danger', "Harga online untuk produk {$product->name_prd} belum diatur.");
            return;
        }

        $effectivePrice = $product->getPriceForSalesType($this->sales_type);

        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['qty']++;
            $this->cart[$productId]['price'] = $effectivePrice;
        } else {
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name_prd,
                'price' => $effectivePrice,
                'qty' => 1,
            ];
        }
        $this->calculateTotal();
    }

    public function removeFromCart($productId)
    {
        unset($this->cart[$productId]);
        $this->calculateTotal();
    }

    public function calculateTotal()
    {
        $this->total_price = array_sum(array_map(function($item) {
            return $item['price'] * $item['qty'];
        }, $this->cart));
        
        $this->updatedPay();
    }

    public function updatedPay()
    {
        $this->change = (int)$this->pay - (int)$this->total_price;
    }

    public function store()
    {
        if (empty($this->cart)) return;
        $this->validate([
            'pay' => 'required|numeric|min:' . $this->total_price,
            'sales_type' => 'required|in:online,offline',
        ]);

        // 1. VALIDASI STOK BAHAN TERLEBIH DAHULU BEFORE TRANSACTION
        $requiredMaterials = []; // bahan_id => required_qty_in_base_unit

        foreach ($this->cart as $item) {
            $product = Product::with('bahans')->find($item['id']);
            if (!$product) continue;

            $productQty = (float)$item['qty'];

            foreach ($product->bahans as $b) {
                $bahanId = $b->id;
                $recipeQtyInBaseUnit = (float)($b->pivot->quantity ?? 1);
                $needed = $recipeQtyInBaseUnit * $productQty;

                if (!isset($requiredMaterials[$bahanId])) {
                    $requiredMaterials[$bahanId] = 0;
                }
                $requiredMaterials[$bahanId] += $needed;
            }
        }

        // Check if any required material stock is insufficient
        foreach ($requiredMaterials as $bahanId => $neededQty) {
            $bahan = Bahan::find($bahanId);
            if (!$bahan || (float)$bahan->stock < $neededQty) {
                $bahanName = $bahan ? $bahan->name_bahan : 'Bahan';
                $available = $bahan ? number_format((float)$bahan->stock, 2, ',', '.') : '0';
                $neededFormatted = number_format($neededQty, 2, ',', '.');
                $unit = $bahan ? ($bahan->base_unit ?? $bahan->unit) : '';

                session()->flash('danger', "Stok bahan '{$bahanName}' tidak mencukupi. Stok tersedia: {$available} {$unit}, Kebutuhan: {$neededFormatted} {$unit}");
                return;
            }
        }

        try {
            DB::transaction(function () {
                $invoice = 'INV-' . date('YmdHis');

                $shopping = Shopping::create([
                    'invoice' => $invoice,
                    'sales_type' => $this->sales_type,
                    'user_id' => auth()->id(),
                    'total_price' => (int)$this->total_price,
                    'pay' => (int)$this->pay,
                    'change' => (int)$this->change,
                ]);

                foreach ($this->cart as $item) {
                    $itemQty = (int)$item['qty'];

                    ShoppingDetail::create([
                        'shopping_id' => $shopping->id,
                        'product_id'  => $item['id'],
                        'qty'         => $itemQty, 
                        'price'       => (int)$item['price'],
                        'subtotal'    => (int)$item['price'] * $itemQty,
                    ]);

                    $product = Product::with('bahans')->find($item['id']);
                    if ($product) {
                        $product->decrement('stock', $itemQty);

                        // Automatic material stock deduction based on recipe quantity * product qty sold
                        foreach ($product->bahans as $b) {
                            $recipeQtyInBaseUnit = (float)($b->pivot->quantity ?? 1);
                            $usage = $recipeQtyInBaseUnit * $itemQty;
                            
                            $bahan = Bahan::lockForUpdate()->find($b->id);
                            if ($bahan) {
                                $stockBefore = (float)$bahan->stock;
                                $stockAfter = max(0, $stockBefore - $usage);

                                $bahan->update(['stock' => $stockAfter]);

                                BahanStockMovement::create([
                                    'bahan_id' => $bahan->id,
                                    'user_id' => auth()->id(),
                                    'type' => 'out',
                                    'qty' => $usage,
                                    'stock_before' => $stockBefore,
                                    'stock_after' => $stockAfter,
                                    'reference' => $invoice,
                                    'notes' => "{$product->name_prd} x {$itemQty}",
                                ]);
                            }
                        }
                    }
                }
            });

            $this->resetInput();
            session()->flash('success', 'Transaksi Berhasil Disimpan & Stok Bahan Berhasil Diperbarui!');
            $this->dispatch('close-modal');

        } catch (\Exception $e) {
            session()->flash('danger', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function viewDetail($id)
    {
        $this->selectedShopping = Shopping::with(['details.product', 'user'])->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.kasir.data-shopping', [
            'shoppings' => Shopping::with('user')->orderBy('created_at', 'DESC')->get(),
            'products' => Product::where('stock', '>', 0)
                          ->whereIn('sales_type', [$this->sales_type, 'all'])
                          ->where('name_prd', 'like', '%'.$this->search_prd.'%')
                          ->get(),
        ])->layout('layouts.app', [
            'subpage' => 'Overview Kasir',
            'content' => 'Data Penjualan'
        ]);
    }
}

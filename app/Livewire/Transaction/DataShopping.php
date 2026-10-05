<?php

namespace App\Livewire\Transaction;

use App\Models\Bahan;
use App\Models\BahanStockMovement;
use App\Models\Product;
use App\Models\Shopping;
use App\Models\ShoppingDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DataShopping extends Component
{
    public $cart = []; 
    public $search_prd;
    public $sales_type = 'offline'; // 'offline' or 'online'
    public $payment_method = 'cash'; // 'cash' or 'qris'
    public $pay = 0;
    public $total_price = 0;
    public $change = 0;
    public $selectedShopping = null;

    public $title = 'Dashboard';
    public $subpage = 'Data Penjualan';
    public $linkTitle;
    public $linkSubpage;
    public $content = 'View Data Penjualan';

    public function mount()
    {
        $isAdmin = Auth::user()->role == 'Admin';
        $this->linkTitle = ($isAdmin) ? route('admin.dashboard') : route('kasir.dashboard');
        $this->subpage = 'Data Penjualan';
        $this->linkSubpage = route('kasir.shopping');
        $this->content = 'View Data Penjualan';
    }

    public function resetInput()
    {
        $this->cart = [];
        $this->search_prd = '';
        $this->payment_method = 'cash';
        $this->pay = 0;
        $this->total_price = 0;
        $this->change = 0;
        $this->resetValidation();
    }

    public function setPaymentMethod($method)
    {
        if (!in_array($method, ['cash', 'qris'])) return;
        $this->payment_method = $method;

        if ($method === 'qris') {
            $this->pay = (int)$this->total_price;
            $this->change = 0;
        } else {
            $this->updatedPay();
        }
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
            $this->payment_method = 'cash';
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
        
        if (!$product) {
            session()->flash('danger', 'Produk tidak ditemukan!');
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
        
        if ($this->payment_method === 'qris') {
            $this->pay = (int)$this->total_price;
            $this->change = 0;
        } else {
            $this->updatedPay();
        }
    }

    public function updatedPay()
    {
        if ($this->payment_method === 'qris') {
            $this->pay = (int)$this->total_price;
            $this->change = 0;
        } else {
            $this->change = (int)$this->pay - (int)$this->total_price;
        }
    }

    public function store()
    {
        if (empty($this->cart)) return;

        if ($this->payment_method === 'qris') {
            $this->pay = (int)$this->total_price;
            $this->change = 0;
        }

        $rules = [
            'pay' => 'required|numeric|min:' . $this->total_price,
            'sales_type' => 'required|in:online,offline',
            'payment_method' => 'required|in:cash,qris',
        ];

        $this->validate($rules);

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
                    'payment_method' => $this->payment_method,
                    'user_id' => Auth::user()->id,
                    'total_price' => (int)$this->total_price,
                    'pay' => (int)$this->pay,
                    'change' => (int)$this->change,
                ]);

                foreach ($this->cart as $item) {
                    $itemQty = (int)$item['qty'];
                    $product = Product::with('bahans')->find($item['id']);
                    $itemMaterialCost = $product ? ($product->calculateTotalRecipeCost() * $itemQty) : 0;

                    ShoppingDetail::create([
                        'shopping_id'   => $shopping->id,
                        'product_id'    => $item['id'],
                        'qty'           => $itemQty, 
                        'price'         => (int)$item['price'],
                        'subtotal'      => (int)$item['price'] * $itemQty,
                        'material_cost' => $itemMaterialCost,
                    ]);

                    if ($product) {
                        // Automatic material stock deduction based on recipe quantity * product qty sold
                        foreach ($product->bahans as $b) {
                            $recipeQtyInBaseUnit = (float)($b->pivot->quantity ?? 1);
                            $usage = $recipeQtyInBaseUnit * $itemQty;
                            
                            $bahan = Bahan::lockForUpdate()->find($b->id);
                            if ($bahan) {
                                $stockBefore = (float)$bahan->stock;
                                $stockAfter = max(0, $stockBefore - $usage);

                                $bahan->update(['stock' => $stockAfter]);

                                $costBase = (float)$bahan->cost_per_base_unit;

                                BahanStockMovement::create([
                                    'bahan_id'           => $bahan->id,
                                    'user_id'            => Auth::user()->id,
                                    'type'               => 'out',
                                    'qty'                => $usage,
                                    'stock_before'       => $stockBefore,
                                    'stock_after'        => $stockAfter,
                                    'cost_per_base_unit' => $costBase,
                                    'total_cost'         => $usage * $costBase,
                                    'reference'          => $invoice,
                                    'notes'              => "{$product->name_prd} x {$itemQty}",
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

    public static function deductMaterialStockForProduct($productId, $itemQty, $invoice = 'TRX-TEST', $notes = null)
    {
        $product = Product::with('bahans')->find($productId);
        if (!$product) return 0;

        $totalTransactionMaterialCost = 0;

        foreach ($product->bahans as $b) {
            $recipeQtyInBaseUnit = (float)($b->pivot->quantity ?? 1);
            $usage = $recipeQtyInBaseUnit * $itemQty;
            
            $bahan = Bahan::lockForUpdate()->find($b->id);
            if ($bahan) {
                $stockBefore = (float)$bahan->stock;
                $stockAfter = max(0, $stockBefore - $usage);

                $bahan->update(['stock' => $stockAfter]);

                $costBase = (float)$bahan->cost_per_base_unit;
                $movementCost = $usage * $costBase;
                $totalTransactionMaterialCost += $movementCost;

                BahanStockMovement::create([
                    'bahan_id'           => $bahan->id,
                    'user_id'            => Auth::user()->id,
                    'type'               => 'out',
                    'qty'                => $usage,
                    'stock_before'       => $stockBefore,
                    'stock_after'        => $stockAfter,
                    'cost_per_base_unit' => $costBase,
                    'total_cost'         => $movementCost,
                    'reference'          => $invoice,
                    'notes'              => $notes ?? "{$product->name_prd} x {$itemQty}",
                ]);
            }
        }

        return $totalTransactionMaterialCost;
    }

    public static function validateStockForCart($cartItems)
    {
        $requiredMaterials = [];

        foreach ($cartItems as $item) {
            $productId = $item['product_id'] ?? $item['id'] ?? null;
            $productQty = (float)($item['qty'] ?? $item['quantity'] ?? 1);
            $product = Product::with('bahans')->find($productId);
            if (!$product) continue;

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

        foreach ($requiredMaterials as $bahanId => $neededQty) {
            $bahan = Bahan::find($bahanId);
            if (!$bahan || (float)$bahan->stock < $neededQty) {
                $bahanName = $bahan ? $bahan->name_bahan : 'Bahan';
                $available = $bahan ? number_format((float)$bahan->stock, 2, ',', '.') : '0';
                $neededFormatted = number_format($neededQty, 2, ',', '.');
                $unit = $bahan ? ($bahan->base_unit ?? $bahan->unit) : '';

                return "Stok bahan '{$bahanName}' tidak mencukupi. Stok tersedia: {$available} {$unit}, Kebutuhan: {$neededFormatted} {$unit}";
            }
        }

        return null;
    }

    public function viewDetail($id)
    {
        $this->selectedShopping = Shopping::with(['details.product', 'user'])->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.transaction.data-shopping', [
            'shoppings' => Shopping::with('user')->orderBy('created_at', 'DESC')->get(),
            'products' => Product::whereIn('sales_type', [$this->sales_type, 'all'])
                          ->where('name_prd', 'like', '%'.$this->search_prd.'%')
                          ->get(),
        ])->layout('layouts.app', [
            'subpage' => $this->subpage,
            'content' => $this->content,
        ]);
    }
}

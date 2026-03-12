<?php

namespace App\Livewire\Kasir;

use App\Models\Product;
use App\Models\Shopping;
use App\Models\ShoppingDetail;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class DataShopping extends Component
{
    public $cart = []; 
    public $search_prd;
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

    public function addToCart($productId)
    {
        $product = Product::find($productId);
        
        if (!$product || $product->stock <= 0) {
            session()->flash('danger', 'Stok tidak mencukupi!');
            return;
        }

        // Jika produk sudah ada di keranjang, tambah QTY
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['qty']++;
        } else {
            $this->cart[$productId] = [
                'id' => $product->id,
                'name' => $product->name_prd,
                'price' => $product->price,
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
        ]);

        try {
            DB::transaction(function () {
                $shopping = Shopping::create([
                    'invoice' => 'INV-' . date('YmdHis'),
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

                    $product = Product::find($item['id']);
                    if ($product) {
                        $product->decrement('stock', $itemQty);
                    }
                }
            });

            $this->resetInput();
            session()->flash('success', 'Transaksi Berhasil Disimpan!');
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
                          ->where('name_prd', 'like', '%'.$this->search_prd.'%')
                          ->get(),
        ])->layout('layouts.app', [
            'subpage' => 'Overview Kasir',
            'content' => 'Data Penjualan'
        ]);
    }
}

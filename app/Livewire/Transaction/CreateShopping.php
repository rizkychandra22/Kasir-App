<?php

namespace App\Livewire\Transaction;

use App\Models\Bahan;
use App\Models\BahanStockMovement;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Shopping;
use App\Models\ShoppingDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CreateShopping extends Component
{
    // Mode: 'quick' (Quick Order) or 'open_bill' (Open Bill)
    public $order_mode = 'quick';

    // Open Bill inputs (NO table number!)
    public $customer_name = '';
    public $notes = '';

    // Quick Order & Transaction inputs
    public $sales_type = 'offline'; // 'offline' or 'online'
    public $payment_method = 'cash'; // 'cash' or 'qris'
    public $qris_confirmed = false;
    public $pay = 0;
    public $total_price = 0;
    public $change = 0;

    // Cart and catalog
    public $cart = [];
    public $search_prd = '';
    public $selected_category = '';

    // Modals / Result states
    public $createdOrder = null;
    public $createdShopping = null;
    public $showSlipModal = false;
    public $showReceiptModal = false;

    // Page metadata
    public $title = 'Dashboard';
    public $subpage = 'Transaksi Baru';
    public $linkTitle;
    public $linkSubpage;

    public function mount()
    {
        $isAdmin = Auth::user()->role === 'Admin';
        $this->linkTitle = ($isAdmin) ? route('admin.dashboard') : route('kasir.dashboard');
        $this->linkSubpage = route('kasir.shopping.create');

        // Optional pre-selected mode from query param
        if (request()->query('mode') === 'open_bill') {
            $this->order_mode = 'open_bill';
        }
    }

    public function setOrderMode($mode)
    {
        if (!in_array($mode, ['quick', 'open_bill'])) return;
        $this->order_mode = $mode;

        if ($mode === 'open_bill') {
            // Open bill operates in cafe offline dine-in
            $this->sales_type = 'offline';
        }
        $this->calculateTotal();
    }

    public function resetCart()
    {
        $this->cart = [];
        $this->customer_name = '';
        $this->notes = '';
        $this->pay = 0;
        $this->change = 0;
        $this->total_price = 0;
        $this->qris_confirmed = false;
        $this->resetValidation();
    }

    public function confirmQris()
    {
        $this->qris_confirmed = true;
    }

    public function setPaymentMethod($method)
    {
        if (!in_array($method, ['cash', 'qris'])) return;
        $this->payment_method = $method;
        $this->qris_confirmed = false;

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
        if ($this->order_mode === 'open_bill') return; // Open Bill is offline
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

        $this->qris_confirmed = false;
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

        $effectiveSalesType = $this->order_mode === 'open_bill' ? 'offline' : $this->sales_type;

        if ($product->sales_type !== 'all' && $product->sales_type !== $effectiveSalesType) {
            session()->flash('danger', "Produk {$product->name_prd} hanya dijual secara " . strtoupper($product->sales_type));
            return;
        }

        if ($effectiveSalesType === 'online' && !$product->hasPriceForSalesType('online')) {
            session()->flash('danger', "Harga online untuk produk {$product->name_prd} belum diatur.");
            return;
        }

        $effectivePrice = $product->getPriceForSalesType($effectiveSalesType);

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

    public function updateQty($productId, $qty)
    {
        $qty = (int)$qty;
        if ($qty <= 0) {
            $this->removeFromCart($productId);
            return;
        }
        if (isset($this->cart[$productId])) {
            $this->cart[$productId]['qty'] = $qty;
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
        $this->total_price = array_sum(array_map(function ($item) {
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

    public function storeQuickOrder()
    {
        if (empty($this->cart)) {
            session()->flash('danger', 'Keranjang belanja masih kosong!');
            return;
        }

        if ($this->payment_method === 'qris') {
            $this->pay = (int)$this->total_price;
            $this->change = 0;
        }

        $rules = [
            'pay' => 'required|numeric|min:' . $this->total_price,
            'sales_type' => 'required|in:online,offline',
            'payment_method' => 'required|in:cash,qris',
        ];

        $messages = [];
        if ($this->sales_type === 'offline' && $this->payment_method === 'qris') {
            $rules['qris_confirmed'] = 'accepted';
            $messages['qris_confirmed.accepted'] = 'Pastikan pembayaran QRIS sudah diterima sebelum menyelesaikan transaksi.';
        }

        $this->validate($rules, $messages);

        // Validasi stok bahan
        $stockError = DataShopping::validateStockForCart($this->cart);
        if ($stockError) {
            session()->flash('danger', $stockError);
            return;
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
                                    'notes'              => "Quick Order: {$product->name_prd} x {$itemQty}",
                                ]);
                            }
                        }
                    }
                }

                $this->createdShopping = Shopping::with(['details.product', 'user'])->find($shopping->id);
            });

            session()->flash('success', 'Transaksi Quick Order berhasil disimpan!');
            $this->showReceiptModal = true;
            $this->cart = [];
            $this->calculateTotal();

        } catch (\Exception $e) {
            session()->flash('danger', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function storeOpenBill()
    {
        $this->validate([
            'customer_name' => 'required|min:2',
        ], [
            'customer_name.required' => 'Nama pemesan wajib diisi untuk Open Bill.',
            'customer_name.min' => 'Nama pemesan minimal 2 karakter.',
        ]);

        if (empty($this->cart)) {
            session()->flash('danger', 'Pilih minimal satu menu untuk Open Bill.');
            return;
        }

        // Validasi stok bahan
        $stockError = DataShopping::validateStockForCart($this->cart);
        if ($stockError) {
            session()->flash('danger', $stockError);
            return;
        }

        try {
            DB::transaction(function () {
                $orderNumber = Order::generateOrderNumber();

                $order = Order::create([
                    'order_number'  => $orderNumber,
                    'customer_name' => trim($this->customer_name),
                    'user_id'       => Auth::user()->id,
                    'shopping_id'   => null,
                    'total_amount'  => (int)$this->total_price,
                    'status'        => 'open',
                    'notes'         => $this->notes,
                ]);

                foreach ($this->cart as $item) {
                    $itemQty = (int)$item['qty'];
                    $product = Product::with('bahans')->find($item['id']);
                    $itemMaterialCost = $product ? ($product->calculateTotalRecipeCost() * $itemQty) : 0;

                    OrderDetail::create([
                        'order_id'      => $order->id,
                        'product_id'    => $item['id'],
                        'product_name'  => $product ? $product->name_prd : $item['name'],
                        'qty'           => $itemQty,
                        'unit_price'    => (int)$item['price'],
                        'subtotal'      => (int)$item['price'] * $itemQty,
                        'material_cost' => $itemMaterialCost, // Snapshot HPP bahan saat open bill dibuat
                    ]);

                    // Potong stok bahan baku SEKALI saat Open Bill dibuat
                    if ($product) {
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
                                    'reference'          => $orderNumber,
                                    'notes'              => "Open Bill {$orderNumber}: {$product->name_prd} x {$itemQty}",
                                ]);
                            }
                        }
                    }
                }

                $this->createdOrder = Order::with(['details.product', 'user'])->find($order->id);
            });

            session()->flash('success', "Open Bill #{$this->createdOrder->order_number} berhasil dibuat!");
            $this->showSlipModal = true;
            $this->cart = [];
            $this->customer_name = '';
            $this->notes = '';
            $this->calculateTotal();

        } catch (\Exception $e) {
            session()->flash('danger', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function render()
    {
        $effectiveSalesType = $this->order_mode === 'open_bill' ? 'offline' : $this->sales_type;

        $productsQuery = Product::whereIn('sales_type', [$effectiveSalesType, 'all'])
            ->where('name_prd', 'like', '%' . $this->search_prd . '%');

        if (!empty($this->selected_category)) {
            $productsQuery->where('category_id', $this->selected_category);
        }

        $catalogReference = Product::whereIn('sales_type', ['offline', 'all'])->take(8)->get();

        return view('livewire.transaction.create-shopping', [
            'products' => $productsQuery->get(),
            'categories' => Category::all(),
            'catalogReference' => $catalogReference,
        ])->layout('layouts.app', [
            'subpage' => $this->subpage,
            'content' => 'Sistem Pemesanan Kasir',
        ]);
    }
}

<?php

namespace App\Livewire\Kasir;

use App\Models\Bahan;
use App\Models\Category;
use App\Models\Product;
use App\Services\UnitConversionService;
use Livewire\Component;

class DataProduct extends Component
{
    public $category_id, $name_prd, $code_prd, $description_prd, $price, $price_online, $price_offline, $stock;
    public $sales_type = 'all'; // 'online', 'offline', 'all'
    public $productId; 

    // Dynamic composition items: array of ['bahan_id' => ..., 'quantity' => ..., 'unit' => ...]
    public $compositions = [];

    public $title = 'Dashboard';
    public $subpage = 'Overview Kasir';
    public $linkTitle;
    public $linkSubpage;
    public $content = 'Daftar Produk';

    public function mount()
    {
        $this->linkTitle = route('kasir.dashboard');
        $this->linkSubpage = route('kasir.product');
    }

    public function updatedNamePrd($value) { $this->generateCode($value, $this->category_id); }
    public function updatedCategoryId($value) { $this->generateCode($this->name_prd, $value); }

    public function addCompositionRow()
    {
        $this->compositions[] = [
            'bahan_id' => '',
            'quantity' => 1,
            'unit' => 'pcs',
        ];
    }

    public function removeCompositionRow($index)
    {
        unset($this->compositions[$index]);
        $this->compositions = array_values($this->compositions);
        $this->generateDescriptionFromCompositions();
    }

    public function updatedCompositions($value, $key)
    {
        // When bahan_id is selected, auto set default unit to bahan's base_unit or purchase_unit
        if (str_contains($key, 'bahan_id')) {
            $parts = explode('.', $key);
            $index = $parts[0] ?? null;
            if ($index !== null && isset($this->compositions[$index]['bahan_id'])) {
                $bahan = Bahan::find($this->compositions[$index]['bahan_id']);
                if ($bahan) {
                    $this->compositions[$index]['unit'] = $bahan->base_unit ?? $bahan->unit;
                }
            }
        }
        $this->generateDescriptionFromCompositions();
    }

    public function generateDescriptionFromCompositions()
    {
        $items = [];
        foreach ($this->compositions as $comp) {
            if (!empty($comp['bahan_id'])) {
                $bahan = Bahan::find($comp['bahan_id']);
                if ($bahan) {
                    $qty = (float)($comp['quantity'] ?? 1);
                    $unit = $comp['unit'] ?? $bahan->base_unit;
                    $items[] = "{$bahan->name_bahan}: {$qty} {$unit}";
                }
            }
        }
        if (!empty($items)) {
            $this->description_prd = 'Komposisi: ' . implode(', ', $items);
        }
    }

    private function generateCode($name, $categoryId)
    {
        if (empty($name) || empty($categoryId)) {
            $this->code_prd = '';
            return;
        }
        $category = Category::find($categoryId);
        $prefix = $category ? $category->name_code : '';
        $words = explode(' ', preg_replace('/\s+/', ' ', trim($name)));
        $suffix = '';
        if (count($words) >= 2) {
            $suffix .= mb_substr($words[0], 0, 1) . rand(0, 9) . mb_substr($words[1], 0, 1);
        } else {
            $suffix .= mb_substr($words[0], 0, 1) . rand(0, 9) . mb_substr($words[0], 1, 1);
        }
        $this->code_prd = strtoupper($prefix . $suffix);
    }

    public function resetInput()
    {
        $this->productId = null;
        $this->category_id = '';
        $this->name_prd = '';
        $this->code_prd = '';
        $this->description_prd = '';
        $this->sales_type = 'all';
        $this->price = '';
        $this->price_online = '';
        $this->price_offline = '';
        $this->stock = '';
        $this->compositions = [];
        $this->resetValidation();
    }

    public function store()
    {
        $rules = [
            'category_id' => 'required',
            'name_prd' => 'required|min:3',
            'code_prd' => 'required|unique:products,code_prd',
            'sales_type' => 'required|in:online,offline,all',
            'stock' => 'required|numeric|min:0',
            'compositions' => 'nullable|array',
            'compositions.*.bahan_id' => 'required|exists:bahans,id',
            'compositions.*.quantity' => 'required|numeric|gt:0',
            'compositions.*.unit' => 'required|string',
        ];

        if ($this->sales_type === 'online') {
            $rules['price_online'] = 'required|numeric|min:0';
        } elseif ($this->sales_type === 'offline') {
            $rules['price_offline'] = 'required|numeric|min:0';
        } else { // 'all'
            $rules['price_online'] = 'required|numeric|min:0';
            $rules['price_offline'] = 'required|numeric|min:0';
        }

        $this->validate($rules);

        // Validate unit compatibility for each composition item
        $pivotData = [];
        foreach ($this->compositions as $index => $comp) {
            if (!empty($comp['bahan_id'])) {
                $bahan = Bahan::find($comp['bahan_id']);
                if ($bahan) {
                    $useUnit = $comp['unit'] ?? $bahan->base_unit;
                    if (!UnitConversionService::areUnitsCompatible($useUnit, $bahan->base_unit)) {
                        session()->flash('danger', "Satuan pemakaian '{$useUnit}' tidak sesuai dengan satuan dasar bahan '{$bahan->name_bahan}' ({$bahan->base_unit}).");
                        return;
                    }
                    $converted = UnitConversionService::convertToBaseUnit($comp['quantity'], $useUnit);
                    $pivotData[$bahan->id] = [
                        'quantity' => (float)$converted['amount'],
                        'unit' => $useUnit,
                    ];
                }
            }
        }

        $mainPrice = 0;
        if ($this->sales_type === 'online') {
            $mainPrice = (int)$this->price_online;
        } elseif ($this->sales_type === 'offline') {
            $mainPrice = (int)$this->price_offline;
        } else {
            $mainPrice = (int)($this->price_offline ?? $this->price_online);
        }

        $product = Product::create([
            'category_id' => $this->category_id,
            'user_id' => auth()->user()->id,
            'name_prd' => $this->name_prd,
            'code_prd' => $this->code_prd, 
            'description_prd' => $this->description_prd,
            'sales_type' => $this->sales_type,
            'price_online' => $this->price_online !== '' ? (int)$this->price_online : null,
            'price_offline' => $this->price_offline !== '' ? (int)$this->price_offline : null,
            'price' => $mainPrice,
            'stock' => $this->stock,
        ]);

        if (!empty($pivotData)) {
            $product->bahans()->sync($pivotData);
        }

        session()->flash('success', 'Produk berhasil ditambahkan!');
        $this->resetInput();
        $this->dispatch('close-modal'); 
    }

    public function edit($id)
    {
        $product = Product::with('bahans')->findOrFail($id);
        $this->productId = $id;
        $this->category_id = $product->category_id;
        $this->name_prd = $product->name_prd;
        $this->code_prd = $product->code_prd;
        $this->description_prd = $product->description_prd;
        $this->sales_type = $product->sales_type ?? 'all';
        $this->price_online = $product->price_online;
        $this->price_offline = $product->price_offline;
        $this->price = $product->price;
        $this->stock = $product->stock;

        $this->compositions = [];
        foreach ($product->bahans as $b) {
            $this->compositions[] = [
                'bahan_id' => $b->id,
                'quantity' => (float)($b->pivot->quantity ?? 1),
                'unit' => $b->pivot->unit ?? $b->base_unit,
            ];
        }
    }

    public function update()
    {
        $rules = [
            'category_id' => 'required',
            'name_prd' => 'required|min:3',
            'sales_type' => 'required|in:online,offline,all',
            'stock' => 'required|numeric|min:0',
            'compositions' => 'nullable|array',
            'compositions.*.bahan_id' => 'required|exists:bahans,id',
            'compositions.*.quantity' => 'required|numeric|gt:0',
            'compositions.*.unit' => 'required|string',
        ];

        if ($this->sales_type === 'online') {
            $rules['price_online'] = 'required|numeric|min:0';
        } elseif ($this->sales_type === 'offline') {
            $rules['price_offline'] = 'required|numeric|min:0';
        } else { // 'all'
            $rules['price_online'] = 'required|numeric|min:0';
            $rules['price_offline'] = 'required|numeric|min:0';
        }

        $this->validate($rules);

        // Validate unit compatibility for each composition item
        $pivotData = [];
        foreach ($this->compositions as $comp) {
            if (!empty($comp['bahan_id'])) {
                $bahan = Bahan::find($comp['bahan_id']);
                if ($bahan) {
                    $useUnit = $comp['unit'] ?? $bahan->base_unit;
                    if (!UnitConversionService::areUnitsCompatible($useUnit, $bahan->base_unit)) {
                        session()->flash('danger', "Satuan pemakaian '{$useUnit}' tidak sesuai dengan satuan dasar bahan '{$bahan->name_bahan}' ({$bahan->base_unit}).");
                        return;
                    }
                    $converted = UnitConversionService::convertToBaseUnit($comp['quantity'], $useUnit);
                    $pivotData[$bahan->id] = [
                        'quantity' => (float)$converted['amount'],
                        'unit' => $useUnit,
                    ];
                }
            }
        }

        $mainPrice = 0;
        if ($this->sales_type === 'online') {
            $mainPrice = (int)$this->price_online;
        } elseif ($this->sales_type === 'offline') {
            $mainPrice = (int)$this->price_offline;
        } else {
            $mainPrice = (int)($this->price_offline ?? $this->price_online);
        }

        $product = Product::findOrFail($this->productId);
        $product->update([
            'category_id' => $this->category_id,
            'user_id' => auth()->user()->id,
            'name_prd' => $this->name_prd,
            'description_prd' => $this->description_prd,
            'sales_type' => $this->sales_type,
            'price_online' => $this->price_online !== '' ? (int)$this->price_online : null,
            'price_offline' => $this->price_offline !== '' ? (int)$this->price_offline : null,
            'price' => $mainPrice,
            'stock' => $this->stock,
        ]);

        $product->bahans()->sync($pivotData);

        session()->flash('success', 'Produk berhasil diperbarui!');
        $this->resetInput();
        $this->dispatch('close-modal');
    }

    public function delete($id)
    {
        Product::find($id)->delete();
        session()->flash('danger', 'Produk berhasil dihapus.');
    }

    public function render()
    {
        return view('livewire.kasir.data-product', [
            'products' => Product::with(['user', 'category', 'bahans'])->latest()->get(),
            'categories' => Category::with('user')->latest()->get(),
            'activeBahans' => Bahan::where('status', 'active')->latest()->get(),
        ])->layout('layouts.app', [
            'subpage' => $this->subpage,
            'content' => $this->content,
        ]);
    }
}
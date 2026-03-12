<?php

namespace App\Livewire\Kasir;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;

class DataProduct extends Component
{
    public $category_id, $name_prd, $code_prd, $description_prd, $price, $stock;
    public $productId; 

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
        $this->price = '';
        $this->stock = '';
        $this->resetValidation();
    }

    public function store()
    {
        $this->validate([
            'category_id' => 'required',
            'name_prd' => 'required|min:3',
            'code_prd' => 'required|unique:products,code_prd',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|numeric|min:0',
        ]);

        Product::create([
            'category_id' => $this->category_id,
            'user_id' => auth()->user()->id,
            'name_prd' => $this->name_prd,
            'code_prd' => $this->code_prd, 
            'description_prd' => $this->description_prd,
            'price' => $this->price,
            'stock' => $this->stock,
        ]);

        session()->flash('success', 'Produk berhasil ditambahkan!');
        $this->resetInput();
        $this->dispatch('close-modal'); 
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $this->productId = $id;
        $this->category_id = $product->category_id;
        $this->name_prd = $product->name_prd;
        $this->code_prd = $product->code_prd;
        $this->description_prd = $product->description_prd;
        $this->price = $product->price;
        $this->stock = $product->stock;
    }

    public function update()
    {
        $this->validate([
            'category_id' => 'required',
            'name_prd' => 'required|min:3',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|numeric|min:0',
        ]);

        $product = Product::findOrFail($this->productId);
        $product->update([
            'category_id' => $this->category_id,
            'user_id' => auth()->user()->id,
            'name_prd' => $this->name_prd,
            'description_prd' => $this->description_prd,
            'price' => $this->price,
            'stock' => $this->stock,
        ]);

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
            'products' => Product::with('user', 'category')->latest()->get(),
            'categories' => Category::with('user')->latest()->get(),
        ])->layout('layouts.app', [
            'subpage' => $this->subpage,
            'content' => $this->content,
        ]);
    }
}
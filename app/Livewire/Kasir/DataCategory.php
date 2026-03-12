<?php

namespace App\Livewire\Kasir;

use App\Models\Category;
use Livewire\Component;

class DataCategory extends Component
{
    public $name, $name_code, $categoryId;
    public $isEdit = false;

    public $title = 'Dashboard';
    public $subpage = 'Overview Kasir';
    public $linkTitle;
    public $linkSubpage;
    public $content = 'Kategori Produk';

    public function mount()
    {
        $this->linkTitle = route('kasir.dashboard');
        $this->linkSubpage = route('kasir.category');
    }

    public function updatedName($value)
    {
        $words = explode(' ', preg_replace('/\s+/', ' ', trim($value)));
        $code = '';

        if (count($words) >= 2) {
            $code .= mb_substr($words[0], 0, 1);
            $code .= rand(0, 9);
            $code .= mb_substr($words[1], 0, 1);
        } elseif (count($words) == 1 && !empty($words[0])) {
            $code .= mb_substr($words[0], 0, 1);
            $code .= rand(1, 9);
            $code .= mb_substr($words[0], 1, 1);
        }

        $this->name_code = strtoupper(str_replace(' ', '', $code));
    }

    public function resetInput()
    {
        $this->name = '';
        $this->name_code = '';
        $this->categoryId = null;
        $this->isEdit = false;
        $this->resetValidation(); 
    }

    public function store()
    {
        $this->validate([
            'name' => 'required|unique:categories,name',
            'name_code' => 'required|max:5|unique:categories,name_code',
        ]);

        Category::create([
            'name' => $this->name,
            'name_code' => strtoupper($this->name_code),
            'user_id' => auth()->user()->id,
        ]);

        $this->resetInput();
        session()->flash('success', 'Kategori baru berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        $this->categoryId = $id;
        $this->name = $category->name;
        $this->name_code = $category->name_code;
        $this->isEdit = true;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|unique:categories,name,' . $this->categoryId,
            'name_code' => 'required|max:5|unique:categories,name_code,' . $this->categoryId,
        ]);

        $category = Category::findOrFail($this->categoryId);
        $category->update([
            'name' => $this->name,
            'name_code' => strtoupper($this->name_code),
            'user_id' => auth()->user()->id,
        ]);

        $this->resetInput();
        session()->flash('success', 'Kategori berhasil diperbarui!');
    }

    public function delete($id)
    {
        Category::find($id)->delete();
        session()->flash('danger', 'Kategori berhasil dihapus.');
    }

    public function render()
    {
        return view('livewire.kasir.data-category',[
            'categories' => Category::with('user')->withCount('products')->latest()->get(),
        ])->layout('layouts.app', [
            'subpage' => $this->subpage,
            'content' => $this->content,
        ]);
    }
}
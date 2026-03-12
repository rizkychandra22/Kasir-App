<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class Kasir extends Component
{
    public $subpage = 'Dashboard';
    public $content = 'Overview Kasir';
    public $linkSubpage;

    public function mount()
    {
        $this->linkSubpage = route('kasir.dashboard');
    }

    public function render()
    {
        return view('livewire.dashboard.kasir')->layout('layouts.app', [
            'subpage' => 'Dashboard',    
            'content' => 'Overview Kasir', 
        ]);
    }
}

<?php

namespace App\Livewire\Dashboard;

use Livewire\Component;

class Admin extends Component
{
    public $subpage = 'Dashboard';
    public $content = 'Overview Admin';
    public $linkSubpage;

    public function mount()
    {
        $this->linkSubpage = route('admin.dashboard');
    }

    public function render()
    {
        return view('livewire.dashboard.admin')->layout('layouts.app', [
            'subpage' => 'Dashboard',    
            'content' => 'Overview Admin', 
        ]);
    }
}

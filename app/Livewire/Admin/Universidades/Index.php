<?php

namespace App\Livewire\Admin\Universidades;

use Livewire\Component;
use App\Models\Universidad;

class Index extends Component
{
    public $universidades;

    public function mount()
    {
        $this->universidades = Universidad::all();
    }

    public function render()
    {
        return view('livewire.admin.universidades.index');
    }
}

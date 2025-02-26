<?php

namespace App\Livewire\Admin\Asignaturas;

use App\Models\Area;
use Livewire\Component;

class Index extends Component
{
    public $asignaturas;

    public function mount()
    {
        $this->asignaturas = Area::query()->with('team')->get();
    }

    public function render()
    {
        return view('livewire.admin.asignaturas.index');
    }
}

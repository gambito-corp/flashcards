<?php

namespace App\Livewire\Admin\Universidades;

use Livewire\Component;
use App\Models\Universidad;

class Edit extends Component
{
    public $universidadId;
    public $name;
    public $universidad;

    protected $rules = [
        'name' => 'required|min:3',
    ];

    public function mount($universidad)
    {
        $this->universidad = $universidad;
        $this->name = $universidad->name;
    }

    // Actualiza la universidad y redirige a la lista
    public function update()
    {
        $this->validate();

        $this->universidad->name = $this->name;
        $this->universidad->save();

        session()->flash('message', 'Universidad actualizada con éxito.');
        return redirect()->route('admin.universidades.index');
    }

    public function render()
    {
        return view('livewire.admin.universidades.edit');
    }
}

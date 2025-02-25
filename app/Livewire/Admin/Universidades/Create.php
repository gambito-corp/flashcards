<?php

namespace App\Livewire\Admin\Universidades;

use Livewire\Component;
use App\Models\Universidad;

class Create extends Component
{
    public $name;

    protected $rules = [
        'name' => 'required|min:3',
    ];

    public function store()
    {
        $this->validate();

        Universidad::create([
            'name' => $this->name,
        ]);

        session()->flash('message', 'Universidad creada con éxito.');

        return redirect()->route('admin.universidades.index');
    }

    public function render()
    {
        return view('livewire.admin.universidades.create');
    }
}

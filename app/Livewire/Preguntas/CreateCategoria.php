<?php

namespace App\Livewire\Preguntas;

use Livewire\Component;
use App\Models\Area;
use App\Models\Category;

class CreateCategoria extends Component
{
    public $areas = [];
    public $selectedArea = null;
    public $name;
    public $description;

    protected $rules = [
        'selectedArea' => 'required|exists:areas,id',
        'name'         => 'required|string|min:3',
        'description'  => 'nullable|string',
    ];

    public function mount()
    {
        $this->areas = Area::all();
        if ($this->areas->isNotEmpty()) {
            $this->selectedArea = $this->areas->first()->id;
        }
    }

    public function store()
    {
        $this->validate();

        Category::create([
            'area_id'     => $this->selectedArea,
            'name'        => $this->name,
            'description' => $this->description,
        ]);

        session()->flash('message', 'Categoría creada correctamente.');
        $this->reset(['name', 'description']);
    }

    public function render()
    {
        return view('livewire.preguntas.create-categoria');
    }
}

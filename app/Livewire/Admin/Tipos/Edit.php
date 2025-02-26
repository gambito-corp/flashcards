<?php

namespace App\Livewire\Admin\Tipos;

use Livewire\Component;
use App\Models\Tipo;
use App\Models\Team;
use App\Models\Area;
use App\Models\Category;

class Edit extends Component
{
    public $tipoId;
    public $name;
    public $category_id;

    public $teams;
    public $areas = [];
    public $categories = [];

    public $selectedTeam;
    public $selectedArea;

    protected $rules = [
        'name'         => 'required|min:3',
        'selectedTeam' => 'required',
        'selectedArea' => 'required',
        'category_id'  => 'required',
    ];

    public function mount(Tipo $tipo)
    {
        $this->tipoId = $tipo->id;
        $this->name = $tipo->name;
        $this->category_id = $tipo->category_id;

        // Cargar todos los equipos
        $this->teams = Team::all();

        // Establecer el Team y Área actuales a partir de la categoría del Tipo
        if ($tipo->category && $tipo->category->area) {
            $this->selectedTeam = $tipo->category->area->team_id;
            $this->selectedArea = $tipo->category->area->id;
        }

        // Cargar las áreas correspondientes al team seleccionado
        $this->areas = Area::where('team_id', $this->selectedTeam)->get();
        // Cargar las categorías correspondientes al área seleccionada
        $this->categories = Category::where('area_id', $this->selectedArea)->get();
    }

    // Actualiza las áreas cuando se selecciona un team nuevo
    public function updatedSelectedTeam($value)
    {
        $this->areas = Area::where('team_id', $value)->get();
        $this->selectedArea = '';
        $this->categories = collect();
        $this->category_id = '';
    }

    // Actualiza las categorías cuando se selecciona un área nueva
    public function updatedSelectedArea($value)
    {
        $this->categories = Category::where('area_id', $value)->get();
        $this->category_id = '';
    }

    public function update()
    {
        $this->validate();

        $tipo = Tipo::findOrFail($this->tipoId);
        $tipo->update([
            'name'        => $this->name,
            'category_id' => $this->category_id,
        ]);

        session()->flash('message', 'Tipo actualizado con éxito.');
        return redirect()->route('admin.tipos.index');
    }

    public function render()
    {
        return view('livewire.admin.tipos.edit');
    }
}

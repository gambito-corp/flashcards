<?php

namespace App\Livewire\Preguntas;

use App\Models\Team;
use Livewire\Component;
use App\Models\Area;
use App\Models\Category;

class CreateCategoria extends Component
{
    public $teams = [];
    public $selectedTeam = null;

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
        // Cargamos las carreras disponibles
        $this->teams = Team::all();
        if ($this->teams->isNotEmpty()) {
            // Asignamos el ID del primer equipo, no el objeto completo
            $this->selectedTeam = $this->teams->first()->id;

            // Cargamos las áreas asociadas a la carrera seleccionada
            $this->areas = Area::query()->where('team_id', $this->selectedTeam)->get();
            if ($this->areas->isNotEmpty()) {
                $this->selectedArea = $this->areas->first()->id;
            }
        }
    }

    public function updatedSelectedTeam($value)
    {
        // Actualizamos las áreas en base al ID de la carrera seleccionado
        $this->areas = Area::query()->where('team_id', $value)->get();
        if ($this->areas->isNotEmpty()) {
            // Asignamos el ID del primer área disponible
            $this->selectedArea = $this->areas->first()->id;
        } else {
            $this->selectedArea = null;
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

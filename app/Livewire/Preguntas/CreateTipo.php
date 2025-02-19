<?php

namespace App\Livewire\Preguntas;

use Livewire\Component;
use App\Models\Team;
use App\Models\Area;
use App\Models\Category;
use App\Models\Tipo;
use Illuminate\Support\Facades\Session;

class CreateTipo extends Component
{
    // Propiedades para la selección anidada
    public $teams = [];
    public $selectedTeam = null;

    public $areas = [];
    public $selectedArea = null;

    public $categories = [];
    public $selectedCategory = null;

    // Propiedad para el nombre del Tipo
    public $name;

    protected function rules()
    {
        return [
            'selectedTeam'     => 'required|exists:teams,id',
            'selectedArea'     => 'required|exists:areas,id',
            'selectedCategory' => 'required|exists:categories,id',
            'name'             => 'required|string|max:255',
        ];
    }

    public function mount()
    {
        // Cargar todas las carreras
        $this->teams = Team::all();
        if ($this->teams->isNotEmpty()) {
            // Asignar el ID de la primera carrera
            $this->selectedTeam = $this->teams->first()->id;

            // Cargar asignaturas (áreas) asociadas a la carrera seleccionada
            $this->areas = Area::where('team_id', $this->selectedTeam)->get();
            if ($this->areas->isNotEmpty()) {
                $this->selectedArea = $this->areas->first()->id;

                // Cargar categorías asociadas a la asignatura seleccionada
                $this->categories = Category::where('area_id', $this->selectedArea)->get();
                if ($this->categories->isNotEmpty()) {
                    $this->selectedCategory = $this->categories->first()->id;
                }
            }
        }
    }

    // Al cambiar la Carrera, se actualizan las Asignaturas y, en consecuencia, las Categorías
    public function updatedSelectedTeam($value)
    {
        $this->areas = Area::where('team_id', $value)->get();
        $this->selectedArea = $this->areas->isNotEmpty() ? $this->areas->first()->id : null;

        if ($this->selectedArea) {
            $this->categories = Category::where('area_id', $this->selectedArea)->get();
            $this->selectedCategory = $this->categories->isNotEmpty() ? $this->categories->first()->id : null;
        } else {
            $this->categories = collect();
            $this->selectedCategory = null;
        }
    }

    // Al cambiar la Asignatura, se actualizan las Categorías
    public function updatedSelectedArea($value)
    {
        $this->categories = Category::where('area_id', $value)->get();
        $this->selectedCategory = $this->categories->isNotEmpty() ? $this->categories->first()->id : null;
    }

    public function store()
    {
        $this->validate();

        // Crear el Tipo asociado a la categoría seleccionada
        Tipo::create([
            'category_id' => $this->selectedCategory,
            'name'        => $this->name,
        ]);

        Session::flash('message', 'Tipo creado correctamente.');

        // Reiniciamos el campo del nombre (puedes dejar las selecciones si lo deseas)
        $this->reset(['name']);
    }

    public function render()
    {
        return view('livewire.preguntas.create-tipo');
    }
}

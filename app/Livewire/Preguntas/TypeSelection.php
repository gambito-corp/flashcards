<?php

namespace App\Livewire\Preguntas;

use Livewire\Component;
use App\Models\Team;
use App\Models\Area;
use App\Models\Category;
use App\Models\Tipo;

class TypeSelection extends Component
{
    // Listados globales
    public $teams = [];
    public $areas = [];
    public $categories = [];
    public $tipos = [];

    // Selección actual en cada nivel
    public $selectedTeam = null;
    public $selectedArea = null;
    public $selectedCategory = null;
    public $selectedTipo = null;

    // Arreglo de combinaciones agregadas
    public $selections = [];

    public function mount()
    {
        // Cargamos todos los equipos (carreras) con sus relaciones anidadas
        $this->teams = Team::with('areas.categories.tipos')->get();

        if ($this->teams->isNotEmpty()) {
            $this->selectedTeam = $this->teams->first()->id;
            $this->updateAreas();
        }
    }

    // Actualiza las áreas en función del equipo seleccionado
    public function updateAreas()
    {
        $team = $this->teams->firstWhere('id', $this->selectedTeam);
        $this->areas = $team ? $team->areas : collect();
        $this->selectedArea = $this->areas->isNotEmpty() ? $this->areas->first()->id : null;
        $this->updateCategories();
    }

    // Actualiza las categorías en función del área seleccionada
    public function updateCategories()
    {
        $area = $this->areas->firstWhere('id', $this->selectedArea);
        $this->categories = $area ? $area->categories : collect();
        $this->selectedCategory = $this->categories->isNotEmpty() ? $this->categories->first()->id : null;
        $this->updateTipos();
    }

    // Actualiza los tipos en función de la categoría seleccionada
    public function updateTipos()
    {
        $category = $this->categories->firstWhere('id', $this->selectedCategory);
        $this->tipos = $category ? $category->tipos : collect();
        $this->selectedTipo = $this->tipos->isNotEmpty() ? $this->tipos->first()->id : null;
    }

    // Métodos para actualizar cada select cuando cambia la selección
    public function updatedSelectedTeam()
    {
        $this->updateAreas();
    }

    public function updatedSelectedArea()
    {
        $this->updateCategories();
    }

    public function updatedSelectedCategory()
    {
        $this->updateTipos();
    }

    // Agrega la combinación actual al arreglo de selecciones y resetea los selects a valores por defecto
    public function addSelection()
    {
        if ($this->selectedTeam && $this->selectedArea && $this->selectedCategory && $this->selectedTipo) {
            $team = $this->teams->firstWhere('id', $this->selectedTeam);
            $area = $this->areas->firstWhere('id', $this->selectedArea);
            $category = $this->categories->firstWhere('id', $this->selectedCategory);
            $tipo = $this->tipos->firstWhere('id', $this->selectedTipo);

            $this->selections[] = [
                'team_id'       => $team->id,
                'team_name'     => $team->name,
                'area_id'       => $area->id,
                'area_name'     => $area->name,
                'category_id'   => $category->id,
                'category_name' => $category->name,
                'tipo_id'       => $tipo->id,
                'tipo_name'     => $tipo->name,
            ];

            // Opcional: Resetear a los valores por defecto (por ejemplo, volver a la primera opción del primer equipo)
            $this->selectedTeam = $this->teams->first()->id;
            $this->updateAreas();
        }
    }

    // Elimina una selección del arreglo
    public function removeSelection($index)
    {
        unset($this->selections[$index]);
        $this->selections = array_values($this->selections);
    }

    public function render()
    {
        return view('livewire.preguntas.type-selection');
    }
}

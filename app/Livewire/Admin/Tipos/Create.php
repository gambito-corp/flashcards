<?php

namespace App\Livewire\Admin\Tipos;

use Livewire\Component;
use App\Models\Tipo;
use App\Models\Team;
use App\Models\Area;
use App\Models\Category;

class Create extends Component
{
    public $tipo = [
        'name'        => '',
        'category_id' => '',
    ];

    public $teams;
    public $selectedTeam = '';

    public $areas = [];
    public $selectedArea = '';

    public $categories = [];

    protected $rules = [
        'tipo.name'        => 'required|min:3',
        'selectedTeam'     => 'required',
        'selectedArea'     => 'required',
        'tipo.category_id' => 'required',
    ];

    public function mount()
    {
        $this->teams = Team::all();
    }

    // Cuando se selecciona una Carrera, se cargan sus áreas (asignaturas)
    public function updatedSelectedTeam($value)
    {
        $this->areas = Area::where('team_id', $value)->get();
        $this->selectedArea = '';
        $this->categories = collect(); // Reiniciamos las categorías
        $this->tipo['category_id'] = '';
    }

    // Cuando se selecciona una Asignatura, se cargan las categorías asociadas
    public function updatedSelectedArea($value)
    {
        $this->categories = Category::where('area_id', $value)->get();
        $this->tipo['category_id'] = '';
    }

    public function store()
    {
        $this->validate();

        Tipo::create([
            'name'        => $this->tipo['name'],
            'category_id' => $this->tipo['category_id'],
        ]);

        session()->flash('message', 'Tipo creado con éxito.');
        return redirect()->route('admin.tipos.index');
    }

    public function render()
    {
        return view('livewire.admin.tipos.create');
    }
}

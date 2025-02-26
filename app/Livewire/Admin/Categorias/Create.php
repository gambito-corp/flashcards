<?php

namespace App\Livewire\Admin\Categorias;

use Livewire\Component;
use App\Models\Category;
use App\Models\Team;
use App\Models\Area;

class Create extends Component
{
    public $category = [
        'name'      => '',
        'team_id'   => '',
        'area_id'   => '',
        'description' => '',
    ];

    public $teams;
    public $areas = []; // Áreas asociadas al team seleccionado

    protected $rules = [
        'category.name'    => 'required|min:3',
        'category.team_id' => 'required',
        'category.area_id' => 'required',
    ];

    public function mount()
    {
        $this->teams = Team::all();
        // Inicialmente, no hay áreas ya que no se ha seleccionado un team.
        $this->areas = collect();
    }

    // Cada vez que se actualice el team seleccionado, se cargan sus áreas.
    public function updatedCategoryTeamId($value)
    {
        $this->areas = Area::where('team_id', $value)->get();
        // Reiniciamos el área seleccionada para obligar al usuario a elegir.
        $this->category['area_id'] = '';
    }

    public function store()
    {
        $this->validate();

        Category::create([
            'area_id'     => $this->category['area_id'],
            'name'        => $this->category['name'],
            'description' => $this->category['description'] ?? '',
        ]);

        session()->flash('message', 'Categoría creada con éxito.');
        return redirect()->route('admin.categorias.index');
    }

    public function render()
    {
        return view('livewire.admin.categorias.create');
    }
}

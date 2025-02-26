<?php

namespace App\Livewire\Admin\Asignaturas;

use App\Models\Area;
use App\Models\Team;
use Livewire\Component;

class Create extends Component
{
    public $asignatura = [
        'nombre' => '',
        'team_id' => '',
    ];
    public $teams;

    protected $rules = [
        'asignatura.nombre'  => 'required',
        'asignatura.team_id' => 'required',
    ];

    public function mount()
    {
        $this->teams = Team::all();
    }

    public function store()
    {
        $this->validate();

        Area::create([
            'team_id'    => $this->asignatura['team_id'],
            'name'       => $this->asignatura['nombre'],
            'description'=> '',
        ]);

        session()->flash('message', 'Asignatura creada con éxito.');

        return redirect()->route('admin.asignaturas.index');
    }

    public function render()
    {
        return view('livewire.admin.asignaturas.create');
    }
}

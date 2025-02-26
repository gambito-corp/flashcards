<?php

namespace App\Livewire\Admin\Asignaturas;

use App\Models\Area;
use App\Models\Team;
use Livewire\Component;

class Edit extends Component
{
    public $asignatura; // Instancia del modelo Area
    public $name;
    public $team_id;
    public $teams;

    protected $rules = [
        'asignatura.name'    => 'required|min:3',
        'asignatura.team_id' => 'required',
    ];

    public function mount(Area $asignatura)
    {
        $this->asignatura = $asignatura; // Asigna la instancia del modelo
        $this->name = $this->asignatura->name;
        $this->team_id = $this->asignatura->team_id;
        $this->teams = Team::all();
    }

    public function update()
    {
        $this->validate();
        $this->asignatura->name = $this->name;
        $this->asignatura->team_id = $this->team_id;
        $this->asignatura->save();
        session()->flash('message', 'Asignatura actualizada con éxito.');
        return redirect()->route('admin.asignaturas.index');
    }

    public function render()
    {
        return view('livewire.admin.asignaturas.edit');
    }
}

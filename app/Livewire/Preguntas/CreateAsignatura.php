<?php

namespace App\Livewire\Preguntas;

use Livewire\Component;
use App\Models\Area;
use App\Models\Team;

class CreateAsignatura extends Component
{
    public $teams = [];
    public $selectedTeam = null;
    public $name;
    public $description;

    protected $rules = [
        'selectedTeam' => 'required|exists:teams,id',
        'name'         => 'required|string|min:3',
        'description'  => 'nullable|string',
    ];

    public function mount()
    {
        $this->teams = Team::all();
        if ($this->teams->isNotEmpty()) {
            $this->selectedTeam = $this->teams->first()->id;
        }
    }

    public function store()
    {
        $this->validate();

        Area::create([
            'team_id'     => $this->selectedTeam,
            'name'        => $this->name,
            'description' => $this->description,
        ]);

        session()->flash('message', 'Asignatura creada correctamente.');
        $this->reset(['name', 'description']);
    }

    public function render()
    {
        return view('livewire.preguntas.create-asignatura');
    }
}

<?php

namespace App\Livewire\Admin\Teams;

use App\Models\Team;
use Livewire\Component;

class Edit extends Component
{
    public $team;
    public $name;

    protected $rules = [
        'name' => 'required|min:3',
    ];

    public function mount(Team $team)
    {
        $this->team = $team;
        $this->name = $team->name;
    }

    public function update()
    {
        $this->validate();

        $this->team->update([
            'name' => $this->name,
        ]);

        session()->flash('message', 'Carrera actualizada con éxito.');

        return redirect()->route('admin.carreras.index');
    }

    public function render()
    {
        return view('livewire.admin.teams.edit');
    }
}

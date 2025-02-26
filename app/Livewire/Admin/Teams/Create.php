<?php

namespace App\Livewire\Admin\Teams;

use App\Models\Team;
use Livewire\Component;

class Create extends Component
{
    public $name;

    protected $rules = [
        'name' => 'required|min:3',
    ];

    public function store()
    {
        $this->validate();

        Team::create([
            'user_id' => auth()->id(),
            'name' => $this->name,
        ]);

        session()->flash('message', 'Carrera creada con éxito.');

        return redirect()->route('admin.carreras.index');
    }

    public function render()
    {
        return view('livewire.admin.teams.create');
    }
}

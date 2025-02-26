<?php

namespace App\Livewire\Admin\Teams;

use App\Models\Team;
use Livewire\Component;

class Index extends Component
{
    public $teams;

    public function mount()
    {
        $this->teams = Team::all();
    }

    public function render()
    {
        return view('livewire.admin.teams.index');
    }
}

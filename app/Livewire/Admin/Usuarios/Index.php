<?php

namespace App\Livewire\Admin\Usuarios;

use App\Models\User;
use Livewire\Component;

class Index extends Component
{
    public $usuarios;
    public function render()
    {
        $data = User::all();
        return view('livewire.admin.usuarios.index', compact('data'));
    }
    public function Asignaturas($team)
    {
        $userAreas = auth()->user()->areas()->where('team_id', $team->id)->get();
        $teamAreas = $team->areas;
        $userIds = $userAreas->pluck('id')->sort()->values()->toArray();
        $teamIds = $teamAreas->pluck('id')->sort()->values()->toArray();

        if ($userIds === $teamIds) {
            return 'todas';
        }

        return $userAreas->pluck('name')->implode(', ');
    }

    public function getUserStatus($status)
    {
        return $status ? 'Premium' : 'Fremium';
    }


}

<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CurrentTeamController extends Controller
{

    public function update(Request $request, Team $team)
    {
        $user = Auth::user();

        if (!$user->teams()->where('teams.id', $team->id)->exists()) {
            return redirect()->back()->withErrors(['No tienes acceso a este equipo.']);
        }

        $user->current_team_id = $team->id;
        $user->save();

        return redirect()->back()->with('status', 'Equipo cambiado exitosamente.');
    }
}

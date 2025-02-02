<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Team;
use App\Models\TeamUser;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1) Crear un usuario
        $user = User::query()->create([
            'name' => 'Pedro Asesor',
            'email' => 'asesor.pedro@gmail.com',
            'password' => Hash::make('admin123'),
            'current_team_id' => 1,
        ]);

        $team = Team::query()->create([
            'user_id' => $user->id,
            'name'    => 'Materia Demo',
            'personal_team' => false,
        ]);

        TeamUser::query()->create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'role'    => 'admin'
        ]);
    }
}

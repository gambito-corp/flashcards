<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\{Tipo, Universidad, User, Team, Area, Category, Question, Option};
use Spatie\Permission\Models\{Permission, Role};

class ProjectSeeder extends Seeder
{
    public function run(): void
    {
        // Crear Roles y Permisos
        $roles = ['root', 'admin', 'colab', 'user'];
        foreach ($roles as $role) {
            Role::query()->create(['name' => $role, 'guard_name' => 'web']);
        }

        $permissions = ['crearAsignatura', 'crearExamen', 'crearPregunta', 'aprobarPregunta', 'responderPregunta'];
        foreach ($permissions as $permission) {
            Permission::query()->create(['name' => $permission, 'guard_name' => 'web']);
        }

        // Crear Usuario Principal
        $mainUser = User::query()->create([
            'name' => 'Pedro Asesor',
            'email' => 'asesor.pedro@gmail.com',
            'password' => Hash::make('admin123'),
        ]);

        // Crear Usuario Principal
        $mainUser2 = User::query()->create([
            'name' => 'kenji',
            'email' => 'kenji@gmail.com',
            'password' => Hash::make('admin123'),
        ]);

        $admin = User::query()->create([
            'name' => 'administrador',
            'email' => 'admin@flashcard.perpetuo.cloud',
            'password' => Hash::make('admin123'),
        ]);
        $admin->assignRole('admin');
        $mainUser->assignRole('root');
        $mainUser2->assignRole('root');

        // Crear Teams (Carreras)
        $teamMedicina = Team::query()->create(['name' => 'Medicina', 'user_id' => $mainUser->id, 'personal_team' => false]);
        if (config('app.env') === 'local') {
            $teamPsicologia = Team::query()->create(['name' => 'Psicología', 'user_id' => $mainUser->id, 'personal_team' => false]);
            $areas = [
                'Medicina' => ['Patología', 'Nefrología', 'Psicología Humana'],
                'Psicología' => ['Psicología Humana', 'Psicoanálisis']
            ];

            $tiposPorCategoria = ['Básico', 'Avanzado', 'Experto'];

            $universitiesList = ['UNMSM', 'UCV', 'Pacífico'];

            foreach ($universitiesList as $university){
                Universidad::query()->create(['name' => $university]);
            }

            foreach ($areas as $teamName => $teamAreas) {
                $team = $teamName === 'Medicina' ? $teamMedicina : $teamPsicologia;
                foreach ($teamAreas as $key => $areaName) {
                    $area = Area::create([
                        'name' => $areaName,
                        'description' => "Área de $areaName",
                        'team_id' => $team->id,
                    ]);
                    $nrg = rand(1,4);

                    for ($i = 1; $i<=$nrg; $i++)
                    {
                        $category = Category::create([
                            'name' => "Categoría $i de $areaName",
                            'description' => "Especialidad en $areaName",
                            'area_id' => $area->id,
                        ]);
                        foreach ($tiposPorCategoria as $tipoName) {
                            $tipo = Tipo::create(['category_id' => $category->id, 'name' => $key+1 .' '. $tipoName]);

//                        // Asignar de 0 a 3 universidades aleatoriamente
//                        $numUniversities = rand(0, 3);
//                        if ($numUniversities > 0) {
//                            $selectedUniversities = array_rand($universitiesList, $numUniversities);
//                            if (!is_array($selectedUniversities)) {
//                                $selectedUniversities = [$selectedUniversities];
//                            }
//                            foreach ($selectedUniversities as $key) {
//                                Universidad::create(['tipo_id' => $tipo->id, 'name' => $universitiesList[$key]]);
//                            }
//                        }
                        }
                    }
                }
            }
        }

        $mainUser->update(['current_team_id' => $teamMedicina->id]);
    }
}

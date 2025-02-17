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
        $mainUser->assignRole('root');

        // Crear Teams (Carreras)
        $teamMedicina = Team::query()->create(['name' => 'Medicina', 'user_id' => $mainUser->id, 'personal_team' => false]);
        $teamPsicologia = Team::query()->create(['name' => 'Psicología', 'user_id' => $mainUser->id, 'personal_team' => false]);

        $mainUser->update(['current_team_id' => $teamMedicina->id]);

        // Crear Áreas, Categorías y Tipos
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

//        // Crear Preguntas y Opciones
//        Category::all()->each(function ($category) use ($mainUser) {
//            $tipos = $category->tipos; // Obtener todos los tipos relacionados con esta categoría
//            foreach (range(1, 100) as $i) {
//                $iframe = '<iframe width="560" height="315" src="https://www.youtube.com/embed/-411MsAzlbw" frameborder="0" allowfullscreen></iframe>';
//                $url = "https://youtu.be/-411MsAzlbw";
//                $mediaType = rand(0, 1) ? $url : $iframe;
//
//                // Crear pregunta
//                $question = Question::create([
//                    'user_id' => $mainUser->id,
//                    'content' => "Pregunta $i para {$category->name}",
//                    'question_type' => 'multiple_choice',
//                    'approved' => (bool)rand(0, 1),
//                    'media_url' => $mediaType === $url ? $url : null,
//                    'media_iframe' => $mediaType === $iframe ? $iframe : null,
//                    'explanation' => 'Explicación para la pregunta.',
//                ]);
//
//                // Asociar la pregunta a la categoría actual
//                $category->questions()->attach($question->id);
//
//                // Asignar la pregunta a uno o varios tipos aleatorios dentro de la categoría
//                $assignedTipos = $tipos->random(rand(1, min(3, $tipos->count()))); // Asignar entre 1 y 3 tipos aleatorios
//                foreach ($assignedTipos as $tipo) {
//                    $tipo->questions()->attach($question->id);
//                }
//
//                // Obtener universidades de los tipos asignados
//                $universidades = Universidad::whereIn('tipo_id', $assignedTipos->pluck('id'))->get();
//
//                // Asignar la pregunta a ninguna, una o varias universidades aleatoriamente
//                $numUniversities = rand(0, $universidades->count());
//                if ($numUniversities > 0) {
//                    $selectedUniversities = $universidades->random($numUniversities);
//                    foreach ($selectedUniversities as $university) {
//                        $university->questions()->attach($question->id);
//                    }
//                }
//
//                // Crear opciones para la pregunta
//                $correctOption = rand(0, 3);
//                foreach (['A', 'B', 'C', 'D'] as $index => $letter) {
//                    Option::create([
//                        'question_id' => $question->id,
//                        'content' => "Opción $letter",
//                        'is_correct' => $index === $correctOption,
//                        'points' => $index === $correctOption ? 1.0 : 0.0,
//                    ]);
//                }
//            }
//        });
    }
}

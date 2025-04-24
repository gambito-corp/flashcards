<?php

namespace App\Http\Controllers;

ini_set('memory_limit', '512M');

use App\Models\{Area,
Category,
Exam,
ExamResult,
ExamTeam,
Tipo};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
class ExamController extends Controller
{

    public function index()
    {
        if (Auth::user()->current_team_id === null) {
            return redirect()->route('dashboard')->with('error', 'Selecciona una materia primero');
        }
        $areas = Area::query()->select('id', 'team_id', 'name')->where('team_id', Auth::user()->current_team_id)->get();
        $categorias = Category::query()->select('id', 'area_id', 'name')->where('area_id', $areas->first()?->id)->get();
        $tipos = Tipo::query()->select('id', 'category_id', 'name')->where('category_id', $categorias->first()?->id)->get();

        return view('examenes.index', [
            'areas' => $areas,
            'categorias' => $categorias,
            'tipos' => $tipos,
        ]);
    }

    public function createExam(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'examCollection' => 'required|json',
            'examTitle' => 'required|string',
            'examTime' => 'required|numeric|min:1|max:120',
        ]);
        $examCollection = json_decode($request->examCollection);
        $examTitle = $request->examTitle;
        $examTime = $request->examTime;

        try {
            DB::beginTransaction();
            // Crear el examen
            $exam = new \App\Models\Exam();
            $exam->title = $examTitle;
            $exam->description = ''; // Puedes ajustar la descripción según lo necesites
            $exam->time_limit = $examTime;
            $exam->save();

            $allSelectedQuestions = collect();

            // Recorremos cada selección
            foreach ($examCollection as $selection) {
                $type = Tipo::with(['questions' => function ($query) use ($selection) {
                    $query->where('approved', 1)
                        ->when(!empty($selection->university_id), function ($q) use ($selection) {
                            $q->whereHas('universidades', function ($q) use ($selection) {
                                $q->where('universidades.id', $selection->university_id);
                            });
                        })
                        ->inRandomOrder()
                        ->take($selection->question_count);
                }])->find($selection->tipo_id);
                if (!$type) {
                    continue;
                }

                $questions = $type->questions;

                $allSelectedQuestions = $allSelectedQuestions->merge($questions);
            }

            // Definir el límite según el estado del usuario
            $limit = auth()->user()->status == 1 ? 200 : 10;
            if(\auth()->user()->hasAnyRole('root', 'admin', 'colab', 'Rector')){
                $limit = 200;
            }

            // Limitar a un máximo global de preguntas según el límite definido
            if ($allSelectedQuestions->count() > $limit) {
                $allSelectedQuestions = $allSelectedQuestions->random($limit);
            }

            // Adjuntar las preguntas al examen (relación many-to-many)
            $exam->questions()->attach($allSelectedQuestions->pluck('id')->unique());
            DB::commit();
        }catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());

        }

        return redirect()->route('examenes.show', $exam)->with('status', 'Examen creado exitosamente.');
    }

    public function show(Exam $exam)
    {
        $exam = $exam->load('questions.options');
        return view('examenes.show', compact('exam'));
    }

    public function evaluarExamen(Request $request)
    {
        try {
            // Validamos que se reciba un array de respuestas y el exam_id correspondiente
            $validated = $request->validate([
                'respuestas' => 'required|array',
                'respuestas.*' => 'exists:options,id',
                'exam_id'    => 'required|integer|exists:exams,id',
            ]);

            $respuestasUsuario = $validated['respuestas'];

            // Cargamos el examen junto con sus preguntas asociadas
            $exam = \App\Models\Exam::with('questions')->findOrFail($validated['exam_id']);
            $totalPreguntas = $exam->questions->count();

            // Obtenemos de la base de datos todos los IDs de las opciones correctas
            $respuestasCorrectas = \App\Models\Option::where('is_correct', true)
                ->pluck('id')
                ->toArray();

            // Calculamos cuántas respuestas correctas dio el usuario comparando los IDs enviados
            // con los IDs correctos
            $totalCorrectas = count(array_intersect($respuestasCorrectas, array_values($respuestasUsuario)));

            // Calculamos la puntuación: se toma el total de preguntas del examen (incluye las no respondidas)
            // para que éstas se cuenten como incorrectas.
            $puntuacion = $totalPreguntas > 0
                ? round(($totalCorrectas / $totalPreguntas) * 100, 2)
                : 0;

            ExamResult::query()->create([
                'user_id'       => Auth::id(),
                'exam_id'       => $validated['exam_id'],
                'total_score'   => $puntuacion,
            ]);
            ExamTeam::query()->create([
                'team_id' => Auth::user()->current_team_id,
                'exam_id' => $validated['exam_id'],
            ]);
            return response()->json([
                'puntuacion' => $puntuacion,
                'respuestas_correctas' => $respuestasCorrectas,
                'respuestas_enviadas' => $respuestasUsuario
            ]);
        } catch (\Exception $e) {
            // Registramos el error para facilitar la depuración
            \Log::error('Error en evaluarExamen: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error al evaluar el examen'], 500);
        }
    }
}

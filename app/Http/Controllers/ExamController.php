<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ExamTeam;
use App\Models\Question;
use App\Models\Team;
use App\Models\Tipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
ini_set('memory_limit', '512M');
class ExamController extends Controller
{

    public function index(Request $request)
    {
        if (Auth::user()->current_team_id === null) {
            return redirect()->route('dashboard')->with('error', 'Selecciona una materia primero');
        }

        $areas = Area::with([
            'categories' => fn($query) => $query->select('id', 'name', 'area_id'),
            'categories.tipos' => fn($query) => $query->select('id', 'name', 'category_id'),
            'categories.tipos.questions' => fn($query) => $query
                ->where('approved', true)
                ->select('id', 'question', 'answer', 'tipo_id')
                ->with(['universidades:id,name'])
        ])
            ->where('team_id', Auth::user()->current_team_id)
            ->select('id', 'name', 'team_id')
            ->get();

        return view('examenes.index', compact('areas'));
    }


    public function createExam(Request $request)
    {
        $user = Auth::user();
        // Si el usuario tiene status 0, se verifica el límite de exámenes del mes actual.
//        if ($user->status == 0) {
//            $currentMonthExamCount = ExamResult::query()->where('user_id', $user->id)
//                ->whereYear('created_at', now()->year)
//                ->whereMonth('created_at', now()->month)
//                ->count();
//
//            if ($currentMonthExamCount >= 10) {
//                return response()->json([
//                    'error' => 'No puede crear más de 10 exámenes por mes.'
//                ], 403);
//            }
//        }
        if (!auth()->check()) {
            return response()->json(['error' => 'No autenticado'], 401);
        }

        $validated = $request->validate([
            'questionSelections'            => 'required|array',
            'questionSelections.*.typeId'     => 'required|integer',
            'questionSelections.*.quantity'   => 'required|integer|min:0',
            'questionSelections.*.university' => 'nullable|integer|exists:universidades,id',
            'time_exam'                     => 'required|integer|min:1',
            'title'                         => 'required|string',
        ]);

        // Crear el examen
        $exam = new \App\Models\Exam();
        $exam->title = $validated['title'];
        $exam->description = ''; // Puedes ajustar la descripción según lo necesites
        $exam->time_limit = $validated['time_exam'];
        $exam->save();

        $allSelectedQuestions = collect();

        // Recorremos cada selección
        foreach ($validated['questionSelections'] as $selection) {
            // Consultamos el Tipo con sus preguntas aprobadas y en orden aleatorio.
            // Si se indicó una universidad, filtramos las preguntas por ella.
            $type = \App\Models\Tipo::with(['questions' => function ($query) use ($selection) {
                $query->where('approved', 1)
                    ->when(!empty($selection['university']), function ($q) use ($selection) {
                        $q->whereHas('universidades', function ($q) use ($selection) {
                            $q->where('universidades.id', $selection['university']);
                        });
                    })
                    ->inRandomOrder();
            }])->find($selection['typeId']);

            if (!$type) {
                continue;
            }

            $questions = $type->questions;
            $quantity = $selection['quantity'];

            // Si hay suficientes preguntas y se pidió al menos 1, seleccionamos aleatoriamente la cantidad solicitada.
            if ($quantity > 0 && $questions->count() >= $quantity) {
                $selected = $questions->random($quantity);
            } else {
                $selected = $questions;
            }

            $allSelectedQuestions = $allSelectedQuestions->merge($selected);
        }

        // Definir el límite según el estado del usuario
        $limit = auth()->user()->status == 1 ? 200 : 10;

        // Limitar a un máximo global de preguntas según el límite definido
        if ($allSelectedQuestions->count() > $limit) {
            $allSelectedQuestions = $allSelectedQuestions->random($limit);
        }

        // Adjuntar las preguntas al examen (relación many-to-many)
        $exam->questions()->attach($allSelectedQuestions->pluck('id')->unique());

        return response()->json([
            'message' => 'Examen creado correctamente',
            'examen'  => $exam->id,
        ]);
    }

    public function showExam($id)
    {
        $exam = Exam::query()->with('questions.options')->find($id);
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

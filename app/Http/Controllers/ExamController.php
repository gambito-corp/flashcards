<?php

namespace App\Http\Controllers;

ini_set('memory_limit', '512M');

use App\Models\{Exam, Question};
use App\Services\Usuarios\MBIAService;
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
        return view('examenes.index');
    }

    public function show(Exam $exam)
    {
        $exam = $this->examToArray($exam->load('questions.options'));
        $exam['is_IA'] = false;
        return view('examenes.show', compact('exam'));
    }

    public function createExam(Request $request)
    {
        $user = Auth::user();

        // 1. Comprobación de límite para usuarios freemium (no root y status == 0)
        if (!$user->hasAnyRole('root') && $user->status == 0) {
            // 2. Contar exámenes IA del usuario
            $iaExamsCount = \App\Models\ExamResult::where('user_id', $user->id)
                ->whereBetween('created_at', [now()->firstOfMonth(), now()->lastOfMonth()])
                ->count();

            if ($iaExamsCount >= 20) {
                return back()->with('error', 'Has alcanzado el límite de 20 exámenes IA permitidos para cuentas gratuitas. <a href="' . route('planes') . '" class="font-bold underline text-blue-600">Hazte PRO</a> para crear más.');
            }
        }
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
            $exam->description = '';
            $exam->time_limit = $examTime;
            $exam->save();

            $allSelectedQuestions = collect();
            $userId = $user->id;

            foreach ($examCollection as $selection) {
                $questions = Question::select('questions.*')
                    ->where('questions.approved', 1)
                    ->when(!empty($selection->university_id), function ($q) use ($selection) {
                        $q->whereHas('universidades', function ($q) use ($selection) {
                            $q->where('universidades.id', $selection->university_id);
                        });
                    })
                    ->whereHas('tipos', function ($q) use ($selection) {
                        $q->where('tipos.id', $selection->tipo_id);
                    })
                    ->inRandomOrder() // Selección 100% aleatoria sin ponderación
                    ->take($selection->question_count)
                    ->get();

                $allSelectedQuestions = $allSelectedQuestions->merge($questions);
            }

            // Elimina duplicados por ID
            $allSelectedQuestions = $allSelectedQuestions->unique('id');

            // Límite global de preguntas
            $limit = $user->status == 1 ? 200 : 10;
            if ($user->hasAnyRole('root', 'admin', 'colab', 'Rector')) {
                $limit = 200;
            }

            if ($allSelectedQuestions->count() > $limit) {
                $allSelectedQuestions = $allSelectedQuestions->random($limit);
            }

            $exam->questions()->attach($allSelectedQuestions->pluck('id'));
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
        }

        return redirect()->route('examenes.show', $exam)->with('status', 'Examen creado exitosamente.');
    }

    public function createExamFailGlobal(Request $request)
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

            $exam = new \App\Models\Exam();
            $exam->title = $examTitle;
            $exam->description = 'Examen de preguntas más falladas por todos los usuarios';
            $exam->time_limit = $examTime;
            $exam->save();

            $allSelectedQuestions = collect();

            foreach ($examCollection as $selection) {
                $questions = \App\Models\Question::select('questions.*')
                    ->where('questions.approved', 1)
                    ->where('fail_weight', '>', 0)
                    ->whereHas('tipos', function ($q) use ($selection) {
                        $q->where('tipos.id', $selection->tipo_id);
                    })
                    ->when(!empty($selection->university_id), function ($q) use ($selection) {
                        $q->whereHas('universidades', function ($q) use ($selection) {
                            $q->where('universidades.id', $selection->university_id);
                        });
                    })
                    ->orderByDesc('fail_weight')
                    ->take($selection->question_count)
                    ->get();

                $allSelectedQuestions = $allSelectedQuestions->merge($questions);
            }

            $allSelectedQuestions = $allSelectedQuestions->unique('id');

            $limit = $user->status == 1 ? 200 : 10;
            if ($user->hasAnyRole('root', 'admin', 'colab', 'Rector')) {
                $limit = 200;
            }

            if ($allSelectedQuestions->count() > $limit) {
                $allSelectedQuestions = $allSelectedQuestions->random($limit);
            }

            $exam->questions()->attach($allSelectedQuestions->pluck('id'));
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
        }

        return redirect()->route('examenes.show', $exam)->with('status', 'Examen de preguntas más falladas creado exitosamente.');
    }

    public function createExamUserFailed(Request $request)
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
        $userId = $user->id;

        try {
            DB::beginTransaction();

            $exam = new \App\Models\Exam();
            $exam->title = $examTitle;
            $exam->description = 'Examen de preguntas más falladas por ti';
            $exam->time_limit = $examTime;
            $exam->save();

            $allSelectedQuestions = collect();

            foreach ($examCollection as $selection) {
                $questions = \App\Models\Question::select('questions.*')
                    ->join('exam_user_answers as eua', function ($join) use ($userId) {
                        $join->on('questions.id', '=', 'eua.question_id')
                            ->where('eua.user_id', '=', $userId)
                            ->where('eua.fail_weight', '>', 0);
                    })
                    ->where('questions.approved', 1)
                    ->whereHas('tipos', function ($q) use ($selection) {
                        $q->where('tipos.id', $selection->tipo_id);
                    })
                    ->when(!empty($selection->university_id), function ($q) use ($selection) {
                        $q->whereHas('universidades', function ($q) use ($selection) {
                            $q->where('universidades.id', $selection->university_id);
                        });
                    })
                    ->orderByDesc('eua.fail_weight')
                    ->take($selection->question_count)
                    ->get();

                $allSelectedQuestions = $allSelectedQuestions->merge($questions);
            }

            $allSelectedQuestions = $allSelectedQuestions->unique('id');

            $limit = $user->status == 1 ? 200 : 10;
            if ($user->hasAnyRole('root', 'admin', 'colab', 'Rector')) {
                $limit = 200;
            }

            if ($allSelectedQuestions->count() > $limit) {
                $allSelectedQuestions = $allSelectedQuestions->random($limit);
            }

            $exam->questions()->attach($allSelectedQuestions->pluck('id'));
            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage());
        }

        return redirect()->route('examenes.show', $exam)->with('status', 'Examen de preguntas más falladas (personal) creado exitosamente.');
    }

    public function examenIA(Request $request)
    {


        $user = Auth::user();

        // 1. Comprobación de límite para usuarios freemium (no root y status == 0)
        if (!$user->hasAnyRole('root') && $user->status == 0) {
            // 2. Contar exámenes IA del usuario
            $iaExamsCount = \App\Models\ExamResult::where('user_id', $user->id)
                ->whereBetween('created_at', [now()->firstOfMonth(), now()->lastOfMonth()])
                ->count();

            if ($iaExamsCount >= 20) {
                return back()->with('error', 'Has alcanzado el límite de 20 exámenes IA permitidos para cuentas gratuitas. <a href="' . route('planes') . '" class="font-bold underline text-blue-600">Hazte PRO</a> para crear más.');
            }
        }

        $examTitle = $request->input('examTitle');
        $examTime = $request->input('examTime');
        $examCollectionRaw = $request->input('examCollection');
        $examCollection = $examCollectionRaw ? json_decode($examCollectionRaw, true) : [];
        $openAI = new MBIAService();
        $iaBlocks = $openAI->generateExamQuestions($examCollection);
        // Crear el examen
        $exam = new \App\Models\Exam();
        $exam->title = $examTitle;
        $exam->description = '';
        $exam->time_limit = $examTime;
        $exam->save();

        // 1. Aplanar y transformar las preguntas
        $questions = [];
        $questionId = 1;

        foreach ($iaBlocks as $block) {
            foreach ($block['questions'] as $q) {
                // Asigna un ID único y transforma las opciones
                $options = [];
                foreach ($q['opciones'] as $idx => $op) {
                    $options[] = [
                        'id' => $idx + 1,
                        'content' => $op,
                    ];
                }
                // Busca el id de la opción correcta
                $correctOptionId = null;
                foreach ($options as $opt) {
                    if (trim($opt['content']) === trim($q['respuesta_correcta'])) {
                        $correctOptionId = $opt['id'];
                        break;
                    }
                }
                $questions[] = [
                    'id' => $questionId++,
                    'content' => $q['pregunta'],
                    'options' => $options,
                    'explanation' => $q['explicacion'] ?? '',
                    'correct_option_id' => $correctOptionId,
                ];
            }
        }

        $exam = [
            'questions' => $questions,
            'examTitle' => $examTitle,
            'examTime' => $examTime,
            'examId' => $exam->id,
            'is_IA' => true,
        ];
        return view('examenes.show', compact('exam'));
    }

    public function examToArray(\App\Models\Exam $exam)
    {
        return [
            'questions' => $exam->questions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'content' => $question->content,
                    'options' => $question->options->map(function ($option) {
                        return [
                            'id' => $option->id,
                            'content' => $option->content,
                        ];
                    })->toArray(),
                    'explanation' => $question->explanation,
                    'correct_option_id' => $question->options->firstWhere('is_correct', true)?->id
                        ?? $question->correct_option_id // fallback por si tienes el campo
                ];
            })->toArray(),
            'examTitle' => $exam->title,
            'examTime' => $exam->time_limit,
            'examId' => $exam->id,
        ];
    }


    public function estadisticas()
    {
        return view('examenes.estadisticas');
    }


}

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


    public function show(Exam $exam)
    {
        $exam = $exam->load('questions.options');
        return view('examenes.show', compact('exam'));
    }

    public function evaluarExamen(Request $request)
    {
        try {
            $validated = $request->validate([
                'respuestas' => 'required|array',
                'respuestas.*' => 'exists:options,id',
                'exam_id' => 'required|integer|exists:exams,id',
            ]);

            $user = Auth::user();
            $respuestasUsuario = $validated['respuestas'];
            $exam = \App\Models\Exam::with('questions.options')->findOrFail($validated['exam_id']);
            $totalPreguntas = $exam->questions->count();

            // Obtener los IDs de opciones correctas SOLO de las preguntas de este examen
            $respuestasCorrectas = [];
            foreach ($exam->questions as $question) {
                foreach ($question->options as $option) {
                    if ($option->is_correct) {
                        $respuestasCorrectas[] = (int)$option->id;
                    }
                }
            }

            $totalCorrectas = 0;

            foreach ($exam->questions as $question) {
                // Convertimos los IDs a int para evitar problemas de tipo
                $optionId = isset($respuestasUsuario[$question->id]) ? (int)$respuestasUsuario[$question->id] : null;
                $option = $question->options->firstWhere('id', $optionId);

                $isCorrect = $option && $option->is_correct;

                if ($isCorrect) {
                    $totalCorrectas++;
                }

                // Registrar o actualizar el registro en exam_user_answers
                $examUserAnswer = \App\Models\ExamUserAnswer::where([
                    'user_id' => $user->id,
                    'exam_id' => $exam->id,
                    'question_id' => $question->id,
                ])->first();

                if ($examUserAnswer) {
                    // Si ya existe, actualiza opción y estado, y suma fail_weight si falló
                    $examUserAnswer->option_id = $optionId;
                    $examUserAnswer->is_correct = $isCorrect;
                    if (!$isCorrect) {
                        $examUserAnswer->fail_weight = $examUserAnswer->fail_weight + 1;
                    } else {
                        $examUserAnswer->fail_weight = max(0, $examUserAnswer->fail_weight - 1);
                    }
                    $examUserAnswer->save();
                } else {
                    // Si no existe, crea el registro
                    \App\Models\ExamUserAnswer::create([
                        'user_id' => $user->id,
                        'exam_id' => $exam->id,
                        'question_id' => $question->id,
                        'option_id' => $optionId,
                        'is_correct' => $isCorrect,
                        'fail_weight' => $isCorrect ? 0 : 1,
                    ]);
                }
                if (!$isCorrect) {
                    $question->increment('fail_weight');
                } else {
                    // Restar solo si es mayor que 0
                    if ($question->fail_weight > 0) {
                        $question->decrement('fail_weight');
                    }
                }
            }

            // Calcular la puntuación
            $puntuacion = $totalPreguntas > 0
                ? round(($totalCorrectas / $totalPreguntas) * 100, 2)
                : 0;

            \App\Models\ExamResult::create([
                'user_id' => $user->id,
                'exam_id' => $exam->id,
                'total_score' => $puntuacion,
            ]);

            \App\Models\ExamTeam::create([
                'team_id' => $user->current_team_id,
                'exam_id' => $exam->id,
            ]);

            return response()->json([
                'puntuacion' => $puntuacion,
                'respuestas_correctas' => array_map('intval', $respuestasCorrectas), // IDs como int
                'respuestas_enviadas' => array_map('intval', $respuestasUsuario),   // IDs como int
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en evaluarExamen: ' . $e->getMessage());
            return response()->json(['error' => 'Ocurrió un error al evaluar el examen'], 500);
        }
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
        ];
//        $exam = $this->arrayToObject($exam);

        return view('examenes.ia', compact('exam'));
    }

    private function arrayToObject($array)
    {
        if (is_array($array)) {
            $obj = new \stdClass();
            foreach ($array as $key => $value) {
                $obj->{$key} = $this->arrayToObject($value);
            }
            return $obj;
        }
        return $array;
    }


}

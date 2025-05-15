<?php

namespace App\Livewire\Exams;

use App\Models\ExamResult;
use App\Models\ExamTeam;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Realize extends Component
{
    public $examen;
    public $currentPage = 1;
    public $questionsPerPage = 1;
    public $selectedAnswers = [];
    public $remainingTime;
    public $showFinishModal = false;
    public $showExitModal = false;
    public $correcciones;
    public $score;
    public $mostrar_correccion;


    public function mount($examen)
    {
        if (Auth::user()->current_team_id === null) {
            redirect()->route('dashboard')->with('error', 'Selecciona una materia primero')->send();
            exit;
        }

        $this->examen = $examen;
        $this->remainingTime = $examen['examTime'] * 60;
    }

    public function getPaginatedQuestionsProperty()
    {
        $start = ($this->currentPage - 1) * $this->questionsPerPage;
        return array_slice($this->examen['questions'], $start, $this->questionsPerPage);
    }

    public function getTotalPagesProperty()
    {
        return ceil(count($this->examen['questions']) / $this->questionsPerPage);
    }

    public function selectAnswer($questionId, $optionId)
    {
        $this->selectedAnswers[$questionId] = $optionId;
    }

    public function nextPage()
    {
        if ($this->currentPage < $this->totalPages) {
            $this->currentPage++;
        }
    }

    public function prevPage()
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
        }
    }

    public function decrementTimer()
    {
        if ($this->remainingTime > 0) {
            $this->remainingTime--;
        }
    }

    public function getFormattedTimeProperty()
    {
        $minutes = floor($this->remainingTime / 60);
        $seconds = $this->remainingTime % 60;
        return sprintf('%02d:%02d', $minutes, $seconds);
    }

    public function goToPage($page)
    {
        if ($page >= 1 && $page <= $this->totalPages) {
            $this->currentPage = $page;
        }
    }

    public function getVisiblePages()
    {
        $maxVisible = 10;
        $pages = [];
        $total = $this->totalPages;

        if ($total <= $maxVisible) {
            // Mostrar todas las páginas si son 10 o menos
            for ($i = 1; $i <= $total; $i++) {
                $pages[] = $i;
            }
        } else {
            $half = floor($maxVisible / 2);
            $start = max(1, $this->currentPage - $half);
            $end = min($total, $start + $maxVisible - 1);

            // Ajustar si estamos al final
            if ($end - $start < $maxVisible - 1) {
                $start = max(1, $end - $maxVisible + 1);
            }

            for ($i = $start; $i <= $end; $i++) {
                $pages[] = $i;
            }
        }

        return $pages;
    }

    public function finalizarExamen()
    {
        // 1. Volver a la página 1
        $this->currentPage = 1; // [1]

        // 2. Corregir el examen (comparar respuesta correcta vs marcada)
        $this->correcciones = [];
        foreach ($this->examen['questions'] as $pregunta) {
            $id = $pregunta['id'];
            $opcion_correcta = $pregunta['correct_option_id'];
            $seleccionada = $this->selectedAnswers[$id] ?? null;
            $this->correcciones[$id] = [
                'correcta' => $opcion_correcta,
                'seleccionada' => $seleccionada,
                'es_correcta' => ($seleccionada === $opcion_correcta),
            ];
        }

        // 3. Puntuación en base 100
        $total = count($this->examen['questions']);
        $aciertos = collect($this->correcciones)->where('es_correcta', true)->count();
        $this->score = $total > 0 ? round(($aciertos / $total) * 100) : 0;

        // 4. Mostrar la corrección automáticamente
        $this->mostrar_correccion = true; // Usarás esto en el Blade para mostrar la corrección
        $this->showFinishModal = false;

        // 5. guardar puntuacion
        try {
            \DB::beginTransaction();
            $this->guardarPuntuacion();
            \DB::commit();
        } catch (\Exception $exception) {
            \DB::rollBack();
            dd($exception);
            session()->flash('error', $exception->getMessage());
        }


    }


    public function salirExamen()
    {
        $this->showExitModal = false;
        return redirect()->route('dashboard');
    }


    public function render()
    {
        return view('livewire.exams.realize', [
            'paginatedQuestions' => $this->paginatedQuestions,
            'totalPages' => $this->totalPages,
            'formattedTime' => $this->formattedTime,
            'examTitle' => $this->examen['examTitle'],
            'currentPage' => $this->currentPage,
        ]);
    }

    private function guardarPuntuacion()
    {
        ExamResult::create([
            'user_id' => Auth::id(),
            'exam_id' => $this->examen['examId'],
            'total_score' => $this->score,
        ]);
        ExamTeam::create([
            'team_id' => Auth::user()->current_team_id,
            'exam_id' => $this->examen['examId'],
        ]);

        if (isset($this->examen['is_IA']) && $this->examen['is_IA'] == false) {
            foreach ($this->examen['questions'] as $question) {
                $optionId = isset($this->selectedAnswers[$question['id']]) ? (int)$this->selectedAnswers[$question['id']] : null;
                $isCorrect = ($optionId == $question['correct_option_id']) ? true : false;
                // Registrar o actualizar el registro en exam_user_answers
                $examUserAnswer = \App\Models\ExamUserAnswer::where([
                    'user_id' => Auth::id(),
                    'exam_id' => $this->examen['examId'],
                    'question_id' => $question['id'],
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
                    $examUserAnswer = \App\Models\ExamUserAnswer::create([
                        'user_id' => Auth::id(),
                        'exam_id' => $this->examen['examId'],
                        'question_id' => $question['id'],
                        'option_id' => $optionId,
                        'is_correct' => $isCorrect,
                    ]);
                    $examUserAnswer->option_id = $optionId;
                    $examUserAnswer->is_correct = $isCorrect;
                    if (!$isCorrect) {
                        $examUserAnswer->fail_weight = $examUserAnswer->fail_weight + 1;
                    } else {
                        $examUserAnswer->fail_weight = max(0, $examUserAnswer->fail_weight - 1);
                    }
                    $examUserAnswer->save();
                }
                if (!$isCorrect) {
                    $question = \App\Models\Question::find($question['id']);
                    $question->increment('fail_weight');
                } else {
                    // Restar solo si es mayor que 0
                    $question = \App\Models\Question::find($question['id']);
                    if ($question->fail_weight > 0) {
                        $question->decrement('fail_weight');
                    }
                }
            }
        }
    }
}

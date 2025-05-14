<?php

namespace App\Livewire\Exams;

use App\Models\ExamResult;
use App\Models\ExamTeam;
use App\Models\ExamUserAnswer;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class RealiceIa extends Component
{
    public $examen; // Array con preguntas, tiempo y título

    /**
     * Método que guarda el resultado y respuestas del examen IA.
     * @param array $payload - ['respuestas' => [pregunta_id => opcion_id, ...], 'score' => int, 'exam_id' => int]
     */
    public function guardarExamen($payload)
    {
        try {
            $user = Auth::user();

            // Validación básica
            if (!isset($payload['respuestas'], $payload['score'], $payload['exam_id'])) {
                $this->dispatch('examen-error', error: 'Faltan datos para guardar el examen.');
                return;
            }

            $exam_id = $payload['exam_id'];
            $respuestas = $payload['respuestas'];
            $score = (float)$payload['score'];

            // Guardar cada respuesta del usuario (updateOrCreate para idempotencia)
            foreach ($respuestas as $question_id => $option_id) {
                ExamUserAnswer::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'exam_id' => $exam_id,
                        'question_id' => $question_id,
                    ],
                    [
                        'option_id' => $option_id,
                        'is_correct' => null, // IA: puedes rellenar luego si tienes info
                        'fail_weight' => 0,
                    ]
                );
            }

            // Guardar el resultado general
            ExamResult::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'exam_id' => $exam_id,
                ],
                [
                    'total_score' => $score,
                ]
            );

            // Relacionar con el equipo, si aplica
            if ($user->current_team_id) {
                ExamTeam::updateOrCreate([
                    'team_id' => $user->current_team_id,
                    'exam_id' => $exam_id,
                ]);
            }

            $this->dispatch('examen-guardado', score: $score);

        } catch (\Exception $e) {
            \Log::error('Error guardando examen IA: ' . $e->getMessage());
            $this->dispatch('examen-error', error: 'Error al guardar el examen.');
        }
    }

    public function render()
    {
        return view('livewire.exams.realice-ia');
    }
}

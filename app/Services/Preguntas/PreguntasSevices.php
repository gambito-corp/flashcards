<?php

namespace App\Services\Preguntas;

use App\Models\Question;

class PreguntasSevices
{
    public function crearPregunta($pregunta, $approved) : Question
    {
        $question = Question::query()->create([
            'content' => $pregunta->newContent,
            'question_type' => 'multiple_choice',
            'explanation' => $pregunta->newExplanation,
            'media_url' => $pregunta->newMediaUrl,
            'media_iframe' => $pregunta->newMediaIframe,
            'approved' => $approved,
            'user_id' => auth()->id(),
        ]);

        dd($pregunta->selectedTipo);
        foreach ($pregunta->selectedTipo as $key => $value) {
            $question->tipos()->attach($value);
        }
        foreach ($pregunta->selectedUniversidades as $key => $value) {
            $question->universidades()->attach($value);
        }

        if ($pregunta->newQuestionType === 'multiple_choice') {
            foreach ($pregunta->newOptions as $index => $optionContent) {
                $question->options()->create([
                    'content' => $optionContent,
                    'is_correct' => ($index == $pregunta->newCorrectOption),
                    'points' => ($index == $pregunta->newCorrectOption) ? 1 : 0,
                ]);
            }
        }
        return $question;
    }
}

<?php

namespace App\Services\Preguntas;

use App\Models\Question;

class PreguntasSevices
{
    public function crearPregunta($pregunta, $approved, $tipos) : Question
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
        foreach ($tipos as $key => $tipo) {
            $question->tipos()->attach($tipo['tipo_ids'][0]);
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

    public function crearPreguntaCSV($row)
    {
        $approved =(auth()->user()->hasRole('admin') || auth()->user()->hasRole('root')) ? 1 : 0;

        $tipoIds = array_filter(array_map('intval', explode(',', $row['tipos'])));
        $universidadIds = array_filter(array_map('intval', explode(',', $row['universidades'])));
        $question = Question::query()->create([
            'content' => $row['content'],
            'question_type' => 'multiple_choice',
            'explanation' => $row['explicacion'],
            'media_url' => $row['url'],
            'media_iframe' => $row['iframe'],
            'approved' => $approved,
            'user_id' => auth()->id(),
        ]);

        foreach ($tipoIds as $key => $tipoId) {
            $question->tipos()->attach($tipoId);
        }
        foreach ($universidadIds as $key => $universidadId) {
            $question->universidades()->attach($universidadId);
        }

        if ($row['answer1'] != '') {
            $question->options()->create([
                'content' => $row['answer1'],
                'is_correct' => ($row['is_correct1'] == '1'),
                'points' => ($row['is_correct1'] == '1') ? 1 : 0,
            ]);
        }
        if ($row['answer2'] != '') {
            $question->options()->create([
                'content' => $row['answer2'],
                'is_correct' => ($row['is_correct2'] == '1'),
                'points' => ($row['is_correct2'] == '1') ? 1 : 0,
            ]);
        }
        if ($row['answer3'] != '') {
            $question->options()->create([
                'content' => $row['answer3'],
                'is_correct' => ($row['is_correct3'] == '1'),
                'points' => ($row['is_correct3'] == '1') ? 1 : 0,
            ]);
        }
        if ($row['answer4'] != '') {
            $question->options()->create([
                'content' => $row['answer4'],
                'is_correct' => ($row['is_correct4'] == '1'),
                'points' => ($row['is_correct4'] == '1') ? 1 : 0,
            ]);
        }
    }
}

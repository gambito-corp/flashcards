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
    public function updatePregunta(Question $question, \App\Livewire\Admin\Preguntas\Edit $param, bool $approved, $addedSelections)
    {

        // Actualizar los datos básicos de la pregunta
        $question->update([
            'content'       => $param->newContent,
            'question_type' => $param->questionType,
            'explanation'   => $param->newExplanation,
            'media_url'     => $param->newMediaUrl,
            'media_iframe'  => $param->newMediaIframe,
            'approved'      => $approved,
        ]);

        // Actualizar las relaciones many-to-many (Tipos y Universidades)
        $question->tipos()->sync(collect($addedSelections)->pluck('tipo_ids')->flatten()->toArray());
        $question->universidades()->sync($param->selectedUniversidades);

        // Actualizar opciones de respuesta
        if ($param->questionType === 'multiple_choice') {
            // Eliminar opciones previas
            $question->options()->delete();

            // Crear nuevas opciones
            foreach ($param->newOptions as $index => $optionContent) {
                $question->options()->create([
                    'content'    => $optionContent,
                    'is_correct' => ($index == $param->newCorrectOption),
                    'points'     => ($index == $param->newCorrectOption) ? 1 : 0,
                ]);
            }
        }

        return $question;
    }

    private function limpiarCodificacion($value)
    {
        if (!is_string($value)) {
            return $value;
        }

        // Reemplazar espacios no separables
        $value = str_replace("\u{A0}", ' ', $value);

        // Incluir codificaciones comunes en MS-DOS
        $detected = mb_detect_encoding($value, ['UTF-8', 'ISO-8859-1', 'WINDOWS-1252', 'CP850'], true);

        if ($detected && $detected !== 'UTF-8') {
            $value = mb_convert_encoding($value, 'UTF-8', $detected);
        }

        // Opcional: Si siguen apareciendo caracteres extraños, forzar conversión desde CP850
        if (strpos($value, 'Â') !== false) {
            $value = iconv('CP850', 'UTF-8//TRANSLIT//IGNORE', $value);
        }

        return trim($value);
    }



    public function crearPreguntaCSV($row)
    {
        dd($row);
        try {
            $approved =(auth()->user()->hasRole('admin') || auth()->user()->hasRole('root')) ? 1 : 0;

            $tipoIds = array_filter(array_map('intval', explode(',', $row['tipos'])));
            $universidadIds = array_filter(array_map('intval', explode(',', $row['universidades'])));
            $question = Question::query()->create([
                'content' => $this->limpiarCodificacion($row['content']),
                'question_type' => 'multiple_choice',
                'explanation' => $this->limpiarCodificacion($row['explicacion']),
                'media_url' => $row['url'],
                'media_iframe' => $row['iframe'],
                'approved' => $approved,
                'user_id' => auth()->id(),
            ]);
        }catch (\Exception $e){
            dd($e->getMessage(),  $row);
        }

        foreach ($tipoIds as $key => $tipoId) {
            $question->tipos()->attach($tipoId);
        }
        foreach ($universidadIds as $key => $universidadId) {
            $question->universidades()->attach($universidadId);
        }

        if ($row['answer1'] != '') {
            $question->options()->create([
                'content' => $this->limpiarCodificacion($row['answer1']),
                'is_correct' => ($row['is_correct1'] == '1' || $row['is_correct1'] == 'true'),
                'points' => ($row['is_correct1'] == '1' || $row['is_correct1'] == 'true') ? 1 : 0,
            ]);
        }
        if ($row['answer2'] != '') {
            $question->options()->create([
                'content' => $this->limpiarCodificacion($row['answer2']),
                'is_correct' => ($row['is_correct2'] == '1'||$row['is_correct2'] == 'true'),
                'points' => ($row['is_correct2'] == '1'||$row['is_correct2'] == 'true') ? 1 : 0,
            ]);
        }
        if ($row['answer3'] != '') {
            $question->options()->create([
                'content' => $this->limpiarCodificacion($row['answer3']),
                'is_correct' => ($row['is_correct3'] == '1'||$row['is_correct3'] == 'true'),
                'points' => ($row['is_correct3'] == '1'||$row['is_correct3'] == 'true') ? 1 : 0,
            ]);
        }
        if ($row['answer4'] != '') {
            $question->options()->create([
                'content' => $this->limpiarCodificacion($row['answer4']),
                'is_correct' => ($row['is_correct4'] == '1'||$row['is_correct4'] == 'true'),
                'points' => ($row['is_correct4'] == '1'||$row['is_correct4'] == 'true') ? 1 : 0,
            ]);
        }
    }


}

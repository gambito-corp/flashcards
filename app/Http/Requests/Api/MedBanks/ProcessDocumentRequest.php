<?php

namespace App\Http\Requests\Api\MedBanks;

use Illuminate\Foundation\Http\FormRequest;

class ProcessDocumentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'document' => 'required|file|mimes:pdf,txt,doc,docx|max:10240', // 10MB máximo
            'num_questions' => 'nullable|integer|min:1|max:200', // Opcional, entre 1 y 100
            'difficulty' => 'nullable|string|in:easy,medium,hard,extreme,suicide', // Opcional, entre 1 y 5
        ];
    }

    public function messages(): array
    {
        return [
            'document.required' => 'El documento es obligatorio',
            'document.file' => 'Debe ser un archivo válido',
            'document.mimes' => 'Solo se permiten archivos PDF, TXT, DOC o DOCX',
            'document.max' => 'El archivo no puede ser mayor a 10MB',
            'num_questions.integer' => 'El numero de preguntas debe ser un numero...',
        ];
    }
}

<?php
// app/Http/Requests/Api/MedBanks/ProcessPdfRequest.php

namespace App\Http\Requests\Api\MedBanks;

use Illuminate\Foundation\Http\FormRequest;

class ProcessPdfRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta solicitud.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación para la solicitud de procesamiento de PDF.
     */
    public function rules(): array
    {
        return [
            'pdf' => [
                'required',
                'file',
                'mimes:pdf',
                'max:10240', // 10MB máximo
            ],
            'num_questions' => [
                'nullable',
                'integer',
                'min:1',
                'max:200',
            ],
            'difficulty' => [
                'nullable',
                'string',
                'in:easy,medium,hard,experto,suicida',
            ],
            'pdf_content' => [
                'nullable',
                'string',
                'max:100000', // Limita el contenido a 100,000 caracteres (ajustable)
            ],
        ];
    }

    /**
     * Mensajes personalizados para los errores de validación.
     */
    public function messages(): array
    {
        return [
            'pdf.required' => 'El archivo PDF es obligatorio.',
            'pdf.file' => 'Debe ser un archivo válido.',
            'pdf.mimes' => 'Solo se permiten archivos PDF.',
            'pdf.max' => 'El archivo no puede ser mayor a 10MB.',
            'num_questions.integer' => 'La cantidad de preguntas debe ser un número.',
            'num_questions.min' => 'El número mínimo de preguntas es 1.',
            'num_questions.max' => 'El número máximo de preguntas es 200.',
            'difficulty.string' => 'La dificultad debe ser una cadena de texto.',
            'difficulty.in' => 'La dificultad seleccionada no es válida.',
            'pdf_content.string' => 'El contenido del PDF debe ser texto.',
            'pdf_content.max' => 'El contenido del PDF es demasiado grande.',
        ];
    }
}

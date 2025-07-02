<?php
// app/Http/Requests/Api/MedBanks/GetDifficultiesRequest.php

namespace App\Http\Requests\Api\MedBanks;

use Illuminate\Foundation\Http\FormRequest;

class GetDifficultiesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Por ahora permitimos a todos, más tarde puedes agregar lógica de autorización
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            // Por ahora no necesitamos validaciones específicas para obtener dificultades
            // Pero mantenemos la estructura por consistencia
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            // Mensajes personalizados si los necesitas más tarde
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            // Atributos personalizados si los necesitas más tarde
        ];
    }
}

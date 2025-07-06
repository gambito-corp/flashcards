<?php

namespace App\Http\Requests\Api\MedBanks;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CountingQuestionsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'tipo' => 'nullable|string|in:area,categoria',
            'tipo_id' => 'nullable|integer|exists:tipos,id',
            'university_id' => 'nullable|integer|exists:universidades,id',
            'failed_type' => 'nullable|string|in:personal-failed,global-failed',
        ];

        // Si viene 'tipo', entonces puede venir area_id o category_id
        if ($this->has('tipo')) {
            $rules['area_id'] = 'nullable|integer|exists:areas,id';
            $rules['category_id'] = 'nullable|integer|exists:categories,id';

            // No pueden venir ambos a la vez
            $rules['area_id'] = [
                'nullable',
                'integer',
                'exists:areas,id',
                Rule::requiredIf(function () {
                    return $this->input('tipo') === 'area' && !$this->has('category_id');
                }),
                Rule::prohibitedIf(function () {
                    return $this->has('category_id');
                })
            ];

            $rules['category_id'] = [
                'nullable',
                'integer',
                'exists:categories,id',
                Rule::requiredIf(function () {
                    return $this->input('tipo') === 'categoria' && !$this->has('area_id');
                }),
                Rule::prohibitedIf(function () {
                    return $this->has('area_id');
                })
            ];
        }

        return $rules;
    }

    public function messages(): array
    {

        return [
            'tipo.in' => 'El tipo debe ser: area o categoria',
            'tipo_id.exists' => 'El tipo seleccionado no existe',
            'area_id.exists' => 'El área seleccionada no existe',
            'category_id.exists' => 'La categoría seleccionada no existe',
            'university_id.exists' => 'La universidad seleccionada no existe',
            'area_id.required_if' => 'El area_id es requerido cuando tipo es "area"',
            'category_id.required_if' => 'El category_id es requerido cuando tipo es "categoria"',
            'area_id.prohibited_if' => 'No se puede enviar area_id cuando ya se envió category_id',
            'category_id.prohibited_if' => 'No se puede enviar category_id cuando ya se envió area_id',
        ];
    }

    /**
     * Validación adicional personalizada
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Validar que no vengan tipo y tipo_id al mismo tiempo
            if ($this->has('tipo') && $this->has('tipo_id')) {
                $validator->errors()->add('tipo', 'No se puede enviar "tipo" y "tipo_id" al mismo tiempo');
            }

            // Validar que venga al menos uno
            if (!$this->has('tipo') && !$this->has('tipo_id')) {
                $validator->errors()->add('tipo', 'Debe enviar "tipo" o "tipo_id"');
            }

            // Si viene tipo=area, debe venir area_id
            if ($this->input('tipo') === 'area' && !$this->has('area_id')) {
                $validator->errors()->add('area_id', 'area_id es requerido cuando tipo es "area"');
            }

            // Si viene tipo=categoria, debe venir category_id
            if ($this->input('tipo') === 'categoria' && !$this->has('category_id')) {
                $validator->errors()->add('category_id', 'category_id es requerido cuando tipo es "categoria"');
            }
        });
    }
}

/*
    *# ✅ Válidos - Conteo por área (sin university_id)
    *GET /api/medbank/counting-questions?tipo=area&area_id=1
    *
    *# ✅ Válidos - Conteo por categoría (sin university_id)
    *GET /api/medbank/counting-questions?tipo=categoria&category_id=1
    *
    *# ✅ Válidos - Conteo por tipo específico (university_id opcional)
    *GET /api/medbank/counting-questions?tipo_id=1
    *GET /api/medbank/counting-questions?tipo_id=1&university_id=2
    *Illuminate\Database\QueryException: SQLSTATE[42S02]: Base table or view not found: 1146 Table 'flashcard.category' doesn't exist (Connection: mysql, SQL: select count(*) as aggregate from `category` where `id` = 1) in file C:\laragon\www\flashcard\vendor\laravel\framework\src\Illuminate\Database\Connection.php on line 829
*/

<?php
// app/Http/Requests/Api/Medbanks/GetTiposRequest.php

namespace App\Http\Requests\Api\MedBanks;

use App\Enums\Api\MedBank\DataTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetTiposRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|integer|exists:categories,id',
            'type' => [
                'nullable',
                'string',
                Rule::enum(DataTypeEnum::class)
            ],
        ];
    }

    public function getCategoryId(): int
    {
        return $this->integer('category_id');
    }

    public function getDataType(): DataTypeEnum
    {
        return DataTypeEnum::tryFrom($this->input('type')) ?? DataTypeEnum::getDefault();
    }
}

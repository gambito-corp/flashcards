<?php
// app/Http/Requests/Api/Medbanks/GetCategoriesRequest.php

namespace App\Http\Requests\Api\MedBanks;

use App\Enums\Api\MedBank\DataTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetCategoriesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'area_id' => 'required|integer|exists:areas,id',
            'type' => [
                'nullable',
                'string',
                Rule::enum(DataTypeEnum::class)
            ],
        ];
    }

    public function getAreaId(): int
    {
        return $this->integer('area_id');
    }

    public function getDataType(): DataTypeEnum
    {
        return DataTypeEnum::tryFrom($this->input('type')) ?? DataTypeEnum::getDefault();
    }
}

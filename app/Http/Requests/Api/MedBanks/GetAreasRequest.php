<?php
// app/Http/Requests/Api/Medbanks/GetAreasRequest.php

namespace App\Http\Requests\Api\MedBanks;

use App\Enums\Api\MedBank\DataTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GetAreasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => [
                'nullable',
                'string',
                Rule::enum(DataTypeEnum::class)
            ],
        ];
    }

    public function getDataType(): DataTypeEnum
    {
        return DataTypeEnum::tryFrom($this->input('type')) ?? DataTypeEnum::getDefault();
    }
}

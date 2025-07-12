<?php
// app/Http/Requests/Api/Pagos/Subscription/StoreSubscriptionRequest.php
namespace App\Http\Requests\Api\Pagos\Subscription;

use Illuminate\Foundation\Http\FormRequest;

class StoreSubscriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'use_plan' => ['required', 'boolean'],
            'product_id' => ['nullable', 'integer', 'exists:products,id'],
            // Datos para flujo con plan
            'plan_name' => ['required_if:use_plan,true', 'string', 'max:255'],
            'freq' => ['required_if:use_plan,true', 'integer', 'min:1'],
            'amount' => ['required_if:use_plan,true', 'numeric', 'min:0'],
            // Datos opcionales de tarjeta (tokenizado en frontend)
            'card_token' => ['nullable', 'string'],
        ];
    }
}

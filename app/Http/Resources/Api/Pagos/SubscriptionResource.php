<?php
// app/Http/Resources/Api/Pagos/SubscriptionResource.php
namespace App\Http\Resources\Api\Pagos;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubscriptionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'mercadopago_id' => $this->mercadopago_id,
            'preapproval_plan_id' => $this->preapproval_plan_id,
            'status' => $this->status,
            'init_point' => $this->init_point,
            'frequency' => $this->frequency,
            'frequency_type' => $this->frequency_type,
            'transaction_amount' => $this->transaction_amount,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}


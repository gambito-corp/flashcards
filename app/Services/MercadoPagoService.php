<?php

namespace App\Services;

use App\Http\Resources\suscipcionResource;
use App\Models\Product;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use MercadoPago\Client\Common\RequestOptions;
use MercadoPago\Client\PreApproval\PreApprovalClient;
use MercadoPago\MercadoPagoConfig;
use MercadoPago\Resources\PreApproval;

class MercadoPagoService
{

    public array $preapproval;
    public PreApproval | null $subscription;

    public Purchase | null $purchase;

    public function __construct(){}

    public function authorize(): void
    {
        MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
    }

    public function getPreapproval(Product $product): array
    {
        return $this->preapproval = (new suscipcionResource($product))->toArray(request());
    }

    public function createSubscription(): PreApproval
    {
        $this->authorize();
        $subscription = new PreApprovalClient();
        return  $this->subscription = $subscription->create($this->preapproval);
    }
    public function createPurchase(): void
    {
        $request = new RequestOptions();
        $this->createSubscription();

        $this->purchase = Purchase::create([
            'user_id'            => Auth::user()->id,
            'product_id'         => $this->preapproval['metadata']['plan_id'],
            'purchased_at'       => now(),
            'preaproval'         => json_encode($this->subscription),
            'preaproval_id'      => $this->subscription->id,
            'status'             => $this->subscription->status,
            'payer_id'           => $this->subscription->payer_id,
            'external_reference' => $this->subscription->external_reference,
            'init_point'         => $this->subscription->init_point,
            'payment_method_id'  => $this->subscription->payment_method_id,
            'suscripcionData'    => json_encode($this->preapproval),
        ]);
    }
    public function updatePurchase(Purchase $purchase): void
    {
        $this->createPurchase();
        $purchase->purchased_at         = now();
        $purchase->preaproval           = json_encode($this->subscription);
        $purchase->preaproval_id        = $this->subscription->id;
        $purchase->status               = $this->subscription->status;
        $purchase->payer_id             = $this->subscription->payer_id;
        $purchase->external_reference   = $this->subscription->external_reference;
        $purchase->init_point           = $this->subscription->init_point;
        $purchase->payment_method_id    = $this->subscription->payment_method_id;
        $purchase->suscripcionData      = json_encode($this->preapproval);
        $purchase->updated_at           = now();
        $purchase->save();
        $this->purchase = $purchase;
    }

    public function checkSuscription (): Purchase|null
    {
        return $this->purchase = Purchase::query()
            ->where('external_reference', $this->preapproval['external_reference'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function checkAuthorizedPurchase(): Purchase|null
    {
        return $this->purchase =  Purchase::query()
            ->where('user_id', Auth::id())
            ->where(function ($query) {
                $query->where('status', 'pending')
                    ->orWhere('status', 'authorized');
            })
            ->orderBy('id', 'desc')
            ->first();
    }

    public function getSubscription(): Purchase|null
    {
        $this->authorize();
        $client = new PreApprovalClient();
        if ($this->purchase) {
            return $this->subscription = $client->get($this->purchase?->preaproval_id);
        }else{
            return null;
        }
    }

    public function checkCronSubscrition(Purchase $purchase): Purchase|null
    {
        $this->purchase = $purchase;
        $this->getSubscription();
        $user = Auth::user();
        $this->updatePurchase($purchase);

        switch ($this->subscription->status) {
            case 'authorized':
                $user->status = 1;
                break;
            case 'pending':
            case 'cancelled':
            case 'paused':
            case 'suspended':
                $user->status = 0;
                break;
            default:
                return new Purchase();
        }
        $user->save();
        return $this->purchase;
    }


}

<?php

namespace App\Livewire\Profile;

use Illuminate\Support\Facades\Http;
use Livewire\Component;

class SubscriptionManager extends Component
{
    public $user;
    public $subscription;
    public $matchingResult;

    public function render()
    {
        $this->user = auth()->user()->load('purchases');

        $this->subscription = $this->user->purchases->where('preaproval_id', '!=', null)->where('status', 'active')->last();

        if($this->subscription?->preaproval_id){
            $url = "https://api.mercadopago.com/preapproval_plan/search";


            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . config('services.mercadopago.access_token')
            ])->get($url);
            if ($response->successful()) {
                $data = $response->json();
//                dd($data['results']);
                $parts = explode("preapproval_plan_id=", $this->subscription->init_point);
                $this->matchingResult = collect($data['results'])->firstWhere('id', $parts[1]);
            }else {
                //
            }
        }

        return view('livewire.profile.subscription-manager');
    }

    public function pauseSubscription()
    {
        $this->updateSubscriptionStatus('paused');
    }

    public function resumeSubscription()
    {
        $this->updateSubscriptionStatus('active');
    }

    private function updateSubscriptionStatus($newStatus)
    {
        try {
            $subscriptionId = $this->matchingResult['id'];
            $url = "https://api.mercadopago.com/preapproval/{$subscriptionId}";

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . config('services.mercadopago.access_token')
            ])->put($url, ['status' => $newStatus]);

            if ($response->successful()) {
                // Actualizar estado local
                $this->matchingResult['status'] = $newStatus;

                // Actualizar base de datos si es necesario
                Subscription::where('preapproval_id', $subscriptionId)
                    ->update(['status' => $newStatus]);

                $this->dispatch('subscriptionUpdated');
            } else {
                session()->flash('error', 'Error: ' . $response->body());
            }

        } catch (\Exception $e) {
            session()->flash('error', 'Error de conexión: ' . $e->getMessage());
        }
    }

    public function cancelSubscription()
    {
        $this->updateSubscriptionStatus('cancelled');
    }
}

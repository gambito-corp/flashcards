<?php

namespace App\Http\Controllers;

use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\Purchase;
use Illuminate\Support\Facades\Auth;
use MercadoPago\MercadoPagoConfig;

class HomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $purchase = Purchase::query()
            ->where('user_id', $user->id)
            ->where(function ($query) {
                $query->where('status', 'pending')
                    ->orWhere('status', 'authorized');
            })
            ->orderBy('id', 'desc')
            ->first();
        if ($purchase) {
            // Configurar el acceso a la API de Mercado Pago y obtener el estado actual de la suscripción
            MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
            $client = new \MercadoPago\Client\PreApproval\PreApprovalClient();
            $acceptedPurchase = $client->get($purchase->preaproval_id);

            $purchase->status = $acceptedPurchase->status;
            $purchase->save();

            if ($acceptedPurchase->status === 'authorized') {
                $user->status = 1;
                $user->save();
            }
        }

        $exams = Exam::query()
            ->with('examResults')
            ->whereHas('examResults', function ($query) {
                $query->where('user_id', auth()->id());
            })
            ->latest()
            ->take(5)
            ->get();

        $userId = auth()->id();
        $results = ExamResult::query()->where('user_id', $userId)->orderBy('created_at')->get();

        // Agrupación diaria
        $daily = $results->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->created_at)->format('d/m/Y');
        })->map(function($group) {
            return round($group->avg('total_score'), 2);
        });

        // Agrupación semanal (por semana del año y año)
        $weekly = $results->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->created_at)->format('W/Y');
        })->map(function($group) {
            return round($group->avg('total_score'), 2);
        });

        // Agrupación mensual
        $monthly = $results->groupBy(function($item) {
            return \Carbon\Carbon::parse($item->created_at)->format('m/Y');
        })->map(function($group) {
            return round($group->avg('total_score'), 2);
        });

        $dailyLabels = $daily->keys();
        $dailyData    = $daily->values();

        $weeklyLabels = $weekly->keys();
        $weeklyData   = $weekly->values();

        $monthlyLabels = $monthly->keys();
        $monthlyData   = $monthly->values();
        return view('index.dashboard', compact('exams', 'dailyLabels', 'dailyData', 'weeklyLabels', 'weeklyData', 'monthlyLabels', 'monthlyData'));
    }

    public function landing()
    {
        return view('index.landing');
    }
}

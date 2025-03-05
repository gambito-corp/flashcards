<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class MercadoPagoWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // Para depurar, puedes guardar el payload o hacer log:
        \Log::info('Webhook MercadoPago recibido:', $request->all());

        // Mercado Pago envía un parámetro 'action' y otros datos en el payload
        // Asegúrate de revisar la documentación para identificar el evento de renovación, por ejemplo, "payment.preapproval.updated"
        $action = $request->input('action');

        // Ejemplo: Si la acción es que se aprobó la renovación...
        if ($action === 'payment.preapproval.updated') {
            $status = $request->input('data.status');
            $externalReference = $request->input('data.external_reference'); // por ejemplo, puede identificar al usuario o suscripción

            // Supongamos que el external_reference contiene el ID del usuario
            $user = User::find($externalReference);
            if ($user) {
                if ($status === 'authorized') {
                    // La renovación fue exitosa, mantenemos o actualizamos el estado activo
                    $user->update(['status' => 1]);
                } else {
                    // Si no se autoriza, se marca el usuario como inactivo (status 0)
                    $user->update(['status' => 0]);
                }
            }
        }

        return response()->json(['status' => 'ok']);
    }
}

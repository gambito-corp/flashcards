<?php

namespace App\Http\Middleware;

use App\Models\SecurityIncident;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::guard('web')->user();   // guard explícito

        if (!$user || !$user->is_admin()) {

            // 1. Registrar el incidente en la base
            $incident = SecurityIncident::create([
                'user_id' => $user?->id,
                'ip_address' => $request->ip(),
                'occurred_at' => now(),
                'type' => 'unauthorized_admin_access',
                'severity' => 'warning',
                'url' => $request->fullUrl(),
                'payload' => $request->except(['password', '_token']),
                'user_agent' => $request->userAgent(),
                'blocked' => true,
                'notes' => 'Intento de acceso a panel sin privilegios',
            ]);

            // 2. Escribir en el canal de seguridad
            Log::channel('security')->warning('Unauthorized admin access', [
                'incident_id' => $incident->id,
                'user_id' => $user?->id,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);

            /* 2. Contador de intentos + bloqueo suave */
            if ($user) {
                $user->admin_attempts = ($user->admin_attempts ?? 0) + 1;

                if ($user->admin_attempts >= 3 && !$user->blocked_at) {
                    $incident->blocked = true;
                    $user->notes = 'Cuenta bloqueada por intentos fallidos de acceso al panel de administración';
                    $user->blocked_at = now();
                    $incident->save();
                }
                $user->save();
            }

            /* 3. Mensaje para el front */
            $warning = rawurlencode(
                'NO ERES UN USUARIO ADMINISTRADOR, EL INTENTO DE LOGUEO FUE REGISTRADO, ' .
                'AL TERCER INTENTO TU CUENTA SERÁ BLOQUEADA...'
            );

            $frontendLogin = rtrim(config('app.frontend_url',
                    env('FRONTEND_URL', 'http://front.flashcard.test')), '/')
                . '/login?warning=' . $warning;

            /* 4. Logout y redirección externa */
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->away($frontendLogin);
        }

        return $next($request);
    }
}

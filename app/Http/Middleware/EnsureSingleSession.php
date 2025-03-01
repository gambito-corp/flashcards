<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use BadMethodCallException;

class EnsureSingleSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $currentSession = session()->get('current_session_id', '');
            if (Auth::user()->current_session_id !== $currentSession) {
                try {
                    // Usamos el guard "web" para llamar a logout()
                    Auth::guard('web')->logout();
                } catch (BadMethodCallException $e) {
                    // En caso de error, redirigimos con un mensaje de aviso
                    return redirect()->route('login')->withErrors([
                        'message' => 'Error al cerrar sesión. ' . $e->getMessage()
                    ]);
                }
                return redirect()->route('login')->withErrors([
                    'message' => 'Tu sesión ha sido cerrada porque iniciaste sesión desde otro dispositivo.'
                ]);
            }
        }
        return $next($request);
    }
}

<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
//         if (Auth::check()) {
//             $currentSession = session()->getId();
//             if (Auth::user()->current_session_id !== $currentSession) {
//                 // Invalida la sesión y redirige al login con un mensaje
//                 Auth::logout();
//                 return redirect()->route('login')->withErrors([
//                     'message' => 'Tu sesión ha sido cerrada porque iniciaste sesión desde otro dispositivo.'
//                 ]);
//             }
//         }
        return $next($request);
    }
}

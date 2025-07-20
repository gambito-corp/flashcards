<?php

// app/Http/Middleware/RejectBlockedUsers.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class RejectBlockedUsers
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::guard('web')->check() && Auth::user()->blocked_at) {

            Log::channel('security')->notice('Petición de usuario bloqueado', [
                'user_id' => Auth::id(),
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
            ]);

            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')               // tu ruta pública de login
                ->withErrors(['email' =>
                    'Tu cuenta está bloqueada; no puedes acceder a ningún recurso.']);
        }

        return $next($request);
    }
}

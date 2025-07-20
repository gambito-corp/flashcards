<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CustomLoginController extends Controller
{
    public function showLoginForm()
    {
        // Devuelve una vista simple con el formulario de login
        return view('auth.login');
    }

    public function authenticate(Request $request)
    {
        // Validar campos
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Intentar autenticar
        if (Auth::attempt($request->only('email', 'password'))) {
            /* 2. ¿Está bloqueado? */
            if (Auth::user()->blocked_at) {
                /* Log del intento */
                Log::channel('security')->warning('Intento de login con cuenta BLOQUEADA', [
                    'user_id' => Auth::user()->id,
                    'ip' => $request->ip(),
                    'url' => $request->fullUrl(),
                ]);

                Auth::guard('web')->logout();          // fuera sesión
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Tu cuenta está BLOQUEADA por seguridad. Contacta con el administrador.',
                ]);
            }
            if (!Auth::user()->is_admin()) {
                return redirect()->intended('/admin/dashboard');
            }
            $usuario = Auth::user();
            $usuario->current_session_id = Str::random(10);
            $usuario->save();
            session()->put('current_session_id', $usuario->current_session_id);
            return redirect()->intended('/telescope/requests'); // Redirige a la página de Telescope
        }

        // Si falla, volver atrás con error
        return back()->withErrors(['email' => 'Credenciales inválidas']);
    }
}

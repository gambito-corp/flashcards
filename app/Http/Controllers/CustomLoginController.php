<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            $usuario = Auth::user();
            $usuario->current_session_id = Str::random(10);
            $usuario->save();
            session()->put('current_session_id', $usuario->current_session_id);
            return redirect()->intended('/dashboard');
        }

        // Si falla, volver atrás con error
        return back()->withErrors(['email' => 'Credenciales inválidas']);
    }
}

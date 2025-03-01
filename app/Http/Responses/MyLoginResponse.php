<?php

namespace App\Http\Responses;

use Illuminate\Support\Facades\Auth;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class MyLoginResponse implements LoginResponseContract
{

    /**
     * @inheritDoc
     */
    public function toResponse($request)
    {
        dd('hola');
        $user = Auth::user();
        $user->current_session_id = session()->getId();
        $user->save();

        return redirect()->intended('/dashboard');
    }
}

<?php

namespace App\Http\Controllers;

class ReactRedirectController extends Controller
{
    //
    public function redirectToReact()
    {
        $reactUrl = config('app.url') . '/new';
        return redirect($reactUrl);
    }
}

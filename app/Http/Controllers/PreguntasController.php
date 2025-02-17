<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PreguntasController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:root|admin|colab');
    }

    public function crearPregunta()
    {
        return view('preguntas.index');
    }
}

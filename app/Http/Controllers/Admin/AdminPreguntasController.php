<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminPreguntasController extends Controller
{
    public function index(){
        return view(view: 'admin.preguntas.index');
    }
}

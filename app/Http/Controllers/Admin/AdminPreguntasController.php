<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Question;
use Illuminate\Http\Request;

class AdminPreguntasController extends Controller
{
    public function index(){
        return view(view: 'admin.preguntas.index');
    }
    public function create(){
        return view(view: 'admin.preguntas.create');
    }
    public function edit(Question $pregunta){
        return view('admin.preguntas.edit', compact('pregunta'));
    }
}

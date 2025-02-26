<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tipo;
use Illuminate\Http\Request;

class TiposController extends Controller
{
    public function index(){
        return view(view: 'admin.tipos.index');
    }
    public function create(){
        return view(view: 'admin.tipos.create');
    }
    public function edit(Tipo $tipo){
        return view('admin.tipos.edit', compact('tipo'));
    }
}

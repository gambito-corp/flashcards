<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Universidad;
use Illuminate\Http\Request;

class UniversidadesController extends Controller
{
    public function index(){
        return view(view: 'admin.universidades.index');
    }
    public function create(){
        return view(view: 'admin.universidades.create');
    }
    public function edit(Universidad $universidad){
        return view('admin.universidades.edit', compact('universidad'));
    }
}

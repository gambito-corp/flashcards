<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;

class AsignaturasController extends Controller
{
    public function index()
    {
        return view(view: 'admin.asignaturas.index');
    }
    public function create()
    {
        return view(view: 'admin.asignaturas.create');
    }
    public function edit(Area $asignatura)
    {
        return view('admin.asignaturas.edit', compact('asignatura'));
    }
}

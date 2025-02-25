<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AsignaturasController extends Controller
{
    public function index()
    {
        return view(view: 'admin.asignaturas.index');
    }
}

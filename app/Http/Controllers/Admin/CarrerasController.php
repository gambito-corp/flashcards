<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CarrerasController extends Controller
{

    public function index()
    {
        return view('admin.carreras.index');
    }
}

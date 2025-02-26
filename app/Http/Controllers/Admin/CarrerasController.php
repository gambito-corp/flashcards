<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;

class CarrerasController extends Controller
{
    public function index()
    {
        return view('admin.carreras.index');
    }

    public function create()
    {
        return view('admin.carreras.create');
    }

    public function edit(Team $team)
    {
        return view('admin.carreras.edit', compact('team'));
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {

        return view('index.dashboard');
    }

    public function planes()
    {
        return view('index.planes');
    }

    public function landing()
    {
        return view('index.landing');
    }
}

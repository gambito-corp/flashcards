<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Config;
use Illuminate\Http\Request;

class ConfigController extends Controller
{
    public function index(){
        return view(view: 'admin.config.index');
    }

    public function edit(Config $config){
        return view('admin.config.edit', compact('config'));
    }
}

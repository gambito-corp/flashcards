<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;

class CategoriasController extends Controller
{
    public function index(){
        return view(view: 'admin.categorias.index');
    }
    public function create(){
        return view(view: 'admin.categorias.create');
    }
    public function edit(Category $category){
        return view('admin.categorias.edit', compact('category'));
    }
}

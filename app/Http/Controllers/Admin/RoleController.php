<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        return view(view: 'admin.role.index');
    }
    public function create()
    {
        return view(view: 'admin.role.create');
    }
    public function store()
    {

    }
    public function edit(Role $rol)
    {
        return view('admin.role.edit', compact('rol'));
    }
    public function update()
    {

    }
    public function destroy(){
    }

}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        return view(view: 'admin.users.index');
    }

    public function create()
    {
        return view(view: 'admin.users.create');
    }
    public function store()
    {

    }
    public function show()
    {
        return view(view: 'admin.users.show');
    }
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }
    public function update()
    {

    }
    public function destroy(){
    }
}

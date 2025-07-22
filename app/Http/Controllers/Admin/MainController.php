<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;

class MainController extends Controller
{
    public function index()
    {
        $totalUsers = User::query()->count();
        $totalPremiumUsers = User::query()->where('status', true)->count();
        return view('admin.dashboard', compact('totalUsers', 'totalPremiumUsers'));
    }

    public function test()
    {
        return phpinfo();
    }
}

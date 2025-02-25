<?php

namespace App\Providers;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\ServiceProvider;

class MenuServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $request = request();

        Config::set('menu.active', $request->query('key', 'default_value'));
    }
}

<?php

namespace App\Livewire;

use App\Services\Api\Commons\CommonService;
use Livewire\Component;

class NavLink extends Component
{
    protected CommonService $commonService;
    protected $listeners = [
        'refresh-navigation-menu' => '$refresh',
    ];

    public $menu;

    public function boot(CommonService $commonService)
    {
        $this->commonService = $commonService;
    }

    public function mount()
    {
        // Determinamos si estamos en la vista de administración
        $isAdminView = request()->is('admin/*') || request()->routeIs('admin.*');
        // Obtenemos el usuario autenticado
        $user = auth()->user();
        $this->menu = $this->commonService->getFilteredMenu($user, $isAdminView);
    }

    public function render()
    {
        return view('navigation-menu');
    }
}

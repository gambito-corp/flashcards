<?php

namespace App\Livewire;

use Livewire\Component;

class NavLink extends Component
{
    protected $listeners = [
        'refresh-navigation-menu' => '$refresh',
    ];

    public $menu;

    public function mount()
    {
        // Cargamos el menú desde la configuración
        $allMenuItems = config('menu');

        if (isset($allMenuItems['active'])) {
            unset($allMenuItems['active']);
        }
        // Detectamos si estamos en la vista de administración
        $isAdminView = request()->is('admin/*') || request()->routeIs('admin.*');

        // Filtramos los elementos según el grupo
        $this->menu = array_filter($allMenuItems, function ($item) use ($isAdminView) {
            // Si no se define el grupo, asumimos que es común
            if (!isset($item['group'])) {
                return true;
            }
            // Se muestran siempre los elementos comunes
            if ($item['group'] === 'common') {
                return true;
            }
            // Si es vista admin, se muestran los elementos del grupo admin
            if ($isAdminView && $item['group'] === 'admin') {
                return true;
            }
            // Si no es admin, se muestran los elementos del grupo usuario
            if (!$isAdminView && $item['group'] === 'user') {
                return true;
            }
            return false;
        });
    }

    public function render()
    {
        return view('navigation-menu');
    }
}

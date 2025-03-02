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
        $allMenuItems = config('menu');

        // Eliminar claves innecesarias
        if (isset($allMenuItems['active'])) {
            unset($allMenuItems['active']);
        }

        // Detectamos si estamos en la vista de administración
        $isAdminView = request()->is('admin/*') || request()->routeIs('admin.*');

        // Filtramos los elementos del menú según grupo y roles
        $this->menu = array_filter($allMenuItems, function ($item) use ($isAdminView) {
            // Si se define la clave roles, se verifica que el usuario tenga al menos uno
            if (isset($item['roles']) && count($item['roles']) > 0) {
                // Si el usuario no está autenticado o no tiene ninguno de esos roles, se omite el ítem
                if (!auth()->check() || !auth()->user()->hasAnyRole($item['roles'])) {
                    return false;
                }
            }

            // Si no se define el grupo, asumimos que es común
            if (!isset($item['group'])) {
                return true;
            }

            // Elementos comunes se muestran siempre
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

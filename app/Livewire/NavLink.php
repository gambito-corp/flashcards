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

        // Eliminamos claves innecesarias (si existe alguna)
        if (isset($allMenuItems['active'])) {
            unset($allMenuItems['active']);
        }

        // Determinamos si estamos en la vista de administración
        $isAdminView = request()->is('admin/*') || request()->routeIs('admin.*');

        // Obtenemos el usuario autenticado
        $user = auth()->user();

        $this->menu = array_filter($allMenuItems, function ($item) use ($isAdminView, $user) {
            // Si el usuario no está autenticado, no se muestra ningún ítem
            if (!$user) {
                return false;
            }

            // Filtrado de roles: si se define 'roles' y el usuario no tiene ninguno, se descarta el ítem.
            if (isset($item['roles']) && count($item['roles']) > 0) {
                if (!$user->hasAnyRole($item['roles'])) {
                    return false;
                }
            }

            // Filtrado premium:
            // Si el ítem requiere premium, el usuario debe tener status == 1.
            if (isset($item['need_premium'])) {
                if ($item['need_premium'] === true && $user->status != 1) {
                    return false;
                }
                // Si el ítem no requiere premium, se mostrará solo a usuarios con status 0 o 1.
                if ($item['need_premium'] === false && !in_array($user->status, [0, 1])) {
                    return false;
                }
            }

            // Filtrado por grupo:
            // Si no se define el grupo, se muestra.
            if (!isset($item['group'])) {
                return true;
            }

            // Los ítems del grupo 'common' se muestran a todos.
            if ($item['group'] === 'common') {
                return true;
            }

            // Si estamos en vista de administración, se muestran los ítems del grupo 'admin'
            if ($isAdminView && $item['group'] === 'admin') {
                return true;
            }

            // Si no es vista admin, se muestran los ítems del grupo 'user'
            if (!$isAdminView && $item['group'] === 'user') {
                return true;
            }

            // En caso de que no se cumplan las condiciones anteriores, no se muestra el ítem.
            return false;
        });
    }

    public function render()
    {
        return view('navigation-menu');
    }
}

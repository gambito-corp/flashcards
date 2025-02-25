<?php

namespace App\Livewire\Admin\Assets;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Sidebar extends Component
{

    protected AuthService $authService;

    public $nav_links = [];

    protected $listeners = [
        'refresh-navigation-menu' => '$refresh',
    ];

    public function mount()
    {
        $this->nav_links = [
            [
                'name' => __('Home'),
                'route' => 'dashboard',
                'active' => request()->routeIs('dashboard'),
                'roles' => ['admin', 'root', 'colab']
            ],
            [
                'name' => __('Administracion'),
                'route' => 'admin.index',
                'active' => request()->routeIs('dashboard'),
                'roles' => ['admin', 'root', 'colab']
            ],
            [
                'name' => __('Usuarios'),
                'route' => 'preguntas.index',
                'active' => request()->routeIs('preguntas.index'),
                'roles' => ['admin', 'root', 'colab']
            ],
            [
                'name' => __('Universidades'),
                'route' => 'preguntas.index',
                'active' => request()->routeIs('preguntas.index'),
                'roles' => ['admin', 'root', 'colab']
            ],
            [
                'name' => __('Carreras'),
                'route' => 'preguntas.index',
                'active' => request()->routeIs('preguntas.index'),
                'roles' => ['admin', 'root', 'colab']
            ],
            [
                'name' => __('Asignaturas'),
                'route' => 'preguntas.index',
                'active' => request()->routeIs('preguntas.index'),
                'roles' => ['admin', 'root', 'colab']
            ],
            [
                'name' => __('Categorias'),
                'route' => 'preguntas.index',
                'active' => request()->routeIs('preguntas.index'),
                'roles' => ['admin', 'root', 'colab']
            ],
            [
                'name' => __('Tipos'),
                'route' => 'preguntas.index',
                'active' => request()->routeIs('preguntas.index'),
                'roles' => ['admin', 'root', 'colab']
            ],
            [
                'name' => __('Preguntas'),
                'route' => 'preguntas.index',
                'active' => request()->routeIs('preguntas.index'),
                'roles' => ['admin', 'root', 'colab']
            ],
        ];

        $this->nav_links = array_filter($this->nav_links, function ($link) {
            if (in_array('*', $link['roles'])) {
                return true;
            }
            return Auth::user()->hasAnyRole($link['roles']);
        });
    }

    public function render()
    {
        return view('livewire.admin.assets.sidebar', [
            'nav_links' => $this->nav_links
        ]);
    }
}

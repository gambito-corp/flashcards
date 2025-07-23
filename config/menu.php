<?php

return [
    [
        'group' => 'common',
        'name' => 'Panel Administrador',
        'route' => 'admin.index',
        'active' => 'admin.index',
        'roles' => ['admin', 'root', 'colab'],
        'need_premium' => false
    ],
    // ELEMENTOS EXCLUSIVOS VISTA ADMINISTRACIÓN
    [
        'group' => 'admin',
        'name' => 'Roles',
        'route' => 'roles.index',
        'active' => 'admin.roles.index',
        'roles' => ['admin', 'root', 'colab'],
        'need_premium' => false
    ],
    [
        'group' => 'admin',
        'name' => 'Usuarios',
        'route' => 'usuarios.index',
        'active' => 'admin.usuarios.index',
        'roles' => ['admin', 'root', 'colab'],
        'need_premium' => false
    ],
    [
        'group' => 'admin',
        'name' => 'Universidades',
        'route' => 'universidades.index',
        'active' => 'admin.universidades.index',
        'roles' => ['admin', 'root', 'colab'],
        'need_premium' => false
    ],
    [
        'group' => 'admin',
        'name' => 'Carreras',
        'route' => 'carreras.index',
        'active' => 'admin.carreras.index',
        'roles' => ['admin', 'root', 'colab'],
        'need_premium' => false
    ],
    [
        'group' => 'admin',
        'name' => 'Asignaturas',
        'route' => 'asignaturas.index',
        'active' => 'admin.asignaturas.index',
        'roles' => ['admin', 'root', 'colab'],
        'need_premium' => false
    ],
    [
        'group' => 'admin',
        'name' => 'Categorías',
        'route' => 'categorias.index',
        'active' => 'admin.categorias.index',
        'roles' => ['admin', 'root', 'colab'],
        'need_premium' => false
    ],
    [
        'group' => 'admin',
        'name' => 'Tipos',
        'route' => 'tipos.index',
        'active' => 'admin.tipos.index',
        'roles' => ['admin', 'root', 'colab'],
        'need_premium' => false
    ],
    [
        'group' => 'admin',
        'name' => 'Preguntas',
        'route' => 'preguntas.index',
        'active' => 'preguntas.index',
        'roles' => ['admin', 'root', 'colab'],
        'need_premium' => false
    ],
    [
        'group' => 'admin',
        'name' => 'Telescope',
        'route' => 'telescope',
        'active' => 'telescope',
        'roles' => ['admin', 'root', 'colab'],
        'need_premium' => false
    ],
];

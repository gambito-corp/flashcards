<?php

return [
    [
        'group'         => 'common',
        'name'          => 'Dashboard',
        'route'         => 'dashboard',
        'active'        => 'dashboard',
        'roles'         => ['admin', 'root', 'colab', 'user'],
        'need_premium'  => false
    ],
    [
        'group'  => 'common',
        'name'   => 'Panel Administrador',
        'route'  => 'admin.index',
        'active' => 'admin.index',
        'roles'  => ['admin', 'root', 'colab'],
        'need_premium' => false
    ],

    // ELEMENTOS EXCLUSIVOS VISTA USUARIOS
    [
        'group'  => 'user',
        'name'   => 'Examenes',
        'route'  => 'examenes.index',
        'active' => 'examenes.index',
        'roles'  => ['admin', 'root', 'colab', 'user'],
        'need_premium' => false
    ],
    [
        'group'  => 'user',
        'name'   => 'Flashcard',
        'route'  => 'flashcard.index',
        'active' => 'flashcard.index',
        'roles'  => ['admin', 'root', 'colab', 'user'],
        'need_premium' => false
    ],
    [
        'group'  => 'user',
        'name'   => 'MediSearch',
        'route'  => 'medisearch.index',
        'active' => 'medisearch.index',
        'roles'  => ['admin', 'root', 'colab', 'user'],
        'need_premium' => true
    ],

    // ELEMENTOS EXCLUSIVOS VISTA ADMINISTRACIÓN
    [
        'group'  => 'admin',
        'name'   => 'Usuarios',
        'route'  => 'admin.usuarios.index',
        'active' => 'admin.usuarios.index',
        'roles'  => ['admin', 'root', 'colab'],
        'need_premium' => false
    ],
    [
        'group'  => 'admin',
        'name'   => 'Universidades',
        'route'  => 'admin.universidades.index',
        'active' => 'admin.universidades.index',
        'roles'  => ['admin', 'root', 'colab'],
        'need_premium' => false
    ],
    [
        'group'  => 'admin',
        'name'   => 'Carreras',
        'route'  => 'admin.carreras.index',
        'active' => 'admin.carreras.index',
        'roles'  => ['admin', 'root', 'colab'],
        'need_premium' => false
    ],
    [
        'group'  => 'admin',
        'name'   => 'Asignaturas',
        'route'  => 'admin.asignaturas.index',
        'active' => 'admin.asignaturas.index',
        'roles'  => ['admin', 'root', 'colab'],
        'need_premium' => false
    ],
    [
        'group'  => 'admin',
        'name'   => 'Categorías',
        'route'  => 'admin.categorias.index',
        'active' => 'admin.categorias.index',
        'roles'  => ['admin', 'root', 'colab'],
        'need_premium' => false
    ],
    [
        'group'  => 'admin',
        'name'   => 'Tipos',
        'route'  => 'admin.tipos.index',
        'active' => 'admin.tipos.index',
        'roles'  => ['admin', 'root', 'colab'],
        'need_premium' => false
    ],
    [
        'group'  => 'admin',
        'name'   => 'Preguntas',
        'route'  => 'admin.preguntas.index',
        'active' => 'preguntas.index',
        'roles'  => ['admin', 'root', 'colab'],
        'need_premium' => false
    ],
];

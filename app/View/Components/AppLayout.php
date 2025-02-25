<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Título a mostrar en el header.
     *
     * @var string|null
     */
    public $title;

    /**
     * Nombre del ícono (sufijo para la clase FontAwesome).
     *
     * @var string|null
     */
    public $icon;

    /**
     * Crea una nueva instancia del componente.
     *
     * @param  string|null  $title
     * @param  string|null  $icon
     */
    public function __construct($title = null, $icon = null)
    {
        $this->title = $title;
        $this->icon = $icon;
    }

    /**
     * Obtiene la vista que representa el componente.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}

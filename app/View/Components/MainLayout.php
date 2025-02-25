<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class MainLayout extends Component
{
    public $title;
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
        $this->icon  = $icon;
    }

    /**
     * Obtiene la vista que representa el componente.
     */
    public function render(): View|Closure|string
    {
        return view('components.main-layout');
    }
}

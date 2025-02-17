<?php

namespace App\Livewire\Exams;

use Livewire\Component;

class Index extends Component
{
    public $areas;
    public function render()
    {
        return view('livewire.exams.index');
    }
}

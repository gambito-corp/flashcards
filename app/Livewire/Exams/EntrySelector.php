<?php

namespace App\Livewire\Exams;

use Livewire\Component;

class EntrySelector extends Component
{
    public $mode = null;

    public function selectMode($mode)
    {
        if ($mode === 'analisis') {
            return $this->redirect(route('examenes.estadisticas'));
        }
        $this->mode = $mode;
    }


    public function render()
    {
        // Según el modo, carga el builder, el pool de fallos, el análisis, etc.
        return match ($this->mode) {
            'global' => view('livewire.exams.wrapper.failed-global'),
            'usuario' => view('livewire.exams.wrapper.failed-user'),
            'ia' => view('livewire.exams.wrapper.ia-exam'),
            'normal' => view('livewire.exams.wrapper.normal-builder'),
            default => view('livewire.exams.entry-selector'),

        };
    }
}

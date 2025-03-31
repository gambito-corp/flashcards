<?php

namespace App\Livewire\Exams;

use Livewire\Component;
use Livewire\Attributes\On;

class QuestionSelector extends Component
{
    public $options;
    public $label;
    public $event;
    public $tabId;

    public $selected;

    public function mount($options, $label, $event, $tabId)
    {
        $this->options = $options;
        $this->label = $label;
        $this->event = $event;
        $this->tabId = $tabId;
    }

    #[On('categories-loaded')]
    #[On('tipos-loaded')]
    public function updateOptions($options)
    {
        $this->options = $options;
    }

    public function updatedSelected($value)
    {
        $this->dispatch($this->event, selected: $value);
    }

    public function render()
    {
        return view('livewire.exams.selector');
    }
}

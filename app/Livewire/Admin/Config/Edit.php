<?php

namespace App\Livewire\Admin\Config;

use App\Models\Config;
use Livewire\Component;

class Edit extends Component
{
    public Config $config;
    public $value;

    public function mount(Config $config) // Aunque no es obligatorio, es buena práctica
    {
        $this->config = $config;
        $this->value = $config->value;
    }

    public function render()
    {
        return view('livewire.admin.config.edit');
    }
    public function update()
    {
        $this->validate([
            'config.value' => 'required|string|max:255'
        ]);

        $this->config->value = $this->value;
        $this->config->save();

        session()->flash('message', 'Configuración actualizada correctamente.');
    }
}

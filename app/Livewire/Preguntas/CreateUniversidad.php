<?php

namespace App\Livewire\Preguntas;

use Livewire\Component;
use App\Models\Universidad; // Asegúrate de tener este modelo
use Illuminate\Support\Facades\Session;

class CreateUniversidad extends Component
{
    public $name;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
        ];
    }

    public function store()
    {
        $this->validate();

        // Crea la universidad
        Universidad::create([
            'name' => $this->name,
        ]);

        Session::flash('message', 'Universidad creada correctamente.');

        // Reinicia el campo del formulario
        $this->reset('name');
    }

    public function render()
    {
        return view('livewire.preguntas.create-universidad');
    }
}

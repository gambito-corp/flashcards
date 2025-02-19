<?php

namespace App\Livewire\Preguntas;

use Livewire\Component;
use App\Models\Team;

class CreateCarrera extends Component
{
    public $showModal = false; // Control de visibilidad del modal
    public $name;             // Campo para el nombre de la carrera

    protected $rules = [
        'name' => 'required|string|min:3',
    ];

    public function openModal()
    {
        $this->resetValidation();
        $this->reset(['name']);
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
    }

    public function store()
    {
        $this->validate();

        // Crear la carrera (Team)
        Team::create([
            'user_id'       => auth()->id(),   // Ajusta según tu lógica de propietario
            'name'          => $this->name,
            'personal_team' => false,
        ]);

        session()->flash('message', 'Carrera creada correctamente.');

        $this->closeModal();
        // Opcionalmente, puedes emitir un evento para refrescar la lista de carreras
        // $this->emit('carreraCreada');
    }

    public function render()
    {
        return view('livewire.preguntas.create-carrera');
    }
}

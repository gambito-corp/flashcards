<?php

namespace App\Livewire\Preguntas;

use Livewire\Component;
use App\Models\Category;
use App\Models\Tipo; // Asegúrate de tener el modelo Tipo
use Illuminate\Support\Facades\Session;

class CreateTipo extends Component
{
    public $selectedCategory;
    public $name;
    public $categories;

    public function mount()
    {
        // Cargar las categorías para el select
        $this->categories = Category::all();
    }

    protected function rules()
    {
        return [
            'selectedCategory' => 'required|exists:categories,id',
            'name'             => 'required|string|max:255',
        ];
    }

    public function store()
    {
        $this->validate();

        // Crear el Tipo
        Tipo::create([
            'category_id' => $this->selectedCategory,
            'name'        => $this->name,
        ]);

        Session::flash('message', 'Tipo creado correctamente.');

        // Opcional: Reiniciar el formulario
        $this->reset(['selectedCategory', 'name']);
    }

    public function render()
    {
        return view('livewire.preguntas.create-tipo');
    }
}

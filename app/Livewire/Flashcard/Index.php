<?php

namespace App\Livewire\Flashcard;

use Livewire\Component;
use App\Models\FcCard;
use App\Models\FcCategory;

class Index extends Component
{
    // Propiedades para el formulario de creación de flashcards
    public $pregunta;
    public $url;
    public $imagen;
    public $respuesta;
    public $url_respuesta;
    public $imagen_respuesta;
    public $selectedCategories = []; // IDs de categorías seleccionadas (opcional)

    // Propiedad para el formulario de creación de categorías
    public $categoryName;

    // Propiedades para la selección de flashcards para el juego
    public $cards = [];          // Flashcards del usuario
    public $selectedCards = [];  // IDs de flashcards seleccionadas para el juego

    // Propiedad para almacenar las categorías disponibles del usuario
    public $availableCategories = [];

    public function mount()
    {
        // Cargamos las categorías y flashcards del usuario autenticado
        $this->availableCategories = FcCategory::where('user_id', auth()->id())->get();
        $this->cards = FcCard::where('user_id', auth()->id())->with('categories')->get();
    }

    // Reglas para la validación de flashcards (solo pregunta y respuesta son obligatorios)
    protected $rules = [
        'pregunta'           => 'required|string',
        'respuesta'          => 'required|string',
        'url'                => 'nullable|url',
        'imagen'             => 'nullable|string',
        'url_respuesta'      => 'nullable|url',
        'imagen_respuesta'   => 'nullable|string',
        'selectedCategories' => 'nullable|array',
    ];

    // Reglas para la validación de la categoría
    protected $rulesCategory = [
        'categoryName' => 'required|string|max:255',
    ];

    public function createCard()
    {
        $this->validate();

        // Creamos la flashcard asociándola al usuario autenticado
        $card = FcCard::create([
            'user_id'          => auth()->id(),
            'pregunta'         => $this->pregunta,
            'url'              => $this->url,
            'imagen'           => $this->imagen,
            'respuesta'        => $this->respuesta,
            'url_respuesta'    => $this->url_respuesta,
            'imagen_respuesta' => $this->imagen_respuesta,
        ]);

        // Si se han seleccionado categorías, se asocian a la flashcard
        if (!empty($this->selectedCategories)) {
            $card->categories()->attach($this->selectedCategories);
        }

        // Reiniciamos los campos del formulario de flashcard
        $this->reset(['pregunta', 'url', 'imagen', 'respuesta', 'url_respuesta', 'imagen_respuesta', 'selectedCategories']);

        // Actualizamos la lista de flashcards
        $this->cards = FcCard::where('user_id', auth()->id())->with('categories')->get();

        session()->flash('message', 'Flashcard creada correctamente.');
    }

    public function createCategory()
    {
        $this->validate($this->rulesCategory);

        // Creamos la categoría asociada al usuario autenticado
        FcCategory::create([
            'user_id' => auth()->id(),
            'nombre'  => $this->categoryName,
        ]);

        // Actualizamos la lista de categorías disponibles
        $this->availableCategories = FcCategory::where('user_id', auth()->id())->get();

        $this->reset('categoryName');
        session()->flash('message', 'Categoría creada correctamente.');
    }

    public function startGame()
    {
        // Si no se ha seleccionado ninguna flashcard, mostramos un mensaje y detenemos la acción
        if (empty($this->selectedCards)) {
            session()->flash('message', 'Debes seleccionar al menos una flashcard para el juego.');
            return null;
        }

        // Guardamos en la sesión los IDs de las flashcards seleccionadas
        session()->put('selected_cards', $this->selectedCards);

        // Redirigimos a la ruta del juego
        return redirect()->route('flashcard.game');
    }

    public function toggleCard($cardId)
    {
        if (in_array($cardId, $this->selectedCards)) {
            $this->selectedCards = array_diff($this->selectedCards, [$cardId]);
        } else {
            $this->selectedCards[] = $cardId;
        }
    }

    public function render()
    {
        return view('livewire.flashcard.index');
    }
}

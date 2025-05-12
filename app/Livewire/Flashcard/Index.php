<?php

namespace App\Livewire\Flashcard;

use Livewire\Component;
use App\Models\FcCard;
use App\Models\FcCategory;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;
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
        if (auth()->user()->status == 0) {
            $this->cards = FcCard::query()
                ->where('user_id', auth()->id())
                ->with('categories')
                ->orderBy('errors', 'desc')
                ->limit(50)
                ->get();
        } else {
            $this->cards = FcCard::query()
                ->where('user_id', auth()->id())
                ->with('categories')
                ->orderBy('errors', 'desc')
                ->get();
        }
    }

    // Reglas para la validación de flashcards (solo pregunta y respuesta son obligatorios)
    protected $rules = [
        'pregunta'           => 'required|string',
        'respuesta'          => 'required|string',
        'url'                => 'nullable|url',
        'imagen'             => 'nullable|image|max:10240',
        'url_respuesta'      => 'nullable|url',
        'imagen_respuesta'   => 'nullable|image|max:10240',
        'selectedCategories' => 'nullable|array',
    ];

    // Reglas para la validación de la categoría
    protected $rulesCategory = [
        'categoryName' => 'required|string|max:255',
    ];

    public function createCard()
    {
        $this->validate();

        // Validación: si el usuario no es premium (status == 0) y ya tiene 50 flashcards, no permitir crear más
        if (auth()->user()->status == 0 && !auth()->user()->hasAnyRole(['root', 'admin', 'colab', 'Rector'])) {
            $currentCount = FcCard::query()->where('user_id', auth()->id())->count();
            if ($currentCount >= 50) {
                session()->flash('error', 'Has alcanzado el límite de 50 flashcards. Adquiere premium para crear más.');
                return;
            }
        }
        // Procesamos la imagen, subiéndola a S3 si se ha seleccionado
        $imagenPath = $this->imagen ? $this->imagen->store('flashcard_images', 's3') : null;
        $imagenRespuestaPath = $this->imagen_respuesta ? $this->imagen_respuesta->store('flashcard_images', 's3') : null;

        // Creamos la flashcard asociándola al usuario autenticado
        $card = FcCard::create([
            'user_id'          => auth()->id(),
            'pregunta'         => $this->pregunta,
            'url'              => $this->url,
            'imagen'           => $imagenPath,
            'respuesta'        => $this->respuesta,
            'url_respuesta'    => $this->url_respuesta,
            'imagen_respuesta' => $imagenRespuestaPath,
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
        if (empty($this->selectedCards)) {
            session()->flash('message', 'Debes seleccionar al menos una flashcard para el juego.');
            return null;
        }
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

<?php

namespace App\Livewire\Flashcard;

use App\Models\FcCard;
use App\Models\FcCategory;
use App\Services\Usuarios\MBIAService;
use Livewire\Component;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;

    public $activeTab = 'sin-categoria';
    public $slidersScroll = [];
    public $searchTerms = [];
    public $pregunta;
    public $url;
    public $imagen;
    public $respuesta;
    public $url_respuesta;
    public $imagen_respuesta;
    public $selectedCategories = [];
    public $categoryName;
    public $cards = [];
    public $selectedCards = [];
    public $availableCategories = [];
    protected MBIAService $openai;

    public function boot(MBIAService $openai)
    {
        $this->openai = $openai;
    }

    public function mount()
    {
        $this->activeTab = 'sin-categoria';
        $this->availableCategories = FcCategory::where('user_id', auth()->id())->get();

        // Inicializar slides y search
        $this->slidersScroll['sin-categoria'] = 0;
        $this->searchTerms['sin-categoria'] = '';
        foreach ($this->availableCategories as $cat) {
            $key = 'cat-' . $cat->id;
            $this->slidersScroll[$key] = 0;
            $this->searchTerms[$key] = '';
        }

        // Cargar las cards
        $this->cards = FcCard::query()
            ->where('user_id', auth()->id())
            ->with('categories')
            ->when(auth()->user()->status == 0, function ($q) {
                $q->limit(50);
            })
            ->orderBy('errors', 'desc')
            ->get();
    }

    // Helpers para filtrar por tab y búsqueda
    public function getCardsForTab($tabId, $search = null)
    {
        if ($tabId === 'sin-categoria') {
            $query = $this->cards->filter(fn($card) => $card->categories->isEmpty());
        } else {
            $catId = intval(str_replace('cat-', '', $tabId));
            $query = $this->cards->filter(fn($card) => $card->categories->contains('id', $catId));
        }

        if ($search) {
            $query = $query->filter(fn($card) => stristr($card->pregunta, $search));
        }
        return $query->values(); // importante: resetear keys para foreach
    }

    public function updatingSearchTerms($value, $key)
    { /* Livewire requiere el método para detectar cambios */
    }

    public function setActiveTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function moveSlider($tab, $direction)
    {
        if (!isset($this->slidersScroll[$tab])) {
            $this->slidersScroll[$tab] = 0;
        }
        $moveAmount = 300;
        if ($direction === 'left') {
            $this->slidersScroll[$tab] = max(0, $this->slidersScroll[$tab] - $moveAmount);
        } else {
            $this->slidersScroll[$tab] += $moveAmount;
        }
    }

    public function toggleCard($cardId)
    {
        if (in_array($cardId, $this->selectedCards)) {
            $this->selectedCards = array_diff($this->selectedCards, [$cardId]);
        } else {
            $this->selectedCards[] = $cardId;
        }
    }

    public function toggleSelectAll()
    {
        $allCardIds = $this->cards->pluck('id')->all();
        $allSelected = count($this->selectedCards) === count($allCardIds);

        if ($allSelected) {
            $this->selectedCards = [];
        } else {
            $this->selectedCards = $allCardIds;
        }
    }

    public function toggleSelectAllTab($tabId)
    {
        // Selección respeta filtro de búsqueda
        $search = $this->searchTerms[$tabId] ?? '';
        $cardsToSelect = $this->getCardsForTab($tabId, $search);
        $tabCardIds = $cardsToSelect->pluck('id')->all();
        $allSelected = count(array_intersect($tabCardIds, $this->selectedCards)) === count($tabCardIds);

        if ($allSelected) {
            $this->selectedCards = array_diff($this->selectedCards, $tabCardIds);
        } else {
            $this->selectedCards = array_unique(array_merge($this->selectedCards, $tabCardIds));
        }
    }

    protected $rules = [
        'pregunta' => 'required|string',
        'respuesta' => 'required|string',
        'url' => 'nullable|url',
        'imagen' => 'nullable|image|max:10240',
        'url_respuesta' => 'nullable|url',
        'imagen_respuesta' => 'nullable|image|max:10240',
        'selectedCategories' => 'nullable|array',
    ];

    protected $rulesCategory = [
        'categoryName' => 'required|string|max:255',
    ];

    public function createCard()
    {
        $this->validate();

        if (auth()->user()->status == 0 && !auth()->user()->hasAnyRole(['root', 'admin', 'colab', 'Rector'])) {
            $currentCount = FcCard::query()->where('user_id', auth()->id())->count();
            if ($currentCount >= 50) {
                session()->flash('error', 'Has alcanzado el límite de 50 flashcards. Adquiere premium para crear más.');
                return;
            }
        }
        $imagenPath = $this->imagen ? $this->imagen->store('flashcard_images', 's3') : null;
        $imagenRespuestaPath = $this->imagen_respuesta ? $this->imagen_respuesta->store('flashcard_images', 's3') : null;

        $card = FcCard::create([
            'user_id' => auth()->id(),
            'pregunta' => $this->pregunta,
            'url' => $this->url,
            'imagen' => $imagenPath,
            'respuesta' => $this->respuesta,
            'url_respuesta' => $this->url_respuesta,
            'imagen_respuesta' => $imagenRespuestaPath,
        ]);
        if (!empty($this->selectedCategories)) {
            $card->categories()->attach($this->selectedCategories);
        }
        $this->reset(['pregunta', 'url', 'imagen', 'respuesta', 'url_respuesta', 'imagen_respuesta', 'selectedCategories']);
        $this->cards = FcCard::where('user_id', auth()->id())->with('categories')->get();
        session()->flash('message', 'Flashcard creada correctamente.');
    }

    public function createCategory()
    {
        $this->validate($this->rulesCategory);

        FcCategory::create([
            'user_id' => auth()->id(),
            'nombre' => $this->categoryName,
        ]);
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
        return redirect()->route('flashcard.game');
    }

    public function generarPreguntaIA()
    {
        $prompt = 'Genera una pregunta de flashcard sobre el tema: ' . ($this->pregunta ?? 'tema general');
        $this->pregunta = empty($this->pregunta) ? '.' : $this->pregunta;
        $this->pregunta = trim($this->openai->completarTexto($prompt, $this->pregunta));
        $this->resetValidation('pregunta'); // Limpia el error visual si la IA rellena el campo
    }

    public function generarRespuestaIA()
    {
        $prompt = 'Genera una respuesta de Flascard breve para la pregunta: ' . ($this->pregunta ?? '...');
        $this->respuesta = empty($this->respuesta) ? '.' : $this->respuesta;
        $this->respuesta = trim($this->openai->completarTexto($prompt, $this->respuesta));
        $this->resetValidation('respuesta');
    }

    public function render()
    {
        // Prepara para todas las tabs los resultados filtrados y seleccionados para la vista
        $filteredTabs = [
            'sin-categoria' => $this->getCardsForTab('sin-categoria', $this->searchTerms['sin-categoria'] ?? ''),
        ];
        foreach ($this->availableCategories as $cat) {
            $tabKey = 'cat-' . $cat->id;
            $filteredTabs[$tabKey] = $this->getCardsForTab($tabKey, $this->searchTerms[$tabKey] ?? '');
        }

        return view('livewire.flashcard.index', [
            'filteredTabs' => $filteredTabs,
        ]);
    }
}

<?php

namespace App\Livewire\Flashcard;

use Livewire\Component;
use App\Models\FcCard;

class Game extends Component
{
    public $cards = [];
    public $currentIndex = 0; // Índice de la flashcard actual

    public function mount()
    {
        // Recuperamos los IDs de las flashcards seleccionadas desde la sesión
        $selectedIds = session('selected_cards', []);

        if (!empty($selectedIds)) {
            // Cargamos las flashcards junto con sus categorías (si las tienen)
            $this->cards = FcCard::whereIn('id', $selectedIds)->with('categories')->get();
        } else {
            // Si no hay flashcards seleccionadas, redirigimos de vuelta al index con un mensaje
            session()->flash('message', 'No se han seleccionado flashcards para el juego.');
            return redirect()->route('flashcard.index');
        }
        return null;
    }

    // Method para pasar a la siguiente flashcard
    public function nextCard()
    {
        if (count($this->cards) > 0) {
            $this->currentIndex = ($this->currentIndex + 1) % count($this->cards);
        }
    }

    public function render()
    {
        return view('livewire.flashcard.game');
    }
}

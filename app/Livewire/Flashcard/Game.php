<?php

namespace App\Livewire\Flashcard;

use App\Models\FcGroupCard;
use Livewire\Component;
use App\Models\FcCard;

class Game extends Component
{
    public $cards = [];
    public $currentIndex = 0; // Índice de la flashcard actual
    public $showAnswer = false;
    public $correctCount = 0; // Asegúrate de incluir esta propiedad
    public $total = 0;

    public function mount()
    {
        $selectedIds = session('selected_cards', []);
        if (!empty($selectedIds)) {
            $this->cards = FcCard::query()->whereIn('id', $selectedIds)
                ->with('categories')
                ->orderBy('errors', 'desc')
                ->get();
            $this->total = $this->cards->count();
        } else {
            session()->flash('message', 'No se han seleccionado flashcards para el juego.');
            return redirect()->route('flashcard.index');
        }
    }

    public function revealAnswer()
    {
        $this->showAnswer = true;
    }

    public function markCorrect()
    {
        $this->correctCount++; // Incrementamos primero
        $this->showAnswer = false;
        $this->nextCard();
    }


    public function markIncorrect()
    {
        $card = $this->cards[$this->currentIndex];

        $card->errors++;
        $card->save();

        $this->showAnswer = false;
        $this->nextCard();
    }

    private function nextCard()
    {
        $this->currentIndex++;
        if ($this->currentIndex >= count($this->cards)) {
            // Creamos el registro del grupo de flashcards con la puntuación
            $group = FcGroupCard::query()->create([
                'user_id' => auth()->id(),
                'correct' => $this->correctCount,
                'incorrect' => $this->total - $this->correctCount,
                'total' => $this->total,
            ]);

            $cardIds = $this->cards->pluck('id')->toArray();
            $group->cards()->attach($cardIds);
            // Guardamos en sesión el ID del grupo de flashcards
            session()->put('fc_group_card_id', $group->id);

            session()->flash('message', '¡Has terminado de repasar las flashcards!');
            return redirect()->route('flashcard.results');
        }
    }


    public function render()
    {
        $currentCard = $this->cards[$this->currentIndex] ?? null;
        return view('livewire.flashcard.game', compact('currentCard'));
    }
}

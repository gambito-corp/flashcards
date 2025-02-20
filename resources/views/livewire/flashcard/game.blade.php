<div x-data="{ showAnswer: false, timerId: null }">
    <h1>Juego de Flashcards</h1>

    @if($cards->isNotEmpty())
        @php
            // Obtenemos la flashcard actual según el índice
            $currentCard = $cards[$currentIndex] ?? null;
        @endphp

        @if($currentCard)
            <div class="card" style="border: 1px solid #ccc; padding: 1rem; margin-bottom: 1rem;">
                <!-- Muestra la pregunta cuando showAnswer es false -->
                <div x-show="!showAnswer">
                    <h2>{{ $currentCard->pregunta }}</h2>
                </div>
                <!-- Muestra la respuesta cuando showAnswer es true -->
                <div x-show="showAnswer">
                    <h2>{{ $currentCard->respuesta }}</h2>
                </div>
            </div>
            <button
                x-on:click="(function(){
                    // Si ya se está mostrando la respuesta y hay un temporizador activo, lo cancelamos y avanzamos inmediatamente
                    if (showAnswer && timerId !== null) {
                        clearTimeout(timerId);
                        timerId = null;
                        $wire.nextCard();
                        showAnswer = false;
                    } else if (!showAnswer) {
                        // Si la respuesta no se está mostrando, la mostramos y programamos el temporizador
                        showAnswer = true;
                        let delay = 15000;
                        timerId = setTimeout(() => {
                            $wire.nextCard();
                            showAnswer = false;
                            timerId = null;
                        }, delay);
                    }
                })()">
                Siguiente Flashcard
            </button>
        @else
            <p>No se encontró la flashcard actual.</p>
        @endif
    @else
        <p>No hay flashcards seleccionadas para el juego.</p>
    @endif
</div>

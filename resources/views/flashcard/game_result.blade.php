<x-main-layout title="Resultados">
    <div class="max-w-md mx-auto p-6 bg-white shadow rounded container-ask">
        <h2 class="text-xl font-bold mb-4 primary-color title-ask-container ">Resultados del juego</h2>
        <hr>
        <div class="results-content">
        <p class="mb-2">Correctas: 
            <strong>{{ $results->correct }}</strong>
        </p>
        <p class="mb-2">Incorrectas: <strong>{{ $results->incorrect }}</strong></p>
        <p class="mb-2">Total: <strong>{{ $results->total }}</strong></p>
        </div>
        <div class="mt-4">
            <a href="{{ route('flashcard.index') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 boton-success-m">
                Volver a Flashcards
            </a>
        </div>
    </div>
</x-main-layout>

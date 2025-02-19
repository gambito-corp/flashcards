<x-main-layout title="Creacion de Preguntas">
    <!-- Enlace para regresar a la página anterior -->
    <div class="mb-4">
        <a href="{{ route('preguntas.index') }}" class="inline-flex items-center text-blue-500 hover:text-blue-700">
            <svg class="h-6 w-6 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Regresar
        </a>
    </div>
    <livewire:preguntas.create-question-modal />
</x-main-layout>

<div class="max-w-4xl mx-auto p-6 space-y-8">
    <!-- Mensaje de sesión -->
    @if (session()->has('message'))
        <div class="p-4 bg-green-100 text-green-700 rounded shadow">
            {{ session('message') }}
        </div>
    @endif

    <!-- Sección para crear una nueva categoría -->
    <div class="bg-white p-6 rounded shadow">
        <h2 class="text-2xl font-semibold mb-4">Crear Categoría</h2>
        <form wire:submit.prevent="createCategory">
            <div class="mb-4">
                <label for="categoryName" class="block text-gray-700">Nombre de la Categoría</label>
                <input type="text" id="categoryName" wire:model="categoryName" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                @error('categoryName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Crear Categoría</button>
        </form>
    </div>

    <hr class="border-gray-300">

    <!-- Formulario de creación de flashcard -->
    <div class="bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-semibold mb-4">Crear Flashcard</h1>
        <form wire:submit.prevent="createCard">
            <div class="mb-4">
                <label for="pregunta" class="block text-gray-700">Pregunta <span class="text-red-500">*</span></label>
                <input type="text" id="pregunta" wire:model="pregunta" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                @error('pregunta') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <label for="respuesta" class="block text-gray-700">Respuesta <span class="text-red-500">*</span></label>
                <input type="text" id="respuesta" wire:model="respuesta" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                @error('respuesta') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <!-- Campos opcionales -->
            <div class="mb-4">
                <label for="url" class="block text-gray-700">URL</label>
                <input type="text" id="url" wire:model="url" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                @error('url') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <label for="imagen" class="block text-gray-700">Imagen (URL o ruta)</label>
                <input type="text" id="imagen" wire:model="imagen" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                @error('imagen') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <label for="url_respuesta" class="block text-gray-700">URL Respuesta</label>
                <input type="text" id="url_respuesta" wire:model="url_respuesta" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                @error('url_respuesta') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <label for="imagen_respuesta" class="block text-gray-700">Imagen Respuesta (URL o ruta)</label>
                <input type="text" id="imagen_respuesta" wire:model="imagen_respuesta" class="mt-1 block w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                @error('imagen_respuesta') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <!-- Sección opcional para asignar categorías a la flashcard -->
            <div class="mb-4">
                <label class="block text-gray-700">Categorías (opcional)</label>
                <div class="mt-2 space-x-4">
                    @foreach ($availableCategories as $category)
                        <label class="inline-flex items-center">
                            <input type="checkbox" wire:model="selectedCategories" value="{{ $category->id }}" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200">
                            <span class="ml-2">{{ $category->nombre }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700">Crear Flashcard</button>
        </form>
    </div>

    <hr class="border-gray-300">
{{--SELECCION DE TARJETAS--}}
    <div x-data="{ activeTab: 'sin-categoria' }" class="bg-white p-6 rounded shadow">
        <h1 class="text-2xl font-semibold mb-4">Seleccionar Flashcards para el juego</h1>
        <form wire:submit.prevent="startGame">
            <!-- Pestañas de categorías -->
            <div class="border-b border-gray-200 mb-4">
                <nav class="-mb-px flex space-x-4" aria-label="Tabs">
                    <button
                        type="button"
                        @click="activeTab = 'sin-categoria'"
                        :class="activeTab === 'sin-categoria' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                        Sin Categoría
                    </button>
                    @foreach($availableCategories as $cat)
                        <button
                            type="button"
                            @click="activeTab = 'cat-{{ $cat->id }}'"
                            :class="activeTab === 'cat-{{ $cat->id }}' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                            {{ $cat->nombre }}
                        </button>
                    @endforeach
                </nav>
            </div>

            <!-- Contenido de las pestañas -->
            <div class="mt-6">
                @php
                    $cardsWithoutCategory = $cards->filter(fn($card) => $card->categories->isEmpty());
                @endphp

                    <!-- Pestaña Sin Categoría -->
                <div x-show="activeTab === 'sin-categoria'">
                    @if($cardsWithoutCategory->count())
                        <!-- Contenedor del slider de "Sin Categoría" -->
                        <div x-data class="relative">
                            <div
                                class="flex space-x-4 overflow-x-auto py-2 px-16"
                                x-ref="slider"
                            >
                                @foreach($cardsWithoutCategory as $card)
                                    <div
                                        wire:click="toggleCard({{ $card->id }})"
                                        class="flex-shrink-0 w-64 p-4 border rounded shadow cursor-pointer transition duration-200 ease-in-out hover:shadow-lg
                                        {{ in_array($card->id, $selectedCards) ? 'bg-green-100 border-green-400' : 'bg-white' }}"
                                    >
                                        <h2 class="font-bold text-lg">{{ $card->pregunta }}</h2>
                                        <p class="text-sm text-gray-600 mt-1">{{ $card->respuesta }}</p>
                                    </div>
                                @endforeach
                            </div>
                            <!-- Flecha izquierda -->
                            <button
                                type="button"
                                class="absolute left-2 top-1/2 -translate-y-1/2 w-9 h-9 bg-gray-200 rounded-full hover:bg-gray-300 shadow z-10 flex items-center justify-center"
                                @click="$refs.slider.scrollBy({ left: -300, behavior: 'smooth' })"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <!-- Flecha derecha -->
                            <button
                                type="button"
                                class="absolute right-2 top-1/2 -translate-y-1/2 w-9 h-9 bg-gray-200 rounded-full hover:bg-gray-300 shadow z-10 flex items-center justify-center"
                                @click="$refs.slider.scrollBy({ left: 300, behavior: 'smooth' })"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    @else
                        <p class="text-gray-600">No hay flashcards sin categoría.</p>
                    @endif
                </div>

                <!-- Pestañas por categoría -->
                @foreach($availableCategories as $cat)
                    @php
                        $cardsInCategory = $cards->filter(function($card) use ($cat) {
                            return $card->categories->contains($cat);
                        });
                    @endphp
                    <div x-show="activeTab === 'cat-{{ $cat->id }}'">
                        @if($cardsInCategory->count())
                            <div x-data class="relative">
                                <div class="flex space-x-4 overflow-x-auto py-2 px-16" x-ref="slider">
                                    @foreach($cardsInCategory as $card)
                                        <div
                                            wire:click="toggleCard({{ $card->id }})"
                                            class="flex-shrink-0 w-64 p-4 border rounded shadow cursor-pointer transition duration-200 ease-in-out hover:shadow-lg {{ in_array($card->id, $selectedCards) ? 'bg-green-100 border-green-400' : 'bg-white' }}">
                                            <h2 class="font-bold text-lg">{{ $card->pregunta }}</h2>
                                            <p class="text-sm text-gray-600 mt-1">{{ $card->respuesta }}</p>
                                        </div>
                                    @endforeach
                                </div>
                                <!-- Flecha izquierda -->
                                <button type="button" class="absolute left-0 top-1/2 transform -translate-y-1/2 p-2 bg-gray-200 rounded-full hover:bg-gray-300"
                                        @click="$refs.slider.scrollBy({ left: -300, behavior: 'smooth' })">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                    </svg>
                                </button>
                                <!-- Flecha derecha -->
                                <button type="button" class="absolute right-0 top-1/2 transform -translate-y-1/2 p-2 bg-gray-200 rounded-full hover:bg-gray-300"
                                        @click="$refs.slider.scrollBy({ left: 300, behavior: 'smooth' })">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </div>
                        @else
                            <p class="text-gray-600">No hay flashcards en esta categoría.</p>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- Botón para iniciar el juego -->
            <div class="mt-4">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Iniciar Juego
                </button>
            </div>
        </form>
    </div>
</div>

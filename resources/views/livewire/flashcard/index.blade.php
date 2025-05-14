<div class="max-w-4xl mx-auto p-6 space-y-8 container-full">
    <!-- Mensaje de sesión -->
    @if (session()->has('message'))
        <div class="p-4 bg-green-100 text-green-700 rounded shadow">
            {{ session('message') }}
        </div>
    @endif

    <!-- Sección para crear una nueva categoría -->
    <div class="bg-white rounded container-askt">
        <h2 class="text-2xl font-semibold mb-4 primary-color title-ask-container">Crear Categoría</h2>
        <hr>
        <form wire:submit.prevent="createCategory">

            <div class="mb-4">
                <label for="categoryName" class="block text-gray-700">Nombre de la Categoría</label>
                <div class="group-formt">
                    <input type="text" id="categoryName" wire:model="categoryName"
                           class="mt-1 block w-full rounded border-gray-300  focus:border-[#195b81] focus:ring-[#195b81]  ">
                    @error('categoryName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 boton-success-m">Crear
                        Categoría
                    </button>
                </div>
            </div>


        </form>
    </div>

    <hr class="border-gray-300">

    <!-- Formulario de creación de flashcard -->
    <div class="bg-white p-6 rounded container-askt">
        <h1 class="text-2xl font-semibold mb-4 primary-color title-ask-container">Crear Flashcard</h1>
        <hr>
        <form wire:submit.prevent="createCard" class="form-container-ask">

            <div class="mb-4">
                <label for="pregunta" class="block text-gray-700">Pregunta <span class="text-red-500">*</span></label>
                <textarea
                    id="pregunta"
                    wire:model="pregunta"
                    class="mt-1 block w-full rounded border-gray-300 focus:border-[#195b81] focus:ring-[#195b81] "
                    rows="1"></textarea>
                @error('pregunta') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <label for="respuesta" class="block text-gray-700">Respuesta <span class="text-red-500">*</span></label>
                <textarea
                    id="respuesta"
                    wire:model="respuesta"
                    class="mt-1 block w-full rounded border-gray-300 focus:border-[#195b81] focus:ring-[#195b81] "
                    rows="1"></textarea>
                {{--                <input type="text" id="respuesta" wire:model="respuesta"--}}
                {{--                       class="mt-1 block w-full rounded border-gray-300 focus:border-[#195b81] focus:ring-[#195b81] ">--}}
                @error('respuesta') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Campos opcionales -->
            <div class="group-form">
                <div class="mb-4">
                    <label for="url" class="block text-gray-700">URL</label>
                    <input type="text" id="url" wire:model="url"
                           class="mt-1 block w-full rounded border-gray-300  focus:border-[#195b81] focus:ring-[#195b81] ">
                    @error('url') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label for="imagen" class="block text-gray-700">Imagen</label>
                    <input type="file" id="imagen" wire:model="imagen" accept="image/*"
                           class=" w-full text-sm text-grey-500 file:mr-5 file:py-2 file:px-6 file:rounded-full file:border-0 file:text-sm file:font-medium file:bg-blue-80 file:sky-950 hover:file:cursor-pointer">
                    @error('imagen') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="group-form">
                <div class="mb-4">
                    <label for="url_respuesta" class="block text-gray-700">URL Respuesta</label>
                    <input type="text" id="url_respuesta" wire:model="url_respuesta"
                           class="focus:border-[#195b81] focus:ring-[#195b81]  mt-1 block w-full rounded border-gray-300  ">
                    @error('url_respuesta') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label for="imagen_respuesta" class="block text-gray-700">Imagen Respuesta</label>
                    <input type="file" id="imagen_respuesta" wire:model="imagen_respuesta" accept="image/*"
                           class=" w-full text-sm text-grey-500 file:mr-5 file:py-2 file:px-6 file:rounded-full file:border-0 file:text-sm file:font-medium file:bg-blue-80 file:sky-950 hover:file:cursor-pointer ">
                    @error('imagen_respuesta') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
            <!-- Sección opcional para asignar categorías a la flashcard -->
            <div class="mb-4">
                <label class="block text-gray-700">Categorías (opcional)</label>
                <div class="mt-2 space-x-4">
                    @foreach ($availableCategories as $category)
                        <label class="inline-flex items-center">
                            <input type="checkbox" wire:model="selectedCategories" value="{{ $category->id }}"
                                   class="focus:border-[#195b81] focus:ring-[#195b81]  rounded border-gray-300 text-indigo-600  checkbox-form ">
                            <span class="ml-2">{{ $category->nombre }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 boton-success-m">Crear
                Flashcard
            </button>
        </form>
    </div>

    <hr class="border-gray-300">
    {{--SELECCION DE TARJETAS--}}

    <div class="">
        <!-- Botón seleccionar todas -->
        <div class="flex items-center justify-between mb-2">
            <span></span>
            <button type="button"
                    wire:click="toggleSelectAll"
                    class="px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700">
                {{ count($selectedCards) === $cards->count() ? 'Deseleccionar todas' : 'Seleccionar todas' }}
            </button>
        </div>

        <!-- Tabs -->
        <div class="border-b box-cat-flash">
            <nav class="-mb-px flex space-x-4" aria-label="Tabs">
                <button type="button"
                        wire:click="setActiveTab('sin-categoria')"
                        class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm
                    {{ $activeTab === 'sin-categoria' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Sin Categoría
                </button>
                @foreach($availableCategories as $cat)
                    <button type="button"
                            wire:click="setActiveTab('cat-{{ $cat->id }}')"
                            class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm
                        {{ $activeTab === 'cat-'.$cat->id ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        {{ $cat->nombre }}
                    </button>
                @endforeach
            </nav>
        </div>

        <!-- Contenido pestañas (solo se muestra la activa) -->
        <div class="mt-6">
            {{-- SIN CATEGORÍA --}}
            @if($activeTab === 'sin-categoria')
                @include('livewire.flashcard.slider', [
                    'tabId' => 'sin-categoria',
                    'cardsToShow' => $filteredTabs['sin-categoria'],
                    'searchTerm' => $searchTerms['sin-categoria'] ?? '',
                    'selectedCards' => $selectedCards,
                    'slidersScroll' => $slidersScroll
                ])
            @endif

            {{-- POR CATEGORÍA --}}
            @foreach($availableCategories as $cat)
                @php $catTab = 'cat-' . $cat->id; @endphp
                @if($activeTab === $catTab)
                    @include('livewire.flashcard.slider', [
                        'tabId' => $catTab,
                        'cardsToShow' => $filteredTabs[$catTab],
                        'searchTerm' => $searchTerms[$catTab] ?? '',
                        'selectedCards' => $selectedCards,
                        'slidersScroll' => $slidersScroll
                    ])
                @endif
            @endforeach
        </div>
    </div>
</div>

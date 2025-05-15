<form method="POST" action="{{ route('examenes.ia') }}">
    @csrf
    <div class="mb-8">
        <div class="bg-white p-6 rounded container-askt mb-8">
            <h1 class="text-2xl font-semibold mb-4 primary-color title-ask-container">Examen IA</h1>
            <hr>

            <!-- Carrusel de Áreas -->
            <div class="carousel-container overflow-x-auto mb-5 relative">
                <ul class="carousel-list flex gap-4 overflow-x-auto scroll-smooth scroll">
                    @foreach ($areas as $area)
                        <li
                            title="{{ $area->description }}"
                            class="carousel-item min-w-fit cursor-pointer pb-4 pt-4 pl-6 pr-6 rounded-[5px] text-sm flex
                            justify-center items-center font-medium text-center duration-300 ease-in
                            {{ isset($selectedArea) && $selectedArea->id === $area->id
                                ? 'font-semibold bg-[#195b81] text-white'
                                : 'bg-[#f7f7f7] border-transparent text-gray-600 hover:text-white hover:bg-[#195b81]' }}"
                            wire:click="selectArea({{$area->id}})">
                            {{ $area->name }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Carrusel de Categorías -->
            <div class="carousel-container overflow-x-auto mb-5 relative">
                <ul class="carousel-list flex space-x-4 overflow-x-auto scroll-smooth">
                    @foreach ($categories as $category)
                        <li
                            title="{{ $category->description }}"
                            class="carousel-item min-w-fit cursor-pointer pb-4 pt-4 pl-6 pr-6 rounded-[5px] text-sm flex justify-center
                            items-center font-medium text-center duration-300 ease-in
                            {{ isset($selectedCategory) && $selectedCategory->id === $category->id
                                ? 'font-semibold bg-[#157b80] text-white'
                                : 'bg-[#f7f7f7] border-transparent text-gray-600 hover:text-white hover:bg-[#157b80]' }}"
                            wire:click="selectCategory({{$category->id}})">
                            {{ $category->name }}
                        </li>
                    @endforeach
                </ul>
            </div>

            <!-- Carrusel de Tipos -->
            <div class="carousel-container overflow-x-auto mb-5 relative">
                <ul class="carousel-list flex space-x-4 overflow-x-auto scroll-smooth">
                    @if(count($categories) > 0)
                        @foreach ($tipos as $tipo)
                            <li
                                class="carousel-item min-w-fit cursor-pointer pb-4 pt-4 pl-6 pr-6 rounded-[5px] text-sm flex justify-center items-center font-medium text-center duration-300 ease-in
                                {{ isset($selectedTipo) && $selectedTipo->id === $tipo->id
                                    ? 'font-semibold bg-[#5b8080] text-white'
                                    : 'bg-[#f7f7f7] border-transparent text-gray-600 hover:text-white hover:bg-[#5b8080]' }}"
                                wire:click="selectTipo({{$tipo->id}})">
                                {{ $tipo->name }}
                            </li>
                        @endforeach
                    @endif
                </ul>
            </div>

            <!-- Inputs para tema, dificultad, preguntas -->
            <div class="mt-10 form-container-ask">
                <label for="tema" class="block text-gray-700 text-sm font-bold mb-2">
                    Tema (puedes escribir libremente)
                </label>
                <input type="text"
                       id="tema"
                       wire:model="tema"
                       class="mt-1 block w-full rounded border-gray-300 focus:border-[#195b81] focus:ring-[#195b81]"
                       placeholder="Ej: Sistema Nervioso, Historia, Matemáticas...">
                @error('tema')
                <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mt-4 form-container-ask">
                <label for="dificultad" class="block text-gray-700 text-sm font-bold mb-2">
                    Dificultad
                </label>
                <select id="dificultad" wire:model="dificultad"
                        class="mt-1 block w-full rounded border-gray-300 focus:border-[#195b81] focus:ring-[#195b81]">
                    <option value="basica">Básica</option>
                    <option value="media">Media</option>
                    <option value="avanzada">Avanzada</option>
                </select>
            </div>

            <div class="mt-4 form-container-ask">
                <label for="questionCount" class="block text-gray-700 text-sm font-bold mb-2">
                    Número de preguntas a generar
                </label>
                <input type="number"
                       id="questionCount"
                       wire:model="numPreguntas"
                       min="1"
                       max="200"
                       class="mt-1 block w-full rounded border-gray-300 focus:border-[#195b81] focus:ring-[#195b81]"
                       placeholder="Ingrese el número de preguntas">
                @error('numPreguntas')
                <span class="text-red-600 text-sm">{{ $message }}</span>
                @enderror
            </div>

            <div class="mt-4">
                <button type="button"
                        wire:click="addCombination"
                        class="text-white rounded boton-success-m button-c2"
                        wire:loading.attr="disabled">
                    <span wire:loading.remove>Agregar combinación</span>
                    <span wire:loading>Agregando...</span>
                </button>
            </div>

            <!-- Mensajes de error o éxito -->
            @if(session()->has('error'))
                <div class="mt-2 text-red-600">
                    {{ session('error') }}
                </div>
            @endif
            @if(session()->has('success'))
                <div class="mt-2 text-green-600">
                    {{ session('success') }}
                </div>
            @endif

            <!-- Tabla/resumen de combinaciones -->
            @if(count($examCollection) > 0)
                <div class="mt-6">
                    <h2 class="font-bold mb-2">Combinaciones seleccionadas:</h2>
                    <ul>
                        @foreach($examCollection as $combo)
                            <li>
                                {{ $combo['area_name'] }} /
                                {{ $combo['category_name'] }} /
                                {{ $combo['tipo_name'] }} -
                                Tema: {{ $combo['tema'] }},
                                Dificultad: {{ ucfirst($combo['dificultad']) }},
                                Preguntas: {{ $combo['question_count'] }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <hr class="border-gray-300 my-6">

            <!-- Inputs para título y tiempo -->
            <div class="bg-white p-6 rounded container-askt">
                <div class="form-container-ask">
                    <label for="examTitle" class="block text-gray-700 text-sm font-bold mb-2">
                        Título del Examen
                    </label>
                    <input type="text"
                           name="examTitle"
                           wire:model="examTitle"
                           id="examTitle"
                           placeholder="Ingrese el título del examen"
                           class="block w-full px-4 py-2 border border-gray-300 rounded focus:border-[#195b81] focus:ring-[#195b81] ">
                    @error('examTitle')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>
                <div class="mt-6 form-container-ask">
                    <label for="examTime" class="block text-gray-700 text-sm font-bold mb-2">
                        Tiempo del Examen (minutos)
                    </label>
                    <input type="number"
                           name="examTime"
                           wire:model="examTime"
                           id="examTime"
                           min="1"
                           max="200"
                           placeholder="Ingrese el tiempo en minutos"
                           class="block w-full px-4 py-2 border border-gray-300 rounded focus:border-[#195b81] focus:ring-[#195b81] ">
                    @error('examTime')
                    <span class="text-red-600 text-sm">{{ $message }}</span>
                    @enderror
                </div>
            </div>

            <!-- Campo oculto con las combinaciones -->
            <input type="hidden" name="examCollection" value="{{ json_encode($examCollection) }}">

            <div class="mt-8">
                <button type="submit"
                        class="bg-[#157b80] font-semibold text-white px-9 py-2.5 text-base rounded ">
                    Generar Examen IA
                </button>
            </div>
        </div>
    </div>
</form>

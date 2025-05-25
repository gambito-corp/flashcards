<form method="POST" action="{{ route('examenes.ia') }}">
    @csrf
    <div class="mb-8">
        <div class="bg-white  rounded container-askt mb-8">
            <div class="flex mb-4 flex md:justify-around justify-start items-center flex-wrap">
                <h1 class="text-2xl font-semibold mb-4 primary-color title-ask-container md:mb-0 mb-2">Examen IA</h1>
                @if(!Auth::user()->hasAnyRole('root') && Auth::user()->status == 0)
                    <h3 class="flex justify-around items-center flex-wrap md:text-base text-[14px] px-5 md:px-0">Los
                        usuarios Fremium solo pueden solo seleecionar 10 preguntas. ¿Quieres preguntas ilimitadas?

                        <div
                            class="px-0 md:px-3 rounded-lg md:w-auto w-full">
                            <a href="{{route('planes')}}"
                               target="_blank"
                               class="pointer-events-auto px-6 py-4 md:px-6 md:py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-bold rounded-full shadow-lg  hover:scale-105 transition flex items-center justify-center text-[13px] md:tex-base mt-3 md:mt-0">
                                🔒 Hazte PRO
                            </a>
                        </div>
                    </h3>
                @endif

                <p class="px-5 text-[12px] bg-[#ffeaea] p-[7px] rounded-[5px] mt-[13px] text-[#3c3c3c]"><strong>Descargo
                        de responsabilidad:</strong>El contenido de estas preguntas ha sido generado por sistemas de
                    inteligencia artificial. Aunque se busca la mayor precisión posible, es imprescindible contrastar
                    las respuestas con bibliografía especializada. El uso de esta información es responsabilidad
                    exclusiva del usuario.</p>
            </div>
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
                <button type="submit" id="generar-ia"
                        class="bg-[#157b80] font-semibold text-white px-9 py-2.5 text-base rounded ">
                    Generar Examen IA
                </button>
            </div>

        </div>
    </div>

</form>



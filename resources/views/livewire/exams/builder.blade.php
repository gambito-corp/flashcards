<div class="">
    <h1 class="first-letter font-extrabold text-2xl">Examen</h1>
    <div class="overflow-x-auto">
        <ul class="flex space-x-4">
            @foreach ($areas as $area)
                <li
                    class="cursor-pointer px-4 py-2 border-b-2 {{ isset($selectedArea) && $selectedArea->id === $area->id
                        ? 'border-blue-500 text-blue-500'
                        : 'border-transparent text-gray-600 hover:text-blue-500' }}"
                    wire:click="getCategories({{ $area->id }})">
                    {{ $area->name }}
                </li>
            @endforeach
        </ul>
    </div>
    <div class="overflow-x-auto">
        <ul class="flex space-x-4">
            @foreach ($categories as $category)
                <li
                    class="cursor-pointer px-4 py-2 border-b-2 {{ isset($selectedCategory) && $selectedCategory->id === $category->id
                        ? 'border-blue-500 text-blue-500'
                        : 'border-transparent text-gray-600 hover:text-blue-500' }}"
                    wire:click="getTypes({{ $category->id }})">
                    {{ $category->name }}
                </li>
            @endforeach
        </ul>
    </div>
    <div class="overflow-x-auto">
        <ul class="flex space-x-4">
            @foreach ($tipos as $tipo)
                <li
                    class="cursor-pointer px-4 py-2 border-b-2 {{ isset($selectedTipo) && $selectedTipo->id === $tipo->id
                        ? 'border-blue-500 text-blue-500'
                        : 'border-transparent text-gray-600 hover:text-blue-500' }}"
                    wire:click="setTypes({{ $tipo->id }})">
                    {{ $tipo->name }}
                </li>
            @endforeach
        </ul>
    </div>
    <div class="mt-4">
        <label for="university" class="block text-gray-700 text-sm font-bold mb-2">
            Universidad
        </label>
        <select id="university" wire:model="selectedUniversity" wire:change="filterQuestions" class="block appearance-none w-full bg-white border border-gray-400 hover:border-gray-500 px-4 py-2 pr-8 rounded shadow leading-tight focus:outline-none focus:shadow-outline">
            <option value="">Todas las universidades</option>
            @foreach ($universities as $university)
                <option value="{{ $university->id }}">{{ $university->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Texto que muestra el total de preguntas disponibles -->
    <div class="mt-2">
        <p class="text-gray-700">
            Preguntas Disponibles:
            @if($selectedUniversity)
                {{ optional($questions->firstWhere('universidad_id', $selectedUniversity))->question_count ?? 0 }}
            @else
                {{ optional($questions->firstWhere('universidad_id', null))->question_count ?? 0 }}
            @endif
        </p>
    </div>

    @php
        // Se obtiene el conteo de preguntas disponibles según la selección actual.
        $availableCount = $selectedUniversity
            ? optional($questions->firstWhere('universidad_id', $selectedUniversity))->question_count
            : optional($questions->firstWhere('universidad_id', null))->question_count;
        // Si no se obtiene un count, se usa 200 por defecto.
        $maxQuestions = $availableCount ? $availableCount : 200;
    @endphp

    <div class="mt-4">
        <label for="questionCount" class="block text-gray-700 text-sm font-bold mb-2">
            Número de preguntas a utilizar
        </label>
        <input type="number"
               id="questionCount"
               wire:model="selectedQuestionCount"
               min="1"
               max="{{ $maxQuestions }}"
               class="block w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring"
               placeholder="Ingrese el número de preguntas">
        @error('selectedQuestionCount')
        <span class="text-red-600 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <!-- Botón para agregar la combinación -->
    <div class="mt-4">
        <button type="button" wire:click="addCombination" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Agregar Combinación
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

    <!-- Mostrar la colección de combinaciones agregadas -->
    <div class="mt-6">
        <h2 class="font-bold text-xl">Combinaciones Agregadas</h2>
        <ul>
            @foreach($examCollection as $exam)
                <li class="border-b border-gray-300 py-2">
                    Área: {{ $exam['area_name'] }},
                    Categoría: {{ $exam['category_name'] }},
                    Tipo: {{ $exam['tipo_name'] }},
                    Universidad: {{ $exam['university_id'] ? $exam['university_id'] : 'Todas' }},
                    Preguntas: {{ $exam['question_count'] }}
                </li>
            @endforeach
            <li class="border-b border-gray-300 py-2">
                Total de preguntas:
                @if(count($examCollection))
                    {{ collect($examCollection)->sum('question_count') }}
                @else
                    Agregar Preguntas
                @endif
            </li>
        </ul>
    </div>

    <!-- Campo para Título del Examen -->
    <div class="mt-4">
        <label for="examTitle" class="block text-gray-700 text-sm font-bold mb-2">
            Título del Examen
        </label>
        <input type="text"
               wire:model="examTitle"
               id="examTitle"
               name="examTitle"
               placeholder="Ingrese el título del examen"
               class="block w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring">
        @error('examTitle')
        <span class="text-red-600 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <!-- Campo para Tiempo del Examen -->
    <div class="mt-4">
        <label for="examTime" class="block text-gray-700 text-sm font-bold mb-2">
            Tiempo del Examen (minutos)
        </label>
        <input type="number"
               wire:model="examTime"
               id="examTime"
               name="examTime"
               min="1"
               placeholder="Ingrese el tiempo en minutos"
               class="block w-full px-4 py-2 border border-gray-300 rounded shadow-sm focus:outline-none focus:ring">
        @error('examTime')
        <span class="text-red-600 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <!-- Formulario para enviar la información mediante POST -->
    <form action="{{route('examenes.create')}}" method="POST">
        @csrf
        @method('POST')
        <input type="hidden" name="examCollection" value="{{ json_encode($examCollection) }}"/>
        <input type="hidden" name="examTitle" value="{{ $examTitle }}"/>
        <input type="hidden" name="examTime" value="{{ $examTime }}"/>
        @error('examCollection')
        <span class="text-red-600 text-sm">{{ $message }}</span>
        @enderror
        <!-- Botón para Realizar el Examen -->
        <div class="mt-4">
            <input type="submit" value="Realizar Examen"  class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded"/>
        </div>
    </form>
</div>

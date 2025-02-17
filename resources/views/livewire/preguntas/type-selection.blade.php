<div>
    <!-- Sección de selección anidada -->
    <div class="grid grid-cols-1 gap-4">
        <!-- Select de Carrera (Team) -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Carrera:</label>
            <select wire:model="selectedTeam" class="mt-1 block w-full border-gray-300 rounded">
                @foreach($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Select de Asignatura (Área) -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Asignatura:</label>
            <select wire:model="selectedArea" class="mt-1 block w-full border-gray-300 rounded">
                @foreach($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Select de Asignatura Específica (Categoría) -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Asignatura Específica:</label>
            <select wire:model="selectedCategory" class="mt-1 block w-full border-gray-300 rounded">
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Select de Tipo -->
        <div>
            <label class="block text-sm font-medium text-gray-700">Tipo:</label>
            <select wire:model="selectedTipo" class="mt-1 block w-full border-gray-300 rounded">
                @foreach($tipos as $tipo)
                    <option value="{{ $tipo->id }}">{{ $tipo->name }}</option>
                @endforeach
            </select>
        </div>

        <!-- Botón para agregar la combinación -->
        <div>
            <button wire:click="addSelection" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                Agregar Tipo
            </button>
        </div>
    </div>

    <!-- Lista de selecciones agregadas -->
    <div class="mt-4">
        <h3 class="text-lg font-bold">Selecciones:</h3>
        <div class="flex flex-wrap gap-2 mt-2">
            @foreach($selections as $index => $sel)
                <div class="flex items-center bg-gray-200 text-gray-800 rounded-full px-3 py-1">
                    <span>{{ $sel['team_name'] }} > {{ $sel['area_name'] }} > {{ $sel['category_name'] }} > {{ $sel['tipo_name'] }}</span>
                    <button wire:click="removeSelection({{ $index }})" class="ml-2 text-red-500 hover:text-red-700">
                        &times;
                    </button>
                </div>
            @endforeach
        </div>
    </div>
</div>

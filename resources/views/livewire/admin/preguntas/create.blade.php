<div class="max-w-5xl mx-auto bg-white p-6 rounded-lg shadow-lg border border-gray-200">
    <h1 class="text-2xl font-bold mb-4">Crear Nueva Pregunta</h1>

    @if (session()->has('message'))
        <div class="mb-4 p-2 bg-green-100 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="store">
        <!-- Enunciado -->
        <div class="mb-4">
            <label for="newContent" class="block text-sm font-medium text-gray-700">Enunciado de la pregunta:</label>
            <textarea wire:model="newContent" id="newContent" rows="4" class="mt-1 block w-full border-gray-300 rounded px-3 py-2 focus:outline-none" placeholder="Escribe el enunciado..."></textarea>
            @error('newContent') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Selección Anidada: Carrera > Área > Categoría > Tipo(s) -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Jerarquía</label>
            <!-- Carrera (Team) -->
            <div class="mb-2">
                <label for="team" class="block text-xs font-medium text-gray-600">Carrera (Team):</label>
                <select wire:model="selectedTeam" id="team" class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none">
                    <option value="" disabled>Seleccione una carrera</option>
                    @foreach($teams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </select>
                @error('selectedTeam') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <!-- Asignatura (Área) -->
            <div class="mb-2">
                <label for="area" class="block text-xs font-medium text-gray-600">Asignatura (Área):</label>
                <select wire:model="selectedArea" id="area" class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none">
                    <option value="" disabled>Seleccione una asignatura</option>
                    @foreach($areas as $area)
                        <option value="{{ $area->id }}">{{ $area->name }}</option>
                    @endforeach
                </select>
                @error('selectedArea') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <!-- Categoría -->
            <div class="mb-2">
                <label for="category" class="block text-xs font-medium text-gray-600">Categoría:</label>
                <select wire:model="selectedCategory" id="category" class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none">
                    <option value="" disabled>Seleccione una categoría</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('selectedCategory') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <!-- Tipo(s) (multiple select) -->
            <div class="mb-2">
                <label for="tipo" class="block text-xs font-medium text-gray-600">Tipo (pueden seleccionar varios):</label>
                <select wire:model="selectedTipo" id="tipo" multiple class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none">
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->name }}</option>
                    @endforeach
                </select>
                @error('selectedTipo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <!-- Botón para agregar selección -->
            <div class="mb-4">
                <button type="button" wire:click="addTipoSelection" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                    Agregar Selección
                </button>
            </div>
            <!-- Mostrar selecciones agregadas (resumen de IDs, por ejemplo) -->
            @if(!empty($addedSelections))
                <div class="mb-4">
                    <h3 class="font-semibold text-lg">Selecciones Agregadas:</h3>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach($addedSelections as $index => $sel)
                            <span class="bg-gray-200 text-gray-800 rounded-full px-3 py-1 text-sm flex items-center">
                    {{ $sel['team_name'] }} > {{ $sel['area_name'] }} > {{ $sel['category_name'] }} > {{ implode(', ', $sel['tipo_names']) }}
                    <button type="button" wire:click="removeTipoSelection({{ $index }})" class="ml-2 text-red-500">&times;</button>
                </span>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Universidades (checkbox group) -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Universidades (puedes seleccionar varias):</label>
            <div class="mt-1 flex flex-wrap gap-2">
                @foreach($universidades as $universidad)
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="selectedUniversidades" value="{{ $universidad->id }}" class="mr-2">
                        <span>{{ $universidad->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('selectedUniversidades') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Campos para Media -->
        <div class="mb-4">
            <label for="newMediaUrl" class="block text-sm font-medium text-gray-700">URL de YouTube:</label>
            <input type="text" wire:model="newMediaUrl" id="newMediaUrl" class="mt-1 block w-full border-gray-300 rounded px-3 py-2 focus:outline-none" placeholder="Ingresa la URL de YouTube">
            @error('newMediaUrl') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label for="newMediaIframe" class="block text-sm font-medium text-gray-700">Iframe:</label>
            <textarea wire:model="newMediaIframe" id="newMediaIframe" rows="3" class="mt-1 block w-full border-gray-300 rounded px-3 py-2 focus:outline-none" placeholder="Ingresa el iframe aquí..."></textarea>
            @error('newMediaIframe') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Explicación -->
        <div class="mb-4">
            <label for="newExplanation" class="block text-sm font-medium text-gray-700">Explicación (opcional):</label>
            <textarea wire:model="newExplanation" id="newExplanation" rows="3" class="mt-1 block w-full border-gray-300 rounded px-3 py-2 focus:outline-none" placeholder="Explica la respuesta correcta (si aplica)..."></textarea>
            @error('newExplanation') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Opciones para preguntas de tipo Multiple Choice -->
        @if($newQuestionType === 'multiple_choice')
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Opciones:</label>
                @foreach($newOptions as $index => $answer)
                    <div class="flex items-center mb-2">
                        <input type="radio" wire:model="newCorrectOption" value="{{ $index }}" class="mr-2" title="Marca la opción correcta">
                        <input type="text" wire:model="newOptions.{{ $index }}" class="flex-1 border rounded px-3 py-2 focus:outline-none" placeholder="Opción {{ $index + 1 }}">
                        <button type="button" wire:click="removeAnswer({{ $index }})" class="ml-2 text-red-500" title="Eliminar opción">
                            <i class="fa-solid fa-trash"></i>
                        </button>
                    </div>
                @endforeach
                <button type="button" wire:click="addAnswer" class="mt-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-1 px-2 rounded">
                    Agregar Opción
                </button>
            </div>
        @endif

        <!-- Botones de acción -->
        <div class="flex justify-end space-x-4 mt-4">
            <button type="button" wire:click="close" class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-bold py-2 px-4 rounded">
                Cancelar
            </button>
            <button type="button" wire:click="store" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                Guardar Pregunta
            </button>
        </div>
    </form>
</div>

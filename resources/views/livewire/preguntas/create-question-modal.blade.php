<div>
    <h2 class="text-2xl font-bold mb-4">Crear Nueva Pregunta</h2>
    <form wire:submit.prevent="store">
        <!-- Enunciado -->
        <div class="mb-4">
            <label for="newContent" class="block text-sm font-medium text-gray-700">Enunciado de la pregunta:</label>
            <textarea wire:model="newContent" id="newContent" rows="4" class="mt-1 block w-full border-gray-300 rounded" placeholder="Escribe el enunciado..."></textarea>
            @error('newContent') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <!-- (Opcional) Tipo de pregunta, comentado por defecto -->
        {{--
        <div class="mb-4">
            <label for="newQuestionType" class="block text-sm font-medium text-gray-700">Tipo de pregunta:</label>
            <select wire:model="newQuestionType" id="newQuestionType" class="mt-1 block w-full border-gray-300 rounded">
                <option value="multiple_choice">Multiple Choice</option>
                <option value="boolean">Verdadero/Falso</option>
                <option value="range">Rango</option>
            </select>
            @error('newQuestionType') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        --}}
        <!-- Selección anidada: Carrera, Asignatura, Categoría y Tipo(s) -->
        <div class="mb-4">
            <!-- Carrera (Team) -->
            <label class="block text-sm font-medium text-gray-700">Carrera (Team):</label>
            <select wire:model.live="selectedTeam" class="mt-1 block w-full border-gray-300 rounded">
                <option value="">Seleccione una carrera</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
            @error('selectedTeam') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <!-- Asignatura (Área) -->
            <label class="block text-sm font-medium text-gray-700">Asignatura (Área):</label>
            <select wire:model.live="selectedArea" class="mt-1 block w-full border-gray-300 rounded">
                <option value="">Seleccione una asignatura</option>
                @foreach($areas->where('team_id', $selectedTeam) as $area)
                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                @endforeach
            </select>
            @error('selectedArea') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <!-- Asignatura Específica (Categoría) -->
            <label class="block text-sm font-medium text-gray-700">Asignatura Específica (Categoría):</label>
            <select wire:model.live="selectedCategory" class="mt-1 block w-full border-gray-300 rounded">
                <option value="">Seleccione una categoría</option>
                @foreach($categories->where('area_id', $selectedArea) as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            @error('selectedCategory') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <!-- Tipos (pueden seleccionarse varios) -->
            <label class="block text-sm font-medium text-gray-700">Tipos (puedes seleccionar varios):</label>
            <select wire:model.live="selectedTipo" multiple class="mt-1 block w-full border-gray-300 rounded">
                @foreach($tipos->where('category_id', $selectedCategory) as $tipo)
                    <option value="{{ $tipo->id }}">{{ $tipo->name }}</option>
                @endforeach
            </select>
            @error('selectedTipo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <!-- Botón para agregar la selección anidada -->
        <div class="mb-4">
            <button type="button" wire:click="addTipoSelection" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                Agregar Selección
            </button>
        </div>
        <!-- Mostrar las selecciones agregadas -->
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
        <!-- Universidades (checkboxes) -->
        <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700">Universidades (puedes seleccionar varias):</label>
            <div class="mt-1 flex flex-wrap gap-2">
                @foreach($universidades as $universidad)
                    <label class="flex items-center">
                        <input type="checkbox" wire:model.live="selectedUniversidades" value="{{ $universidad->id }}" class="mr-2">
                        <span>{{ $universidad->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('selectedUniversidades') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <!-- NUEVOS INPUTS: URL de YouTube e Iframe -->
        <div class="mb-4">
            <label for="newMediaUrl" class="block text-sm font-medium text-gray-700">URL de YouTube:</label>
            <input type="text" wire:model="newMediaUrl" id="newMediaUrl" class="mt-1 block w-full border-gray-300 rounded" placeholder="Ingresa la URL de YouTube">
            @error('newMediaUrl') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4">
            <label for="newMediaIframe" class="block text-sm font-medium text-gray-700">Iframe:</label>
            <textarea wire:model="newMediaIframe" id="newMediaIframe" rows="3" class="mt-1 block w-full border-gray-300 rounded" placeholder="Ingresa el iframe aquí..."></textarea>
            @error('newMediaIframe') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <!-- Explicación -->
        <div class="mb-4">
            <label for="newExplanation" class="block text-sm font-medium text-gray-700">Explicación (opcional):</label>
            <textarea wire:model="newExplanation" id="newExplanation" rows="3" class="mt-1 block w-full border-gray-300 rounded" placeholder="Explica la respuesta correcta (si aplica)..."></textarea>
            @error('newExplanation') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <!-- Opciones para preguntas de tipo Multiple Choice -->
        @if($newQuestionType === 'multiple_choice')
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Opciones:</label>
                @foreach($newOptions as $index => $option)
                    <div class="flex items-center mb-2">
                        <input type="radio" wire:model="newCorrectOption" value="{{ $index }}" class="mr-2">
                        <input type="text" wire:model="newOptions.{{ $index }}" class="flex-1 border-gray-300 rounded p-1" placeholder="Opción {{ $index + 1 }}">
                        <button type="button" wire:click="removeNewOption({{ $index }})" class="ml-2 text-red-500">Eliminar</button>
                    </div>
                @endforeach
                <button type="button" wire:click="addNewOption" class="mt-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-1 px-2 rounded">
                    Agregar Opción
                </button>
            </div>
        @endif
    </form>
    <div class="flex justify-end space-x-4 mt-4">
        <button type="button" wire:click="$set('showModal', false)" class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-bold py-2 px-4 rounded">
            Cancelar
        </button>
        <button type="button" wire:click="store" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
            Guardar Pregunta
        </button>
    </div>
</div>

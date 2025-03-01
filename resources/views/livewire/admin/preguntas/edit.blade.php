<div class="max-w-5xl mx-auto bg-white p-6 rounded-lg shadow-lg border border-gray-200 container-ask">
    <h1 class="text-2xl font-bold mb-4 primary-color title-ask-container">Editar Pregunta</h1>
    <hr>

    @if (session()->has('message'))
        <div class="mb-4 p-2 bg-green-100 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="updateQuestion" class="form-container-ask">
        <!-- Enunciado -->
        <div class="mb-4">
            <label for="newContent" class="block text-sm font-medium text-gray-700">Enunciado de la pregunta:</label>
            <textarea wire:model.live="newContent" id="newContent" rows="4" class="mt-1 block w-full border-gray-300 rounded px-3 py-2 focus:outline-none" placeholder="Escribe el enunciado..."></textarea>
            @error('newContent') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Selección Anidada: Carrera > Área > Categoría > Tipo(s) -->
        <div class="mb-4 form-ct">
            <label class="block text-sm font-medium text-gray-700 mb-1">Jerarquía</label>
            <div class="group-3"> 
            <!-- Carrera (Team) -->
            <div class="mb-2">
                <label for="team" class="block text-xs font-medium text-gray-600">Carrera (Team):</label>
                <select wire:model.live="selectedTeam" id="team" class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none">
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
                <select wire:model.live="selectedArea" id="area" class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none">
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
                <select wire:model.live="selectedCategory" id="category" class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none">
                    <option value="" disabled>Seleccione una categoría</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('selectedCategory') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            </div>
            <!-- Tipo(s) (multiple select) -->
            <div class="mb-2">
                <label for="tipo" class="block text-xs font-medium text-gray-600">Tipo (pueden seleccionar varios):</label>
                <select wire:model.live="selectedTipo" id="tipo" multiple class="w-full border-gray-300 rounded px-3 py-2 focus:outline-none">
                    @foreach($tipos as $tipo)
                        <option value="{{ $tipo->id }}">{{ $tipo->name }}</option>
                    @endforeach
                </select>
                @error('selectedTipo') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
           
            <!-- Botón para agregar selección -->
            <div class="mb-4">
                <button type="button" wire:click="addTipoSelection" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded boton-success-m">
                    Agregar Selección
                </button>
            </div>
            <!-- Mostrar selecciones agregadas (resumen con nombres) -->
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
        <div class="mb-4 form-uni">
            <label class="block text-sm font-medium text-gray-700">Universidades (puedes seleccionar varias):</label>
            <div class="mt-1 flex flex-wrap gap-8">
                @foreach($universidades as $universidad)
                    <label class="flex items-center">
                        <input type="checkbox" wire:model.live="selectedUniversidades" value="{{ $universidad->id }}" class="mr-2 checkbox-form">
                        <span>{{ $universidad->name }}</span>
                    </label>
                @endforeach
            </div>
            @error('selectedUniversidades') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Campos para Media -->
        <div class="mb-4 form-ct">
            <label for="newMediaUrl" class="block text-sm font-medium text-gray-700">URL de YouTube:</label>
            <input type="text" wire:model.live="newMediaUrl" id="newMediaUrl" class="mt-1 block w-full border-gray-300 rounded px-3 py-2 focus:outline-none" placeholder="Ingresa la URL de YouTube">
            @error('newMediaUrl') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div class="mb-4 ">
            <label for="newMediaIframe" class="block text-sm font-medium text-gray-700">Iframe:</label>
            <textarea wire:model.live="newMediaIframe" id="newMediaIframe" rows="3" class="mt-1 block w-full border-gray-300 rounded px-3 py-2 focus:outline-none" placeholder="Ingresa el iframe aquí..."></textarea>
            @error('newMediaIframe') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Explicación -->
        <div class="mb-4 form-ct">
            <label for="newExplanation" class="block text-sm font-medium text-gray-700">Explicación (opcional):</label>
            <textarea wire:model="newExplanation" id="newExplanation" rows="3" class="mt-1 block w-full border-gray-300 rounded px-3 py-2 focus:outline-none" placeholder="Explica la respuesta correcta (si aplica)..."></textarea>
            @error('newExplanation') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>

        <!-- Opciones para preguntas de tipo Multiple Choice -->
        @if($questionType  === 'multiple_choice')
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700">Opciones:</label>
                @foreach($newOptions as $index => $answer)
                    <div class="flex items-center mb-2">
                        <input type="radio" wire:model="newCorrectOption" value="{{ $index }}" class="mr-2" title="Marca la opción correcta">
                        <input type="text" wire:model="newOptions.{{ $index }}" class="flex-1 border rounded px-3 py-2 focus:outline-none" placeholder="Opción {{ $index + 1 }}">
                        <button type="button" wire:click="removeAnswer({{ $index }})" class="ml-2 text-red-500" title="Eliminar opción">
                         <img class="delete-image" src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyNC4yLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0ic3ZnMiIgeG1sbnM6c3ZnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyINCgkgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCAxMjYuMiAxMjYuMiINCgkgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMTI2LjIgMTI2LjI7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiNGMDQ0NDQ7fQ0KCS5zdDF7ZmlsbDojRkZGRkZGO30NCjwvc3R5bGU+DQo8Zz4NCgk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMTA1LjMsMTI2LjJIMjAuOEM5LjMsMTI2LjIsMCwxMTYuOCwwLDEwNS4zVjIwLjhDMCw5LjMsOS4zLDAsMjAuOCwwaDg0LjVjMTEuNSwwLDIwLjgsOS4zLDIwLjgsMjAuOHY4NC41DQoJCUMxMjYuMiwxMTYuOCwxMTYuOCwxMjYuMiwxMDUuMywxMjYuMnoiLz4NCgk8Zz4NCgkJPHBhdGggY2xhc3M9InN0MSIgZD0iTTQ3LjcsOTIuM2MtMC42LTAuMi0xLjItMC4zLTEuOC0wLjZjLTEuOS0xLTMuMS0yLjUtMy4yLTQuN2MtMC4zLTQtMC40LTcuOS0wLjYtMTEuOQ0KCQkJYy0wLjMtNi41LTAuNi0xMy4xLTAuOS0xOS42Yy0wLjEtMS40LTAuMS0yLjgtMC4yLTQuM2MtMC4zLDAtMC43LDAtMSwwYy0xLjIsMC0yLTAuOC0yLTJjMC0yLjMsMC00LjUsMC02LjgNCgkJCWMwLTIuNywyLjItNC45LDQuOS00LjljMy40LDAsNi44LDAsMTAuMiwwYzAuMiwwLDAuMywwLDAuNiwwYzAtMC41LDAtMS4xLDAtMS42YzAtMy44LDIuNS02LjIsNi4yLTYuM2MyLjQsMCw0LjgsMCw3LjIsMA0KCQkJYzMuNiwwLDYuMSwyLjUsNi4xLDYuMWMwLDAuNSwwLDEuMSwwLDEuN2MwLjMsMCwwLjUsMCwwLjcsMGMzLjMsMCw2LjUsMCw5LjgsMGMzLjEsMCw1LjIsMi4xLDUuMiw1LjJjMCwyLjEsMCw0LjIsMCw2LjINCgkJCWMwLDEuNS0wLjcsMi4zLTIuMywyLjNjLTAuMiwwLTAuNCwwLTAuNywwYzAsMC44LTAuMSwxLjUtMC4xLDIuMmMtMC4zLDUuNS0wLjUsMTEtMC44LDE2LjZjLTAuMyw1LjctMC41LDExLjMtMC44LDE3DQoJCQljLTAuMiwyLjctMi4xLDQuNy00LjgsNS4zYy0wLjEsMC0wLjIsMC4xLTAuMywwLjFDNjguNiw5Mi4zLDU4LjIsOTIuMyw0Ny43LDkyLjN6IE00NC45LDUxLjJjMCwwLjYsMCwxLjEsMCwxLjYNCgkJCWMwLjMsNi41LDAuNiwxMy4xLDAuOSwxOS42YzAuMiw0LjYsMC40LDkuMiwwLjcsMTMuOGMwLjEsMS42LDAuOCwyLjIsMi40LDIuMmM5LjcsMCwxOS4zLDAsMjksMGMxLjYsMCwyLjMtMC42LDIuNC0yLjMNCgkJCWMwLjMtNS41LDAuNS0xMSwwLjgtMTYuNWMwLjItNC43LDAuNS05LjUsMC43LTE0LjJjMC4xLTEuNCwwLjEtMi44LDAuMi00LjJDNjkuNSw1MS4yLDU3LjIsNTEuMiw0NC45LDUxLjJ6IE04NC45LDQ3LjINCgkJCWMwLTEuNSwwLTIuOSwwLTQuNGMwLTEuMi0wLjMtMS41LTEuNS0xLjVjLTEzLjQsMC0yNi44LDAtNDAuMiwwYy0wLjEsMC0wLjEsMC0wLjIsMGMtMSwwLTEuMywwLjMtMS4zLDEuM2MwLDEuNCwwLDIuNywwLDQuMQ0KCQkJYzAsMC4yLDAsMC4zLDAsMC41QzU2LjIsNDcuMiw3MC41LDQ3LjIsODQuOSw0Ny4yeiBNNjkuMywzNy40YzAtMC43LDAtMS40LDAtMi4xYy0wLjEtMS0wLjktMS44LTEuOS0xLjhjLTIuNywwLTUuMywwLTgsMA0KCQkJYy0wLjksMC0xLjcsMC44LTEuOCwxLjdjLTAuMSwwLjcsMCwxLjUsMCwyLjJDNjEuNCwzNy40LDY1LjMsMzcuNCw2OS4zLDM3LjR6Ii8+DQoJCTxwYXRoIGNsYXNzPSJzdDEiIGQ9Ik01MS42LDY5LjdjMC00LjIsMC04LjQsMC0xMi42YzAtMS41LDEuMy0yLjUsMi43LTEuOWMwLjcsMC4zLDEuMiwwLjksMS4zLDEuN2MwLDAuMiwwLDAuNCwwLDAuNg0KCQkJYzAsOC4yLDAsMTYuNSwwLDI0LjdjMCwwLjMsMCwwLjUsMCwwLjhjLTAuMiwxLTEuMSwxLjctMi4xLDEuNmMtMS0wLjEtMS44LTAuOS0xLjgtMmMwLTIuNSwwLTUsMC03LjUNCgkJCUM1MS42LDczLjIsNTEuNiw3MS41LDUxLjYsNjkuN3oiLz4NCgkJPHBhdGggY2xhc3M9InN0MSIgZD0iTTY1LjMsNjkuN2MwLDQuMiwwLDguNCwwLDEyLjZjMCwxLjUtMS4zLDIuNS0yLjYsMmMtMC44LTAuMy0xLjMtMS0xLjMtMS45YzAtMS4zLDAtMi41LDAtMy44DQoJCQljMC03LjEsMC0xNC4zLDAtMjEuNGMwLTEuNSwxLjMtMi41LDIuNy0yYzAuOCwwLjMsMS4zLDEsMS4zLDJjMCwzLjYsMCw3LjIsMCwxMC44QzY1LjMsNjguNSw2NS4zLDY5LjEsNjUuMyw2OS43eiIvPg0KCQk8cGF0aCBjbGFzcz0ic3QxIiBkPSJNNzEuMiw2OS43YzAtNC4yLDAtOC40LDAtMTIuNmMwLTEuNSwxLjMtMi41LDIuNy0xLjljMC43LDAuMywxLjIsMC45LDEuMywxLjdjMCwwLjIsMCwwLjQsMCwwLjYNCgkJCWMwLDguMiwwLDE2LjUsMCwyNC43YzAsMC4zLDAsMC41LDAsMC44Yy0wLjIsMS0xLjEsMS43LTIuMSwxLjZjLTEtMC4xLTEuNy0wLjktMS44LTJjMC0yLjUsMC01LDAtNy41DQoJCQlDNzEuMiw3My4yLDcxLjIsNzEuNSw3MS4yLDY5Ljd6Ii8+DQoJPC9nPg0KPC9nPg0KPC9zdmc+DQo=">
                        </button>
                    </div>
                @endforeach
                <button type="button" wire:click="addAnswer" class="mt-2 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-1 px-2 rounded boton-success-m button-c2">
                    Agregar Opción
                </button>
            </div>
        @endif

        <!-- Botones de acción -->
        <div class="flex justify-end space-x-4 mt-4">
            <button type="button" wire:click="close" class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-bold py-2 px-4 rounded boton-success-m button-c3">
                Cancelar
            </button>
            <button type="button" wire:click="updateQuestion" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded boton-success-m">
                Guardar Pregunta
            </button>
        </div>
    </form>
</div>

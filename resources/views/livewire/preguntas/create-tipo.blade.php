<div>
    <h2 class="text-xl font-bold mb-4">Crear Tipo</h2>
    <form wire:submit.prevent="store">
        <!-- Select de Carrera (Team) -->
        <div class="mb-4">
            <label for="selectedTeam" class="block text-sm font-medium text-gray-700">Carrera:</label>
            <select wire:model.live="selectedTeam" id="selectedTeam" class="mt-1 block w-full border-gray-300 rounded">
                <option value="">Seleccione una Carrera</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
            @error('selectedTeam')
            <span class="text-red-500 text-xs">{{ $message }}</span>
            @enderror
        </div>

        <!-- Select de Asignatura (Área) -->
        <div class="mb-4">
            <label for="selectedArea" class="block text-sm font-medium text-gray-700">Asignatura:</label>
            <select wire:model.live="selectedArea" id="selectedArea" class="mt-1 block w-full border-gray-300 rounded">
                <option value="">Seleccione una Asignatura</option>
                @foreach($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                @endforeach
            </select>
            @error('selectedArea')
            <span class="text-red-500 text-xs">{{ $message }}</span>
            @enderror
        </div>

        <!-- Select de Categoría -->
        <div class="mb-4">
            <label for="selectedCategory" class="block text-sm font-medium text-gray-700">Categoría:</label>
            <select wire:model.live="selectedCategory" id="selectedCategory" class="mt-1 block w-full border-gray-300 rounded">
                <option value="">Seleccione una Categoría</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            @error('selectedCategory')
            <span class="text-red-500 text-xs">{{ $message }}</span>
            @enderror
        </div>

        <!-- Input para el nombre del Tipo -->
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Nombre del Tipo:</label>
            <input type="text" wire:model="name" id="name" placeholder="Ingresa el nombre del tipo" class="mt-1 block w-full border-gray-300 rounded">
            @error('name')
            <span class="text-red-500 text-xs">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
            Guardar Tipo
        </button>
    </form>

    @if(session()->has('message'))
        <div class="mt-4 text-green-600">
            {{ session('message') }}
        </div>
    @endif
</div>

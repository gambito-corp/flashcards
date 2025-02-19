<div>
    <h2 class="text-xl font-bold mb-4">Crear Categoría</h2>
    <form wire:submit.prevent="store">
        <!-- Select de Carrera -->
        <div class="mb-4">
            <label for="selectedTeam" class="block text-sm font-medium text-gray-700">Carrera:</label>
            <select wire:model.live="selectedTeam" id="selectedTeam" class="mt-1 block w-full border-gray-300 rounded">
                <option value="">Seleccione una Carrera</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
            @error('selectedTeam') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <!-- Select de Área -->
        <div class="mb-4">
            <label for="selectedArea" class="block text-sm font-medium text-gray-700">Área:</label>
            <select wire:model.live="selectedArea" id="selectedArea" class="mt-1 block w-full border-gray-300 rounded">
                <option value="">Seleccione un área</option>
                @foreach($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                @endforeach
            </select>
            @error('selectedArea') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <!-- Input para el nombre -->
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Nombre de Categoría:</label>
            <input type="text" wire:model="name" id="name" placeholder="Ingresa el nombre de la categoría" class="mt-1 block w-full border-gray-300 rounded">
            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <!-- Input para la descripción -->
        <div class="mb-4">
            <label for="description" class="block text-sm font-medium text-gray-700">Descripción (opcional):</label>
            <textarea wire:model="description" id="description" rows="3" placeholder="Ingresa una descripción..." class="mt-1 block w-full border-gray-300 rounded"></textarea>
            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
            Guardar Categoría
        </button>
    </form>
    @if(session()->has('message'))
        <div class="mt-4 text-green-600">
            {{ session('message') }}
        </div>
    @endif
</div>

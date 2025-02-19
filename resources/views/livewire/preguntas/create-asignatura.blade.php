<div>
    <h2 class="text-xl font-bold mb-4">Crear Asignatura</h2>
    <form wire:submit.prevent="store">
        <!-- Select de Carrera (Team) -->
        <div class="mb-4">
            <label for="selectedTeam" class="block text-sm font-medium text-gray-700">Carrera (Team):</label>
            <select wire:model="selectedTeam" id="selectedTeam" class="mt-1 block w-full border-gray-300 rounded">
                <option value="">Seleccione una carrera</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
            @error('selectedTeam') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <!-- Input para el nombre -->
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Nombre de Asignatura:</label>
            <input type="text" wire:model="name" id="name" placeholder="Ingresa el nombre de la asignatura" class="mt-1 block w-full border-gray-300 rounded">
            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <!-- Input para la descripción -->
        <div class="mb-4">
            <label for="description" class="block text-sm font-medium text-gray-700">Descripción (opcional):</label>
            <textarea wire:model="description" id="description" rows="3" placeholder="Ingresa una descripción..." class="mt-1 block w-full border-gray-300 rounded"></textarea>
            @error('description') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
            Guardar Asignatura
        </button>
    </form>
    @if(session()->has('message'))
        <div class="mt-4 text-green-600">
            {{ session('message') }}
        </div>
    @endif
</div>

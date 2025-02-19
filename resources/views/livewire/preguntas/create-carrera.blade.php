<div>
    <h2 class="text-xl font-bold mb-4">Crear Carrera</h2>
    <form wire:submit.prevent="store">
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Nombre de Carrera</label>
            <input type="text" id="name" wire:model="name" placeholder="Ingresa el nombre de la carrera" class="mt-1 block w-full border-gray-300 rounded">
            @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
            Guardar Carrera
        </button>
    </form>
    @if(session()->has('message'))
        <div class="mt-4 text-green-600">
            {{ session('message') }}
        </div>
    @endif
</div>

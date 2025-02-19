<div>
    <h2 class="text-xl font-bold mb-4">Crear Universidad</h2>
    <form wire:submit.prevent="store">
        <!-- Input para el nombre de la Universidad -->
        <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700">Nombre de la Universidad:</label>
            <input type="text" wire:model="name" id="name" placeholder="Ingresa el nombre de la universidad" class="mt-1 block w-full border-gray-300 rounded">
            @error('name')
            <span class="text-red-500 text-xs">{{ $message }}</span>
            @enderror
        </div>

        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
            Guardar Universidad
        </button>
    </form>

    @if(session()->has('message'))
        <div class="mt-4 text-green-600">
            {{ session('message') }}
        </div>
    @endif
</div>

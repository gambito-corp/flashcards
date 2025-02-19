<div>
    <h2 class="text-xl font-bold mb-4">Crear Tipo</h2>
    <form wire:submit.prevent="store">
        <!-- Select de Categoría -->
        <div class="mb-4">
            <label for="selectedCategory" class="block text-sm font-medium text-gray-700">Categoría:</label>
            <select wire:model="selectedCategory" id="selectedCategory" class="mt-1 block w-full border-gray-300 rounded">
                <option value="">Seleccione una categoría</option>
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

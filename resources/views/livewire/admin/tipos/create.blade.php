<div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow-lg border border-gray-200">
    <h1 class="text-2xl font-bold mb-4">Crear Tipo</h1>

    @if (session()->has('message'))
        <div class="mb-4 p-2 bg-green-100 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="store">
        <!-- Campo: Nombre del Tipo -->
        <div class="mb-4">
            <label for="name" class="block font-medium text-gray-700 mb-1">
                Nombre <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                id="name"
                wire:model.live ="tipo.name"
                class="w-full border rounded px-3 py-2 focus:outline-none"
                placeholder="Ingresa el nombre del tipo"
            />
            @error('tipo.name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Select anidado: Seleccionar Carrera (Team) -->
        <div class="mb-4">
            <label for="team" class="block font-medium text-gray-700 mb-1">
                Carrera <span class="text-red-500">*</span>
            </label>
            <select id="team" wire:model.live   ="selectedTeam" class="w-full border rounded px-3 py-2 focus:outline-none">
                <option value="" disabled>-- Selecciona una carrera --</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
            @error('selectedTeam') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Select anidado: Seleccionar Asignatura (Area) -->
        <div class="mb-4">
            <label for="area" class="block font-medium text-gray-700 mb-1">
                Asignatura <span class="text-red-500">*</span>
            </label>
            <select id="area" wire:model.live   ="selectedArea" class="w-full border rounded px-3 py-2 focus:outline-none">
                <option value="" disabled>-- Selecciona una asignatura --</option>
                @foreach($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                @endforeach
            </select>
            @error('selectedArea') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Select anidado: Seleccionar Categoría -->
        <div class="mb-4">
            <label for="category_id" class="block font-medium text-gray-700 mb-1">
                Categoría <span class="text-red-500">*</span>
            </label>
            <select id="category_id" wire:model.live    ="tipo.category_id" class="w-full border rounded px-3 py-2 focus:outline-none">
                <option value="" disabled>-- Selecciona una categoría --</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            @error('tipo.category_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="flex justify-end">
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded shadow">
                Crear Tipo
            </button>
        </div>
    </form>
</div>

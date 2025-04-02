<div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow-lg border border-gray-200 container-ask"">
    <h1 class="text-2xl font-bold mb-4 primary-color title-ask-container">Editar Tipo</h1>
    <hr>

    @if (session()->has('message'))
        <div class="mb-4 p-2 bg-green-100 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="update" class="form-container-ask">
        <!-- Campo: Nombre del Tipo -->
        <div class="mb-4">
            <label for="name" class="block font-medium text-gray-700 mb-1">
                Nombre <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                id="name"
                wire:model="name"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:border-[#195b81] focus:ring-[#195b81] "
                placeholder="Ingresa el nombre del tipo"
            />
            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Select anidado: Seleccionar Carrera (Team) -->
        <div class="mb-4">
            <label for="team" class="block font-medium text-gray-700 mb-1">
                Carrera <span class="text-red-500">*</span>
            </label>
            <select id="team" wire:model="selectedTeam" class="w-full border rounded px-3 py-2 focus:outline-none focus:border-[#195b81] focus:ring-[#195b81] ">
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
            <select id="area" wire:model="selectedArea" class="w-full border rounded px-3 py-2 focus:outline-none focus:border-[#195b81] focus:ring-[#195b81] ">
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
            <select id="category_id" wire:model="category_id" class="w-full border rounded px-3 py-2 focus:outline-none focus:border-[#195b81] focus:ring-[#195b81] ">
                <option value="" disabled>-- Selecciona una categoría --</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="flex ">
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded shadow boton-success-m">
                Actualizar Tipo
            </button>
        </div>
    </form>
</div>

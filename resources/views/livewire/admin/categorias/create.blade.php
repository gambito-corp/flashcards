<div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow-lg border border-gray-200 container-ask ">
    <h1 class="text-2xl font-bold mb-4 primary-color title-ask-container">Crear Categoría</h1>
    <hr>

    @if (session()->has('message'))
        <div class="mb-4 p-2 bg-green-100 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="store" class="form-container-ask">
        <!-- Nombre de Categoría -->
        <div class="mb-4">
            <label for="name" class="block font-medium text-gray-700 mb-1">
                Nombre <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                id="name"
                wire:model.live="category.name"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:border-[#195b81] focus:ring-[#195b81] "
                placeholder="Ingresa el nombre de la categoría"
            />
            @error('category.name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Select anidado: Primero Carrera (Team) -->
        <div class="mb-4">
            <label for="team_id" class="block font-medium text-gray-700 mb-1">
                Carrera (Team) <span class="text-red-500">*</span>
            </label>
            <select
                id="team_id"
                wire:model.live="category.team_id"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:border-[#195b81] focus:ring-[#195b81] "
            >
                <option value="" disabled>-- Selecciona una carrera --</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
            @error('category.team_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Select anidado: Luego Asignatura (Area) -->
        <div class="mb-4">
            <label for="area_id" class="block font-medium text-gray-700 mb-1">
                Asignatura <span class="text-red-500">*</span>
            </label>
            <select
                id="area_id"
                wire:model.live="category.area_id"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:border-[#195b81] focus:ring-[#195b81] "
            >
                <option value="" disabled>-- Selecciona una asignatura --</option>
                @foreach($areas as $area)
                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                @endforeach
            </select>
            @error('category.area_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Descripción (opcional) -->
        <div class="mb-4">
            <label for="description" class="block font-medium text-gray-700 mb-1">
                Descripción (opcional)
            </label>
            <textarea
                id="description"
                wire:model="category.description"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:border-[#195b81] focus:ring-[#195b81] "
                placeholder="Ingresa una descripción (opcional)"
            ></textarea>
        </div>

        <div class="flex no-bottom">
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded shadow boton-success-m">
                Crear Categoría
            </button>
        </div>
    </form>
</div>

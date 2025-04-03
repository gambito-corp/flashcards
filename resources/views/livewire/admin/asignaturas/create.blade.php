<div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow-lg border border-gray-200 container-ask">
    <h1 class="text-2xl font-bold mb-4 primary-color title-ask-container">Crear Asignatura</h1>
    <hr>

    @if (session()->has('message'))
        <div class="mb-4 p-2 bg-green-100 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="store" class="form-container-ask">
        <!-- Campo: Nombre de Asignatura -->
        <div class="mb-4">
            <label for="nombre" class="block font-medium text-gray-700 mb-1">
                Nombre <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                id="nombre"
                wire:model="asignatura.nombre"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:border-[#195b81] focus:ring-[#195b81] "
                placeholder="Ingresa el nombre de la asignatura"
            />
            @error('asignatura.nombre') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <!-- Campo: Seleccionar Team -->
        <div class="mb-4">
            <label for="team_id" class="block font-medium text-gray-700 mb-1">
                Carrera (Team) <span class="text-red-500">*</span>
            </label>
            <select
                id="team_id"
                wire:model="asignatura.team_id"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:border-[#195b81] focus:ring-[#195b81] "
            >
                <option value="" disabled>-- Selecciona una carrera --</option>
                @foreach($teams as $team)
                    <option value="{{ $team->id }}">{{ $team->name }}</option>
                @endforeach
            </select>
            @error('asignatura.team_id') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="flex  no-bottom">
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded shadow boton-success-m">
                Crear Asignatura
            </button>
        </div>
    </form>
</div>

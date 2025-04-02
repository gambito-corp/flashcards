<div class="max-w-xl mx-auto bg-white p-6 rounded-lg shadow-lg border border-gray-200 container-ask">
    <h1 class="text-2xl font-bold mb-4 primary-color title-ask-container">Editar Carrera</h1>

    @if (session()->has('message'))
        <div class="mb-4 p-2 bg-green-100 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="update" class="form-container-ask">
        <div class="mb-4">
            <label for="name" class="block font-medium text-gray-700 mb-1">
                Nombre <span class="text-red-500">*</span>
            </label>
            <input
                type="text"
                id="name"
                wire:model="name"
                class="w-full border rounded px-3 py-2 focus:outline-none focus:border-[#195b81] focus:ring-[#195b81] "
                placeholder="Ingresa el nombre de la carrera">
            @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
        </div>

        <div class="flex no-bottom">
            <button type="submit"
                    class="bg-blue-500 hover:bg-blue-600 text-white font-semibold px-4 py-2 rounded shadow boton-success-m">
                Actualizar Carrera
            </button>
        </div>
    </form>
</div>

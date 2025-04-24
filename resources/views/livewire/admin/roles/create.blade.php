<div class="m-4 p-4 bg-white rounded-lg shadow-lg border border-gray-200 container-ask">
    @if (session()->has('message'))
        <div class="mb-4 p-2 bg-green-100 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif
    <form wire:submit.prevent="save" class="form-container-ask">
        <h2 class="text-lg font-semibold mb-2 primary-color title-ask-container fz-15">Rol</h2>
        <hr>
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label for="name" class="block font-medium text-gray-700 mb-1">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    wire:model.live="name"
                    class="w-full border rounded px-3 py-2 focus:outline-none focus:border-[#195b81] focus:ring-[#195b81] "
                    placeholder="Ingresa el nombre del rol" />
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="guard_name" class="block font-medium text-gray-700 mb-1">
                    Guard <span class="text-red-500">*</span>
                </label>
                <select
                    wire:model.live="guard_name"
                    id="guard"
                    class="
                    w-full
                    h-full
                    border
                    rounded
                    px-3
                    py-2 ç
                    focus:outline-none
                    focus:border-[#195b81]
                    focus:ring-[#195b81]">
                    @forelse($guards as $guard)
                        <option value="{{$guard}}" @if($guard == $guard_name) selected @endif>{{$guard}}</option>
                    @empty
                        <option disabled>No hay asignaturas disponibles</option>
                    @endforelse
                </select>
                @error('guard_name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <!-- Botón de enviar -->
            <div class="flex ">
                <button
                    type="submit"
                    class="bg-teal-500 hover:bg-teal-600 text-white font-semibold px-6 py-2 boton-success-m rounded">
                    Crear Rol
                </button>
            </div>
        </div>
    </form>
</div>

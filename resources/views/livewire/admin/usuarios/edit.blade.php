<div class="m-4 p-4 bg-white rounded-lg shadow-lg border border-gray-200 container-ask">
    @if (session()->has('message'))
        <div class="mb-4 p-2 bg-green-100 text-green-700 rounded">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="updateUser" class="form-container-ask">
        <!-- SECCIÓN 1: Datos Básicos -->
        <h2 class="text-lg font-semibold mb-2 primary-color title-ask-container fz-15">Datos Básicos</h2>
        <hr>
        <div class="grid grid-cols-2 gap-4 mb-6">
            <div>
                <label for="name" class="block font-medium text-gray-700 mb-1">
                    Nombre <span class="text-red-500">*</span>
                </label>
                <input type="text" id="name" name="name" wire:model.live="name"
                       class="w-full border rounded px-3 py-2 focus:outline-none"
                       placeholder="Ingresa el nombre del usuario">
                @error('name') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div>
                <label for="email" class="block font-medium text-gray-700 mb-1">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" id="email" name="email" wire:model.live="email"
                       class="w-full border rounded px-3 py-2 focus:outline-none"
                       placeholder="Ingresa el correo electrónico">
                @error('email') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- SECCIÓN 2: Credenciales y Foto de Perfil -->
        <h2 class="text-lg font-semibold mb-2 primary-color title-ask-container fz-15">Credenciales y Foto</h2>
        <hr>
        <div class="grid gap-4 mb-6">
            <div class="mb-4">
                <label for="profile_photo" class="block font-medium text-gray-700 mb-1">
                    Foto de Perfil (opcional)
                </label>
                <input type="file" id="profile_photo" name="profile_photo" wire:model.live="profile_photo"
                       class="w-full text-sm text-grey-500 file:mr-5 file:py-2 file:px-6 file:rounded-full file:border-0 file:text-sm file:font-medium file:bg-blue-80 file:sky-950 hover:file:cursor-pointer" accept="image/*">
                @error('profile_photo') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror

                <!-- Previsualización: Si se selecciona una nueva foto, la muestra; de lo contrario, la foto actual -->
                <div class="mt-2">
                    @if ($profile_photo)
                        <!-- Previsualización de la nueva foto -->
                        <img src="{{ $profile_photo->temporaryUrl() }}" alt="Previsualización de la Foto de Perfil" class="w-20 h-20 rounded-full object-cover">
                    @elseif($usuario->profile_photo_path)
                        <!-- Foto actual almacenada en S3 -->
                        <img src="{{ Storage::disk('s3')->temporaryUrl($usuario->profile_photo_path, now()->addMinutes(10)) }}" alt="{{ $usuario->name }}" class="w-20 h-20 rounded-full object-cover">
                    @else
                        <!-- Imagen por defecto -->
                        <img src="{{$usuario->profile_photo_url}}" alt="Foto de Perfil" class="w-20 h-20 rounded-full object-cover">
                    @endif
                </div>
            </div>

        </div>

        <!-- SECCIÓN 3: Asignaciones -->
        <h2 class="text-lg font-semibold mb-2 primary-color title-ask-container fz-15">Asignaciones</h2>
        <hr>
        <div class="grid grid-cols-2 gap-4 mb-6">
            <!-- Roles (Checkbox Group) -->
            <div>
                <label class="block font-medium text-gray-700 mb-1">
                    Roles <span class="text-red-500">*</span>
                </label>
                <div class="flex flex-wrap gap-2">
                    @foreach($roles as $rol)
                        <label class="inline-flex items-center">
                            <input type="checkbox" value="{{ $rol->id }}" wire:model.defer="selectedRoles"
                                   class="form-checkbox text-green-500 chexbox-f">
                            <span class="ml-2">{{ $rol->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('selectedRoles') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <!-- Teams Asignados (Checkbox Group) -->
            <div>
                <label class="block font-medium text-gray-700 mb-1">
                    Teams Asignados <span class="text-red-500">*</span>
                </label>
                <div class="flex flex-wrap gap-2">
                    @foreach($teams as $team)
                        <label class="inline-flex items-center">
                            <input type="checkbox" value="{{ $team->id }}" wire:model.live="selectedTeams"
                                   class="form-checkbox text-green-500 chexbox-f">
                            <span class="ml-2">{{ $team->name }}</span>
                        </label>
                    @endforeach
                </div>
                @error('selectedTeams') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
        </div>

        <!-- SECCIÓN 4: Asignaturas -->
        <div class="mb-6">
            <label for="subjects" class="block font-medium text-gray-700 mb-1">
                Asignaturas <span class="text-red-500">*</span>
            </label>
            <div class="flex gap-4 m-25">
                <!-- Izquierda: Select Múltiple con asignaturas disponibles (excluyendo las ya agregadas) -->
                <div class="w-1/2 h-40">
                    <select id="subjectsLeft" name="availableSubjects[]" multiple wire:model="selectedToAdd"
                            class="w-full h-full border rounded px-3 py-2 focus:outline-none h-auto">
                        @forelse(collect($availableSubjects)->reject(fn($subject) => isset($selectedSubjects[$subject->id])) as $subject)
                            <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                        @empty
                            <option disabled>No hay asignaturas disponibles</option>
                        @endforelse
                    </select>
                </div>
                <!-- Centro: Botones verticales -->
                <div class="flex flex-col items-center justify-center gap-2 h-40 buttons-user">
                    <button type="button" wire:click="addSelected"
                            class="w-10 h-10 bg-green-500 hover:bg-green-600 rounded flex items-center justify-center text-white"
                            title="Agregar selección">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                    <button type="button" wire:click="addAll"
                            class="w-10 h-10 bg-green-500 hover:bg-green-600 rounded flex items-center justify-center text-white"
                            title="Agregar todas">
                        <i class="fa-solid fa-angles-right"></i>
                    </button>
                    <button type="button" wire:click="removeSelected"
                            class="w-10 h-10 bg-green-500 hover:bg-green-600 rounded flex items-center justify-center text-white"
                            title="Quitar selección">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button type="button" wire:click="removeAll"
                            class="w-10 h-10 bg-green-500 hover:bg-green-600 rounded flex items-center justify-center text-white"
                            title="Quitar todas">
                        <i class="fa-solid fa-angles-left"></i>
                    </button>
                </div>
                <!-- Derecha: Lista de asignaturas seleccionadas -->
                <div class="w-1/2 h-40">
                    <div class="w-full h-full  rounded px-3 py-2 bg-gray-100 overflow-y-auto">
                        <ul class="list-disc pl-5">
                            @forelse($selectedSubjects as $id => $subjectName)
                                <li class="mb-1">
                                    <label class="inline-flex items-center cursor-pointer {{ in_array($id, $deleteAsignature) ? 'bg-blue-500 text-white rounded px-1' : '' }}"
                                           wire:click="toggleDeleteSubject({{ $id }})">
                                        <span>{{ $subjectName }}</span>
                                    </label>
                                </li>
                            @empty
                                <li>No hay asignaturas seleccionadas</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
            <p class="text-sm text-gray-500 mt-1">
                Haz clic en una asignatura de la lista de la derecha para marcarla para eliminación, y luego presiona "Quitar selección".
            </p>
        </div>

        <!-- Botón de enviar -->
        <div class="flex ">
            <button type="submit"
                    class="bg-teal-500 hover:bg-teal-600 text-white font-semibold px-6 py-2 boton-success-m rounded">
                Actualizar Usuario
            </button>
        </div>
    </form>
</div>

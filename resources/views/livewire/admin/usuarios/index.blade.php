<div>
    <div class="max-w-7xl mx-auto bg-white p-6 rounded shadow">
        <!-- Contenedor del enlace -->
        <div class="flex items-center justify-end mb-4 mt gap-2">
            <!-- Enlace en lugar de botón -->
            <a href="{{route('admin.usuarios.create')}}"
               class="px-4 py-2 rounded text-white bg-green-500 hover:bg-green-600 flex items-center gap-2"
               title="Crear usuario"
            >
                <i class="fa-solid fa-plus"></i>
            </a>
        </div>

        <!-- Select de paginación e Input de búsqueda (50% cada uno) -->
        <div class="flex w-full mb-4 gap-2">
            <select class="w-1/2 border rounded px-2 py-1 focus:outline-none">
                <option value="10">10 por página</option>
                <option value="20">20 por página</option>
                <option value="50">50 por página</option>
                <option value="100">100 por página</option>
                <option value="all">Todos</option>
            </select>
            <input
                type="text"
                placeholder="Buscar..."
                class="w-1/2 border rounded px-2 py-1 focus:outline-none"
            />
        </div>

        <!-- Tabla principal -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <thead class="border-b text-gray-500">
                    <tr>
                        <th class="px-4 py-2">Nº</th>
                        <th class="px-4 py-2">Nombre</th>
                        <th class="px-4 py-2">Email</th>
                        <th class="px-4 py-2">Verificado Email</th>
                        <th class="px-4 py-2">Foto de Perfil</th>
                        <th class="px-4 py-2">Carreras Asignadas</th>
                        <th class="px-4 py-2">Asignaturas Asignadas</th>
                        <!-- Nueva columna Acciones -->
                        <th class="px-4 py-2">Acciones</th>
                    </tr>
                </thead>
                <tbody class="text-gray-700">
                @foreach($data as $item)
                    <tr class="border-b last:border-0">
                        <!-- Nº: número de orden usando $loop->iteration -->
                        <td class="px-4 py-2">{{ $loop->iteration }}</td>
                        <!-- Nombre -->
                        <td class="px-4 py-2">{{ $item->name }}</td>
                        <!-- Email -->
                        <td class="px-4 py-2">{{ $item->email }}</td>
                        <!-- Verificación de Email -->
                        <td class="px-4 py-2 text-center">
                            @if($item->email_verified_at)
                                <i class="fa-solid fa-check text-green-500"></i>
                            @else
                                <i class="fa-solid fa-xmark text-red-500"></i>
                            @endif
                        </td>
                        <!-- Foto de Perfil -->
                        <td class="px-4 py-2">
                            <img src="{{ $item->profile_photo_url }}" alt="Foto de Perfil" class="w-10 h-10 rounded-full">
                        </td>
                        <!-- Carreras Registradas -->
                        <td class="px-4 py-2">
                            {{ implode(', ', $item->teams->pluck('name')->toArray()) }}
                        </td>
                        <!-- Asignaturas Registradas -->
                        <td class="px-4 py-2">
                            @foreach($item->teams as $team)
                                <div class="mb-1">
                                    <strong>{{ $team->name }}:</strong>
                                    {{ $this->Asignaturas($team) }}
                                </div>
                            @endforeach
                        </td>
                        <!-- Columna Acciones -->
                        <td class="px-4 py-2 text-center">
                            <div class="inline-flex space-x-2">
                                <!-- Botón Editar -->
                                <a href="{{ route('admin.usuarios.edit', $item) }}"
                                   class="w-8 h-8 rounded flex items-center justify-center text-white bg-green-500 hover:bg-green-600"
                                   title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
{{--                                <!-- Botón Eliminar -->--}}
{{--                                <button--}}
{{--                                    class="w-8 h-8 rounded flex items-center justify-center text-white bg-red-500 hover:bg-red-600"--}}
{{--                                    title="Eliminar"--}}
{{--                                >--}}
{{--                                    <i class="fa-solid fa-trash"></i>--}}
{{--                                </button>--}}
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
        </table>
    </div>
</div>

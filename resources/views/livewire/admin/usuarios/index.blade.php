<div>
    <div class="max-w-7xl mx-auto bg-white p-6 rounded shadow container-ask">
        <!-- Contenedor del enlace -->
        <div class="flex items-center justify-end mb-4 mt gap-2">
            <!-- Enlace en lugar de botón -->
            <a href="{{route('admin.usuarios.create')}}"
               class="px-4 py-2 rounded text-white bg-green-500 hover:bg-green-600 flex items-center gap-2 button-add"
               title="Crear usuario"
            >
                <i class="fa-solid fa-plus"></i>
            </a>
        </div>

        <!-- Select de paginación e Input de búsqueda (50% cada uno) -->
        <div class="flex w-full mb-4 gap-2 form-container-ask form-table">
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
            <table class="w-full border-collapse table-users">
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
                            @if ($item->profile_photo_path)
                                <img src="{{ Storage::disk('s3')->temporaryUrl($item->profile_photo_path, now()->addMinutes(10)) }}" alt="Foto de Perfil" class="w-10 h-10 rounded-full">
                            @else
                                <img src="{{ $item->profile_photo_url }}" alt="Foto de Perfil" class="w-10 h-10 rounded-full">
                            @endif
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
                            <div class="inline-flex space-x-2 button-edit">
                                <!-- Botón Editar -->
                                <a href="{{ route('admin.usuarios.edit', $item) }}"
                                   class="w-8 h-8 rounded flex items-center justify-center text-white bg-green-500 hover:bg-green-600"
                                   title="Editar">
                                   <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyNC4yLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0ic3ZnMiIgeG1sbnM6c3ZnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyINCgkgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCAxMjYuMiAxMjYuMiINCgkgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMTI2LjIgMTI2LjI7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiNGRkZGRkY7fQ0KPC9zdHlsZT4NCjxnPg0KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0xMjYuMiwzMC4zYy0wLjEsNC44LTEuOSw4LjQtNSwxMS41Yy0xMSwxMC45LTIyLDIxLjgtMzMuMSwzMi43Yy0xNC4xLDE0LTI4LjMsMjcuOS00Mi40LDQyLjENCgkJYy0zLjgsMy44LTguMSw2LTEzLjUsNi41Yy04LjcsMC44LTE3LjMsMS45LTI2LDIuOGMtNC4xLDAuNS02LjctMi4xLTYuMi02LjJjMS4xLTkuNSwyLjEtMTksMy4yLTI4LjVjMC40LTMuOCwyLjItNyw0LjktOS43DQoJCWMxMy43LTEzLjYsMjcuMy0yNy4zLDQxLTQxQzYwLjgsMjguOSw3Mi41LDE3LjIsODQuMiw1LjVjNy4xLTcuMSwxNi4zLTcuMSwyMy40LDBjNC42LDQuNiw5LjIsOS4yLDEzLjgsMTMuOA0KCQlDMTI0LjYsMjIuNSwxMjYuMSwyNi4zLDEyNi4yLDMwLjN6IE00NS4zLDEwMi42YzE2LjktMTYuNywzMy44LTMzLjUsNTAuNi01MC4xQzg4LjUsNDUuMSw4MS4yLDM3LjcsNzQsMzAuNg0KCQlDNTcuMiw0Ny4zLDQwLjQsNjQuMiwyMy42LDgwLjlDMzAuNyw4OC4xLDM4LDk1LjMsNDUuMywxMDIuNnogTTEwMy42LDQ0LjhjMC4zLTAuMywwLjYtMC42LDAuOS0wLjljMy4zLTMuMyw2LjctNi42LDEwLTEwDQoJCWMxLjktMS45LDItNC45LDAuMS02LjhjLTUtNS4yLTEwLjItMTAuMy0xNS4zLTE1LjNjLTEuOC0xLjgtNC44LTEuOS02LjYtMC4yYy0zLjgsMy42LTcuNSw3LjQtMTEuMiwxMS4xDQoJCUM4OC45LDMwLjEsOTYuMiwzNy40LDEwMy42LDQ0Ljh6IE0zNy42LDExMC4xYy03LjMtNy4zLTE0LjUtMTQuNS0yMS43LTIxLjdjLTEuNiwxLjEtMi40LDIuNy0yLjYsNC42Yy0wLjYsNS42LTEuMywxMS4zLTEuOSwxNi45DQoJCWMtMC4yLDEuNy0wLjMsMy40LTAuNSw1LjJjNy41LTAuOSwxNC44LTEuNywyMi4xLTIuNUMzNSwxMTIuNCwzNi40LDExMS42LDM3LjYsMTEwLjF6Ii8+DQo8L2c+DQo8L3N2Zz4NCg==" />
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

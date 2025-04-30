<div>
    <div class="max-w-7xl mx-auto bg-white p-6 rounded shadow container-ask">
        <!-- Select de paginación e Input de búsqueda (50% cada uno) -->
        <div class="flex w-full mb-4 gap-2 form-container-ask form-table">
            <select wire:model.live="paginate" class="w-1/2 border rounded px-2 py-1 focus:outline-none">
                <option value="10" {{$this->paginate == 10 ? 'selected' : ''}}>10 por página</option>
                <option value="20" {{$this->paginate == 20 ? 'selected' : ''}}>20 por página</option>
                <option value="50" {{$this->paginate == 50 ? 'selected' : ''}}>50 por página</option>
                <option value="100" {{$this->paginate == 100 ? 'selected' : ''}}>100 por página</option>
            </select>
            <input
                type="text"
                wire:model.live="search"
                placeholder="Buscar..."
                class="w-1/2 border rounded px-2 py-1 focus:outline-none search-input"
            />
        </div>

        <!-- Tabla principal -->
        <div class="overflow-x-auto">
            <table class="w-full border-collapse table-users">
                <thead class="border-b text-gray-500">
                <tr>
                    <th class="px-4 py-2">Nº</th>
                    <th class="px-4 py-2">Clave</th>
                    <th class="px-4 py-2">Valor</th>
                    <th class="px-4 py-2">Estado</th>
                    <th class="px-4 py-2">Acciones</th>
                </tr>
                </thead>
                <tbody class="text-gray-700">
                @foreach($data as $item)
                    <tr class="border-b last:border-0">
                        <!-- Nº: número de orden usando $loop->iteration -->
                        <td class="px-4 py-2">{{ $loop->iteration }}</td>
                        <!-- Id de la clave -->
                        <td class="px-4 py-2">{{ $item->id }}</td>
                        <!-- Nombre -->
                        <td class="px-4 py-2">{{ $item->tipo }}</td>
                        <!-- Email -->
                        <td class="px-4 py-2">{{ $item->value }}</td>
                        <td class="px-4 py-2 text-center">
                            @if($item->status)
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64">
                                    <circle cx="32" cy="32" r="32" fill="#34D399"/>
                                    <text x="32" y="42" font-size="28" font-family="Arial" fill="white" text-anchor="middle" font-weight="bold">ON</text>
                                </svg>
                            @else
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64">
                                    <circle cx="32" cy="32" r="32" fill="#EF4444"/>
                                    <text x="32" y="42" font-size="28" font-family="Arial" fill="white" text-anchor="middle" font-weight="bold">OFF</text>
                                </svg>
                            @endif
                        </td>
                        <!-- Columna Acciones -->
                        <td class="px-4 py-2 text-center">
                            <div class="inline-flex space-x-2 button-edit">
                                <!-- Botón Editar -->
                                <a href="{{ route('admin.config.edit', $item) }}"
                                   class="w-8 h-8 rounded flex items-center justify-center text-white bg-green-500 hover:bg-green-600"
                                   title="Editar">
                                    <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyNC4yLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0ic3ZnMiIgeG1sbnM6c3ZnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyINCgkgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCAxMjYuMiAxMjYuMiINCgkgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMTI2LjIgMTI2LjI7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiNGRkZGRkY7fQ0KPC9zdHlsZT4NCjxnPg0KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0xMjYuMiwzMC4zYy0wLjEsNC44LTEuOSw4LjQtNSwxMS41Yy0xMSwxMC45LTIyLDIxLjgtMzMuMSwzMi43Yy0xNC4xLDE0LTI4LjMsMjcuOS00Mi40LDQyLjENCgkJYy0zLjgsMy44LTguMSw2LTEzLjUsNi41Yy04LjcsMC44LTE3LjMsMS45LTI2LDIuOGMtNC4xLDAuNS02LjctMi4xLTYuMi02LjJjMS4xLTkuNSwyLjEtMTksMy4yLTI4LjVjMC40LTMuOCwyLjItNyw0LjktOS43DQoJCWMxMy43LTEzLjYsMjcuMy0yNy4zLDQxLTQxQzYwLjgsMjguOSw3Mi41LDE3LjIsODQuMiw1LjVjNy4xLTcuMSwxNi4zLTcuMSwyMy40LDBjNC42LDQuNiw5LjIsOS4yLDEzLjgsMTMuOA0KCQlDMTI0LjYsMjIuNSwxMjYuMSwyNi4zLDEyNi4yLDMwLjN6IE00NS4zLDEwMi42YzE2LjktMTYuNywzMy44LTMzLjUsNTAuNi01MC4xQzg4LjUsNDUuMSw4MS4yLDM3LjcsNzQsMzAuNg0KCQlDNTcuMiw0Ny4zLDQwLjQsNjQuMiwyMy42LDgwLjlDMzAuNyw4OC4xLDM4LDk1LjMsNDUuMywxMDIuNnogTTEwMy42LDQ0LjhjMC4zLTAuMywwLjYtMC42LDAuOS0wLjljMy4zLTMuMyw2LjctNi42LDEwLTEwDQoJCWMxLjktMS45LDItNC45LDAuMS02LjhjLTUtNS4yLTEwLjItMTAuMy0xNS4zLTE1LjNjLTEuOC0xLjgtNC44LTEuOS02LjYtMC4yYy0zLjgsMy42LTcuNSw3LjQtMTEuMiwxMS4xDQoJCUM4OC45LDMwLjEsOTYuMiwzNy40LDEwMy42LDQ0Ljh6IE0zNy42LDExMC4xYy03LjMtNy4zLTE0LjUtMTQuNS0yMS43LTIxLjdjLTEuNiwxLjEtMi40LDIuNy0yLjYsNC42Yy0wLjYsNS42LTEuMywxMS4zLTEuOSwxNi45DQoJCWMtMC4yLDEuNy0wLjMsMy40LTAuNSw1LjJjNy41LTAuOSwxNC44LTEuNywyMi4xLTIuNUMzNSwxMTIuNCwzNi40LDExMS42LDM3LjYsMTEwLjF6Ii8+DQo8L2c+DQo8L3N2Zz4NCg=="  alt="imagen"/>
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{$data->links()}}
        </div>
    </div>
</div>

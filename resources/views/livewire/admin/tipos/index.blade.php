<div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow-lg border border-gray-200 container-ask">
    <!-- Encabezado: Título y botón de crear -->
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold primary-color title-ask-container" >Tipos</h1>
        <a href="{{ route('admin.tipos.create') }}"
           class="px-4 py-2 rounded text-white bg-green-500 hover:bg-green-600 flex items-center gap-2 button-add"
           title="Crear Tipo">
            <i class="fa-solid fa-plus"></i>
        </a>
    </div>

    <!-- Filtros: Select de paginación e Input de búsqueda -->
    <div class="flex items-center mb-4 w-full gap-2 form-container-ask form-table">
        <select wire:model.live="perPage" class="border-gray-300 rounded p-1 w-1/2">
            <option value="10">10 por página</option>
            <option value="20">20 por página</option>
            <option value="50">50 por página</option>
            <option value="100">100 por página</option>
            <option value="all">Todos</option>
        </select>
        <input type="text" wire:model.live="search" placeholder="Buscar..." class="border-gray-300 rounded p-1 w-1/2" />
    </div>

    <!-- Tabla -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border">
            <thead class="bg-gray-200">
            <tr>
                <th class="px-4 py-2 text-left">Nº</th>
                <th class="px-4 py-2 text-left">Carrera</th>
                <th class="px-4 py-2 text-left">Asignatura</th>
                <th class="px-4 py-2 text-left">Categoría</th>
                <th class="px-4 py-2 text-left">Nombre</th>
                <th class="px-4 py-2 text-center">Acciones</th>
            </tr>
            </thead>
            <tbody>
            @forelse($tipos as $index => $tipo)
                <tr class="border-b">
                    <td class="px-4 py-2">{{ $index + 1 }}</td>
                    <td class="px-4 py-2">{{ $tipo->category->area->team->name ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $tipo->category->area->name ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $tipo->category->name ?? '-' }}</td>
                    <td class="px-4 py-2">{{ $tipo->name }}</td>
                    <td class="px-4 py-2 text-center button-edit">
                        <a href="{{ route('admin.tipos.edit', $tipo) }}"
                           class="w-8 h-8 inline-flex items-center justify-center rounded text-white bg-green-500 hover:bg-green-600"
                           title="Editar Tipo">
                           <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyNC4yLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0ic3ZnMiIgeG1sbnM6c3ZnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyINCgkgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCAxMjYuMiAxMjYuMiINCgkgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMTI2LjIgMTI2LjI7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiNGRkZGRkY7fQ0KPC9zdHlsZT4NCjxnPg0KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0xMjYuMiwzMC4zYy0wLjEsNC44LTEuOSw4LjQtNSwxMS41Yy0xMSwxMC45LTIyLDIxLjgtMzMuMSwzMi43Yy0xNC4xLDE0LTI4LjMsMjcuOS00Mi40LDQyLjENCgkJYy0zLjgsMy44LTguMSw2LTEzLjUsNi41Yy04LjcsMC44LTE3LjMsMS45LTI2LDIuOGMtNC4xLDAuNS02LjctMi4xLTYuMi02LjJjMS4xLTkuNSwyLjEtMTksMy4yLTI4LjVjMC40LTMuOCwyLjItNyw0LjktOS43DQoJCWMxMy43LTEzLjYsMjcuMy0yNy4zLDQxLTQxQzYwLjgsMjguOSw3Mi41LDE3LjIsODQuMiw1LjVjNy4xLTcuMSwxNi4zLTcuMSwyMy40LDBjNC42LDQuNiw5LjIsOS4yLDEzLjgsMTMuOA0KCQlDMTI0LjYsMjIuNSwxMjYuMSwyNi4zLDEyNi4yLDMwLjN6IE00NS4zLDEwMi42YzE2LjktMTYuNywzMy44LTMzLjUsNTAuNi01MC4xQzg4LjUsNDUuMSw4MS4yLDM3LjcsNzQsMzAuNg0KCQlDNTcuMiw0Ny4zLDQwLjQsNjQuMiwyMy42LDgwLjlDMzAuNyw4OC4xLDM4LDk1LjMsNDUuMywxMDIuNnogTTEwMy42LDQ0LjhjMC4zLTAuMywwLjYtMC42LDAuOS0wLjljMy4zLTMuMyw2LjctNi42LDEwLTEwDQoJCWMxLjktMS45LDItNC45LDAuMS02LjhjLTUtNS4yLTEwLjItMTAuMy0xNS4zLTE1LjNjLTEuOC0xLjgtNC44LTEuOS02LjYtMC4yYy0zLjgsMy42LTcuNSw3LjQtMTEuMiwxMS4xDQoJCUM4OC45LDMwLjEsOTYuMiwzNy40LDEwMy42LDQ0Ljh6IE0zNy42LDExMC4xYy03LjMtNy4zLTE0LjUtMTQuNS0yMS43LTIxLjdjLTEuNiwxLjEtMi40LDIuNy0yLjYsNC42Yy0wLjYsNS42LTEuMywxMS4zLTEuOSwxNi45DQoJCWMtMC4yLDEuNy0wLjMsMy40LTAuNSw1LjJjNy41LTAuOSwxNC44LTEuNywyMi4xLTIuNUMzNSwxMTIuNCwzNi40LDExMS42LDM3LjYsMTEwLjF6Ii8+DQo8L2c+DQo8L3N2Zz4NCg==" />
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="px-4 py-2" colspan="6">No se encontraron tipos.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    @if($perPage !== 'all')
        <div class="mt-4 pagination-table">
            {{ $tipos->links() }}
        </div>
    @endif
</div>

<div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow-lg border border-gray-200 container-ask">
    <!-- Encabezado: Título y botón de crear -->
    <h1 class="text-2xl font-bold primary-color title-ask-container">Preguntas</h1>
    <div class="flex justify-end items-center mb-4">
        <a href="{{ route('admin.preguntas.create') }}"
           class="px-4 py-2  rounded text-white bg-green-500 hover:bg-green-600 flex gap-2 button-add"
           title="Crear Pregunta">
            <i class="fa-solid fa-plus"></i>
        </a>
        <a href="{{ route('admin.preguntas.cargar') }}"
           class="px-4 py-2 ml-4 rounded text-white bg-blue-500 hover:bg-blue-600 flex gap-2 upload-button"
           title="Cargar CSV">
            <i class="fa-solid fa-file-csv"></i>
        </a>

    </div>

    <!-- Filtros: Select de paginación e Input de búsqueda -->
    <div class="flex items-center mb-4 w-full gap-2 form-container-ask form-table">
        <select wire:model.live="perPage" class="border-gray-300 rounded p-1 w-1/2">
            <option value="10">10 por página</option>
            <option value="20">20 por página</option>
            <option value="50">50 por página</option>
            <option value="100">100 por página</option>
            <option value="all">Todas</option>
        </select>
        <input type="text" wire:model.live="search" placeholder="Buscar..." class="border-gray-300 rounded p-1 w-1/2 search-input" />
    </div>

    <!-- Tabla de Preguntas -->
    <div class="overflow-x-auto">
        <table class="min-w-full w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nº</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enunciado</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Universidades</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado por</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aprobada</th>
                <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @forelse($questions as $index => $question)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $index + 1 }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $question->content }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $question->question_type }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $question->universidades->pluck('name')->implode(', ') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $question->user ? $question->user->name : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $question->approved ? 'Sí' : 'No' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-center button-edit">
                        <a href="{{ route('admin.preguntas.edit', $question->id) }}"
                           class="w-8 h-8 inline-flex items-center justify-center rounded text-white bg-green-500 hover:bg-green-600"
                           title="Editar Pregunta">
                           <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyNC4yLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0ic3ZnMiIgeG1sbnM6c3ZnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyINCgkgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCAxMjYuMiAxMjYuMiINCgkgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMTI2LjIgMTI2LjI7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiNGRkZGRkY7fQ0KPC9zdHlsZT4NCjxnPg0KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0xMjYuMiwzMC4zYy0wLjEsNC44LTEuOSw4LjQtNSwxMS41Yy0xMSwxMC45LTIyLDIxLjgtMzMuMSwzMi43Yy0xNC4xLDE0LTI4LjMsMjcuOS00Mi40LDQyLjENCgkJYy0zLjgsMy44LTguMSw2LTEzLjUsNi41Yy04LjcsMC44LTE3LjMsMS45LTI2LDIuOGMtNC4xLDAuNS02LjctMi4xLTYuMi02LjJjMS4xLTkuNSwyLjEtMTksMy4yLTI4LjVjMC40LTMuOCwyLjItNyw0LjktOS43DQoJCWMxMy43LTEzLjYsMjcuMy0yNy4zLDQxLTQxQzYwLjgsMjguOSw3Mi41LDE3LjIsODQuMiw1LjVjNy4xLTcuMSwxNi4zLTcuMSwyMy40LDBjNC42LDQuNiw5LjIsOS4yLDEzLjgsMTMuOA0KCQlDMTI0LjYsMjIuNSwxMjYuMSwyNi4zLDEyNi4yLDMwLjN6IE00NS4zLDEwMi42YzE2LjktMTYuNywzMy44LTMzLjUsNTAuNi01MC4xQzg4LjUsNDUuMSw4MS4yLDM3LjcsNzQsMzAuNg0KCQlDNTcuMiw0Ny4zLDQwLjQsNjQuMiwyMy42LDgwLjlDMzAuNyw4OC4xLDM4LDk1LjMsNDUuMywxMDIuNnogTTEwMy42LDQ0LjhjMC4zLTAuMywwLjYtMC42LDAuOS0wLjljMy4zLTMuMyw2LjctNi42LDEwLTEwDQoJCWMxLjktMS45LDItNC45LDAuMS02LjhjLTUtNS4yLTEwLjItMTAuMy0xNS4zLTE1LjNjLTEuOC0xLjgtNC44LTEuOS02LjYtMC4yYy0zLjgsMy42LTcuNSw3LjQtMTEuMiwxMS4xDQoJCUM4OC45LDMwLjEsOTYuMiwzNy40LDEwMy42LDQ0Ljh6IE0zNy42LDExMC4xYy03LjMtNy4zLTE0LjUtMTQuNS0yMS43LTIxLjdjLTEuNiwxLjEtMi40LDIuNy0yLjYsNC42Yy0wLjYsNS42LTEuMywxMS4zLTEuOSwxNi45DQoJCWMtMC4yLDEuNy0wLjMsMy40LTAuNSw1LjJjNy41LTAuOSwxNC44LTEuNywyMi4xLTIuNUMzNSwxMTIuNCwzNi40LDExMS42LDM3LjYsMTEwLjF6Ii8+DQo8L2c+DQo8L3N2Zz4NCg==" />
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td class="px-6 py-4" colspan="7">No se encontraron preguntas.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación -->
    @if($perPage !== 'all')
        <div class="mt-4">
            {{ $questions->links() }}
        </div>
    @endif
</div>

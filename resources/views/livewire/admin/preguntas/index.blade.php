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
                        <a
                            class="w-8 h-8 inline-flex items-center justify-center rounded text-white bg-red-500 hover:bg-red-600 ml-2 "
                            onclick="confirm('¿Estás seguro de eliminar esta pregunta?') || event.stopImmediatePropagation()"
                            wire:click="delete({{ $question->id }})"
                            title="Eliminar Pregunta">
                           <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhLS0gQ3JlYXRlZCB3aXRoIElua3NjYXBlIChodHRwOi8vd3d3Lmlua3NjYXBlLm9yZy8pIC0tPgoKPHN2ZwogICB2ZXJzaW9uPSIxLjEiCiAgIGlkPSJzdmcyIgogICB3aWR0aD0iMTY4LjI2NjY2IgogICBoZWlnaHQ9IjE2OC4yNjY2NiIKICAgdmlld0JveD0iMCAwIDE2OC4yNjY2NiAxNjguMjY2NjYiCiAgIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyIKICAgeG1sbnM6c3ZnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CiAgPGRlZnMKICAgICBpZD0iZGVmczYiPgogICAgPGNsaXBQYXRoCiAgICAgICBjbGlwUGF0aFVuaXRzPSJ1c2VyU3BhY2VPblVzZSIKICAgICAgIGlkPSJjbGlwUGF0aDE2Ij4KICAgICAgPHBhdGgKICAgICAgICAgZD0iTSAwLDEyNi4yIEggMTI2LjIgViAwIEggMCBaIgogICAgICAgICBpZD0icGF0aDE0IiAvPgogICAgPC9jbGlwUGF0aD4KICA8L2RlZnM+CiAgPGcKICAgICBpZD0iZzgiCiAgICAgdHJhbnNmb3JtPSJtYXRyaXgoMS4zMzMzMzMzLDAsMCwtMS4zMzMzMzMzLDAsMTY4LjI2NjY3KSI+CiAgICA8ZwogICAgICAgaWQ9ImcxMCI+CiAgICAgIDxnCiAgICAgICAgIGlkPSJnMTIiCiAgICAgICAgIGNsaXAtcGF0aD0idXJsKCNjbGlwUGF0aDE2KSI+CiAgICAgICAgPGcKICAgICAgICAgICBpZD0iZzE4IgogICAgICAgICAgIHRyYW5zZm9ybT0idHJhbnNsYXRlKDc0Ljg2MzMsMTEwLjI1MDcpIj4KICAgICAgICAgIDxwYXRoCiAgICAgICAgICAgICBkPSJNIDAsMCBDIDAsMS40NjggMC4wNzMsMi44NTQgLTAuMDE4LDQuMjI5IC0wLjE0Niw2LjE3OCAtMS43NzYsNy43NTUgLTMuNzUxLDcuNzcxIC05LjEwMyw3LjgxNyAtMTQuNDU0LDcuODE3IC0xOS44MDYsNy43NyAtMjEuNjY1LDcuNzUzIC0yMy4zMDIsNi4yNTkgLTIzLjQ2OSw0LjQyNyAtMjMuNjAyLDIuOTggLTIzLjQ5NiwxLjUxMyAtMjMuNDk2LDAgWiBtIDMxLjQxNCwtMTkuNjM5IGMgMCwzLjAxMyAxMGUtNCw1LjkwOCAwLDguODAzIC0wLjAwMSwyLjQwNyAtMC41MjcsMi45MyAtMi45NTYsMi45MyAtMjYuODA2LDAgLTUzLjYxMSwwIC04MC40MTcsMCAtMC4xMjMsMCAtMC4yNDUsMTBlLTQgLTAuMzY4LDAgLTEuOTM5LC0wLjAxMSAtMi41OTQsLTAuNjQ1IC0yLjU5OSwtMi41NDkgLTAuMDA3LC0yLjczNyAtMC4wMDQsLTUuNDc1IDAuMDAyLC04LjIxMyAwLC0wLjMxMiAwLjA0OCwtMC42MjQgMC4wNzYsLTAuOTcxIHogbSAtODAuMTIzLC03Ljk1NiBjIDAsLTEuMTM3IC0wLjA0MiwtMi4xNTggMC4wMDYsLTMuMTc1IDAuNjE4LC0xMy4xMDIgMS4yNSwtMjYuMjAzIDEuODc0LC0zOS4zMDUgMC40MzcsLTkuMTg0IDAuODUsLTE4LjM2OCAxLjMxLC0yNy41NTEgMC4xNTksLTMuMTU1IDEuNjA2LC00LjQ3MSA0Ljc0MywtNC40NzEgMTkuMzMxLC0wLjAwMSAzOC42NjMsLTAuMDAxIDU3Ljk5NCwwIDMuMjE0LDAgNC42MzMsMS4zMDEgNC43OTIsNC41NDkgMC41MzksMTEuMDIgMS4wMzksMjIuMDQxIDEuNTYyLDMzLjA2MiAwLjQ1MSw5LjUwOSAwLjkxOCwxOS4wMTkgMS4zNzMsMjguNTI5IDAuMTMyLDIuNzY0IDAuMjQ3LDUuNTI5IDAuMzczLDguMzYyIHogbSA1LjU1NSwtODIuMzUyIGMgLTEuMTg4LDAuNDAzIC0yLjQ0MiwwLjY3NSAtMy41NTMsMS4yMzEgLTMuODY3LDEuOTM2IC02LjIyLDUuMDg0IC02LjUwMiw5LjQxNSAtMC41Miw3Ljk0OSAtMC44MzYsMTUuOTEzIC0xLjIyMSwyMy44NzEgLTAuNjM0LDEzLjEwMiAtMS4yNTgsMjYuMjAzIC0xLjg4MywzOS4zMDUgLTAuMTM0LDIuODEgLTAuMjU4LDUuNjIgLTAuMzk1LDguNTk2IC0wLjY5NSwwIC0xLjMzMywtMC4wMDkgLTEuOTcxLDAuMDAyIC0yLjQzMywwLjA0MyAtNC4wOCwxLjY2MSAtNC4wOTIsNC4wOTEgLTAuMDIxLDQuNTM2IC0wLjA0LDkuMDczIDAuMDA1LDEzLjYwOSAwLjA1Myw1LjQzNyA0LjMxNiw5LjcxOSA5Ljc1Myw5Ljc1NyA2LjgyNSwwLjA0OCAxMy42NSwwLjAxMyAyMC40NzUsMC4wMTMgaCAxLjE1OCBjIDAsMS4wOTMgLTAuMDAxLDIuMTM1IDAsMy4xNzggMC4wMDYsNy41MyA0Ljk4OCwxMi41MTYgMTIuNTEzLDEyLjUyIDQuODIzLDAuMDAzIDkuNjQ1LDAuMDA4IDE0LjQ2OCwtMTBlLTQgQyAyLjgsMTUuNjI1IDcuODQzLDEwLjU3MyA3Ljg2NiwzLjM2NiBjIDAuMDAzLC0xLjA5NSAwLC0yLjE5MSAwLC0zLjQyMyBoIDEuNDA1IGMgNi41MzksMCAxMy4wNzgsMC4wMDUgMTkuNjE3LC0wLjAwMSA2LjIxLC0wLjAwNiAxMC4zNjQsLTQuMTY3IDEwLjM3NSwtMTAuMzg0IDAuMDA3LC00LjE2OSAwLjAwNCwtOC4zMzcgMCwtMTIuNTA2IC0wLjAwMywtMy4wOSAtMS40OTIsLTQuNTczIC00LjU5LC00LjU4MSAtMC40NDUsLTAuMDAxIC0wLjg5MSwwIC0xLjQ0OCwwIC0wLjA4OCwtMS41MjMgLTAuMTg2LC0yLjk3NyAtMC4yNTUsLTQuNDMyIC0wLjUyNCwtMTEuMDYxIC0xLjAzMywtMjIuMTIzIC0xLjU2NSwtMzMuMTg0IC0wLjU0NCwtMTEuMzQ2IC0xLjA0MywtMjIuNjk1IC0xLjY5NCwtMzQuMDM1IC0wLjMwOCwtNS4zNTggLTQuMjEsLTkuNTA2IC05LjUxNiwtMTAuNTUyIC0wLjE5MSwtMC4wMzkgLTAuMzcsLTAuMTQyIC0wLjU1NCwtMC4yMTUgeiIKICAgICAgICAgICAgIHN0eWxlPSJmaWxsOiNmZmZmZmY7ZmlsbC1vcGFjaXR5OjE7ZmlsbC1ydWxlOm5vbnplcm87c3Ryb2tlOm5vbmUiCiAgICAgICAgICAgICBpZD0icGF0aDIwIiAvPgogICAgICAgIDwvZz4KICAgICAgICA8ZwogICAgICAgICAgIGlkPSJnMjIiCiAgICAgICAgICAgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMzkuNTU4Niw0NS40MzYyKSI+CiAgICAgICAgICA8cGF0aAogICAgICAgICAgICAgZD0ibSAwLDAgYyAwLDguNDIxIC0wLjAxLDE2Ljg0MSAwLjAwNiwyNS4yNjEgMC4wMDYsMy4wMDMgMi42NjcsNC45NSA1LjMxNiwzLjkwMyBDIDYuODE5LDI4LjU3MiA3LjY0NiwyNy40MTYgNy44MzEsMjUuODE4IDcuODczLDI1LjQ1NSA3Ljg0OSwyNS4wODMgNy44NDksMjQuNzE1IDcuODUsOC4yNDMgNy44NSwtOC4yMyA3Ljg0NiwtMjQuNzAzIGMgMCwtMC41MjkgMC4wMTgsLTEuMDcyIC0wLjA4NywtMS41ODYgLTAuNDAyLC0xLjk3NyAtMi4yNTcsLTMuMzI2IC00LjIyOSwtMy4xMjYgLTEuOTcyLDAuMiAtMy41MDYsMS44NDYgLTMuNTE4LDMuOTA5IEMgLTAuMDE5LC0yMC40NzkgMCwtMTUuNDUxIDAsLTEwLjQyMyBaIgogICAgICAgICAgICAgc3R5bGU9ImZpbGw6I2ZmZmZmZjtmaWxsLW9wYWNpdHk6MTtmaWxsLXJ1bGU6bm9uemVybztzdHJva2U6bm9uZSIKICAgICAgICAgICAgIGlkPSJwYXRoMjQiIC8+CiAgICAgICAgPC9nPgogICAgICAgIDxnCiAgICAgICAgICAgaWQ9ImcyNiIKICAgICAgICAgICB0cmFuc2Zvcm09InRyYW5zbGF0ZSg2Ny4wMzEzLDQ1LjQ5ODcpIj4KICAgICAgICAgIDxwYXRoCiAgICAgICAgICAgICBkPSJtIDAsMCBjIDAsLTguNDIxIDAuMDA5LC0xNi44NDEgLTAuMDA1LC0yNS4yNjIgLTAuMDA1LC0zLjAxOCAtMi42MjgsLTQuOTc5IC01LjI2OSwtMy45ODIgLTEuNTg5LDAuNTk5IC0yLjU1NiwxLjk5OCAtMi41NywzLjgwNCAtMC4wMTksMi41MzQgLTAuMDA1LDUuMDY4IC0wLjAwNSw3LjYwMiAtMTBlLTQsMTQuMzA3IC0wLjAwNSwyOC42MTMgMC4wMDMsNDIuOTIgMC4wMDIsMy4wOTkgMi42MjUsNS4wNjQgNS4zMjcsNC4wMjUgMS41ODcsLTAuNjA5IDIuNTEzLC0yLjA0MyAyLjUxNSwtMy45NjkgQyAwLjAwNCwxNy45NDQgMCwxMC43NSAwLDMuNTU2IFoiCiAgICAgICAgICAgICBzdHlsZT0iZmlsbDojZmZmZmZmO2ZpbGwtb3BhY2l0eToxO2ZpbGwtcnVsZTpub256ZXJvO3N0cm9rZTpub25lIgogICAgICAgICAgICAgaWQ9InBhdGgyOCIgLz4KICAgICAgICA8L2c+CiAgICAgICAgPGcKICAgICAgICAgICBpZD0iZzMwIgogICAgICAgICAgIHRyYW5zZm9ybT0idHJhbnNsYXRlKDc4LjgwNTcsNDUuNTYwMikiPgogICAgICAgICAgPHBhdGgKICAgICAgICAgICAgIGQ9Im0gMCwwIGMgMCw4LjM3OSAtMC4wMTEsMTYuNzU5IDAuMDA1LDI1LjEzOCAwLjAwNiwzLjAwMiAyLjY2OSw0Ljk0OSA1LjMxNywzLjkwMSAxLjQ5NywtMC41OTIgMi4zMjQsLTEuNzQ4IDIuNTA4LC0zLjM0NiAwLjA0MiwtMC4zNjMgMC4wMTgsLTAuNzM1IDAuMDE5LC0xLjEwMyAwLC0xNi40NzIgMTBlLTQsLTMyLjk0NSAtMC4wMDMsLTQ5LjQxOCAwLC0wLjUyOSAwLjAxNywtMS4wNzIgLTAuMDg3LC0xLjU4NiAtMC40MDMsLTEuOTc3IC0yLjI1OSwtMy4zMjYgLTQuMjMxLC0zLjEyNSAtMS45NzEsMC4yMDEgLTMuNTA1LDEuODQ2IC0zLjUxNywzLjkxIC0wLjAzMSw0Ljk4NiAtMC4wMTEsOS45NzQgLTAuMDEyLDE0Ljk2IDAsMy41NTcgMCw3LjExMiAwLjAwMSwxMC42NjkiCiAgICAgICAgICAgICBzdHlsZT0iZmlsbDojZmZmZmZmO2ZpbGwtb3BhY2l0eToxO2ZpbGwtcnVsZTpub256ZXJvO3N0cm9rZTpub25lIgogICAgICAgICAgICAgaWQ9InBhdGgzMiIgLz4KICAgICAgICA8L2c+CiAgICAgIDwvZz4KICAgIDwvZz4KICA8L2c+Cjwvc3ZnPgo=" />
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

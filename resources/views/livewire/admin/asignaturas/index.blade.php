<div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow-lg border border-gray-200">
    <!-- Encabezado con título y botón de crear -->
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Carreras</h1>
        <a href="{{ route('admin.asignaturas.create') }}"
           class="px-4 py-2 rounded text-white bg-green-500 hover:bg-green-600 flex items-center gap-2"
           title="Crear asignatura">
            <i class="fa-solid fa-plus"></i>
        </a>
    </div>

    <!-- Tabla de carreras -->
    <table class="min-w-full bg-white border">
        <thead class="bg-gray-200">
        <tr>
            <th class="px-4 py-2 text-left">Nº</th>
            <th class="px-4 py-2 text-left">Carrera</th>
            <th class="px-4 py-2 text-left">Nombre</th>
            <th class="px-4 py-2 text-center">Acciones</th>
        </tr>
        </thead>
        <tbody>
        @foreach($asignaturas as $index => $item)
            <tr class="border-b">
                <td class="px-4 py-2">{{ $index + 1 }}</td>
                <td class="px-4 py-2">{{ $item->team->name }}</td>
                <td class="px-4 py-2">{{ $item->name }}</td>
                <td class="px-4 py-2 text-center">
                    <a href="{{ route('admin.asignaturas.edit', $item) }}"
                       class="w-8 h-8 inline-flex items-center justify-center rounded text-white bg-green-500 hover:bg-green-600"
                       title="Editar Carrera">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

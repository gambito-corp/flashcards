<div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow-lg border border-gray-200">
    <!-- Encabezado: Título y botón de crear -->
    <h1 class="text-2xl font-bold">Preguntas</h1>
    <div class="flex justify-end items-center mb-4">
        <a href="{{ route('admin.preguntas.create') }}"
           class="px-4 py-2 mr-4 rounded text-white bg-green-500 hover:bg-green-600 flex gap-2"
           title="Crear Pregunta">
            <i class="fa-solid fa-plus"></i>
        </a>
        <a href="{{ route('admin.preguntas.cargar') }}"
           class="px-4 py-2 ml-4 rounded text-white bg-blue-500 hover:bg-blue-600 flex gap-2"
           title="Cargar CSV">
            <i class="fa-solid fa-file-csv"></i>
        </a>

    </div>

    <!-- Filtros: Select de paginación e Input de búsqueda -->
    <div class="flex items-center mb-4 w-full gap-2">
        <select wire:model.live="perPage" class="border-gray-300 rounded p-1 w-1/2">
            <option value="10">10 por página</option>
            <option value="20">20 por página</option>
            <option value="50">50 por página</option>
            <option value="100">100 por página</option>
            <option value="all">Todas</option>
        </select>
        <input type="text" wire:model.live="search" placeholder="Buscar..." class="border-gray-300 rounded p-1 w-1/2" />
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
                    <td class="px-6 py-4 whitespace-nowrap text-center">
                        <a href="{{ route('admin.preguntas.edit', $question->id) }}"
                           class="w-8 h-8 inline-flex items-center justify-center rounded text-white bg-green-500 hover:bg-green-600"
                           title="Editar Pregunta">
                            <i class="fa-solid fa-pen"></i>
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

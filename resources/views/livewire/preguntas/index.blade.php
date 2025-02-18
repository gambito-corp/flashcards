<div>
    <!-- Encabezado: botones y filtros -->
    <div class="flex justify-between items-center mb-4">
        <!-- Botones de acción -->
        <div class="flex space-x-4">
            <button wire:click="openCreateModal"
                    class="flex items-center bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded">
                <!-- Ícono de "más" -->
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Crear Pregunta
            </button>
            <button wire:click="openCreateModal2"
                    class="flex items-center bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">
                <!-- Ícono CSV -->
                <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M4 8h16M4 12h16" />
                </svg>
                Cargar Preguntas
            </button>
        </div>
        <!-- Filtros: Select de paginación e input de búsqueda -->
        <div class="flex space-x-4 items-center">
            <select wire:model.debounce.300ms="perPage" class="border-gray-300 rounded p-1">
                <option value="10">10 por página</option>
                <option value="20">20 por página</option>
                <option value="50">50 por página</option>
                <option value="100">100 por página</option>
                <option value="all">Todas</option>
            </select>
            <input type="text" wire:model.debounce.300ms="search" placeholder="Buscar..."
                   class="border-gray-300 rounded p-1"/>
        </div>
    </div>

    <!-- Tabla de preguntas -->
    <div class="overflow-x-auto">
        <table class="min-w-full w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Enunciado</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Universidades</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Creado por</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aprobada</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @forelse ($questions as $question)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $question->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $question->content }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $question->question_type }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $question->universidades->pluck('name')->implode(', ') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        {{ $question->user ? $question->user->name : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">{{ $question->approved ? 'Sí' : 'No' }}</td>
                </tr>
            @empty
                <tr>
                    <td class="px-6 py-4" colspan="6">No se encontraron preguntas.</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <!-- Paginación dinámica -->
    <div class="mt-4">
        @if($perPage !== 'all')
            {{ $questions->links() }}
        @endif
    </div>

    <!-- Modal personalizado para crear preguntas -->
    <x-modal wire:model="showModal">
        <livewire:preguntas.create-question-modal :question="$question" />
    </x-modal>

    <!-- Modal personalizado para crear preguntas -->
    <x-modal wire:model="showModal2">
        <livewire:preguntas.create-csv-questions />
    </x-modal>

</div>

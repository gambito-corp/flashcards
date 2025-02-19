<div>
    <div class="w-full">
        <!-- Línea de botones centrados ocupando el 100% del ancho -->
        <div class="flex flex-wrap gap-2 w-full my-4 justify-center">
            <a href="{{ route('preguntas.crear') }}" class="flex items-center bg-green-400 hover:bg-green-500 text-white font-semibold py-1 px-2 rounded text-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                <span class="ml-1">Crear Pregunta</span>
            </a>
            <a href="{{ route('preguntas.cargar') }}" class="flex items-center bg-blue-400 hover:bg-blue-500 text-white font-semibold py-1 px-2 rounded text-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a2 2 0 002 2h12a2 2 0 002-2v-1M4 8h16M4 12h16" />
                </svg>
                <span class="ml-1">Cargar Preguntas</span>
            </a>
            <a href="{{ route('preguntas.carrera') }}" class="flex items-center bg-red-400 hover:bg-red-500 text-white font-semibold py-1 px-2 rounded text-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9-4 9 4v6a9 9 0 11-18 0V7z" />
                </svg>
                <span class="ml-1">Crear Carreras</span>
            </a>
            <a href="{{ route('preguntas.asignatura') }}" class="flex items-center bg-purple-400 hover:bg-purple-500 text-white font-semibold py-1 px-2 rounded text-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 19.5A2.5 2.5 0 006.5 22H20V2H6.5A2.5 2.5 0 004 4.5v11z" />
                </svg>
                <span class="ml-1">Crear Asignaturas</span>
            </a>
            <a href="{{ route('preguntas.categoria') }}" class="flex items-center bg-orange-400 hover:bg-orange-500 text-white font-semibold py-1 px-2 rounded text-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12" />
                </svg>
                <span class="ml-1">Crear Categorías</span>
            </a>
            <a href="{{ route('preguntas.tipo') }}" class="flex items-center bg-teal-400 hover:bg-teal-500 text-white font-semibold py-1 px-2 rounded text-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12v-2a2 2 0 00-2-2h-2V6a2 2 0 00-2-2h-2m0 12v2a2 2 0 002 2h2v-2a2 2 0 012-2h2" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 6h.01M13 10h.01M9 14h.01M15 6h.01" />
                </svg>
                <span class="ml-1">Crear Tipos</span>
            </a>
            <a href="{{ route('preguntas.universidad') }}" class="flex items-center bg-indigo-400 hover:bg-indigo-500 text-white font-semibold py-1 px-2 rounded text-sm">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-2a4 4 0 014-4h10a4 4 0 014 4v2M7 10V3a1 1 0 011-1h8a1 1 0 011 1v7" />
                </svg>
                <span class="ml-1">Crear Universidad</span>
            </a>
        </div>

        <!-- Filtros: select e input, cada uno al 50% -->
        <div class="flex items-center mb-4 w-full">
            <select wire:model.live="perPage" class="border-gray-300 rounded p-1 w-1/2">
                <option value="10">10 por página</option>
                <option value="20">20 por página</option>
                <option value="50">50 por página</option>
                <option value="100">100 por página</option>
                <option value="all">Todas</option>
            </select>
            <input type="text" wire:model.live="search" placeholder="Buscar..." class="border-gray-300 rounded p-1 w-1/2" />
        </div>
    </div>
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
    <div class="mt-4">
        @if($perPage !== 'all')
            {{ $questions->links() }}
        @endif
    </div>
</div>

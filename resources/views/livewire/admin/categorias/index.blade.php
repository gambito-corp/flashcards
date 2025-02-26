<div class="max-w-7xl mx-auto bg-white p-6 rounded-lg shadow-lg border border-gray-200">
    <!-- Encabezado: Título y botón de crear -->
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Categorías</h1>
        <a href="{{ route('admin.categorias.create') }}"
           class="px-4 py-2 rounded text-white bg-green-500 hover:bg-green-600 flex items-center gap-2"
           title="Crear Categoría">
            <i class="fa-solid fa-plus"></i>
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
        <input type="text" wire:model.live="search" placeholder="Buscar..."
               class="border-gray-300 rounded p-1 w-1/2" />
    </div>

    <!-- Tabla de Categorías -->
    <table class="min-w-full bg-white border">
        <thead class="bg-gray-200">
        <tr>
            <th class="px-4 py-2 text-left">Nº</th>
            <th class="px-4 py-2 text-left">Carrera</th>
            <th class="px-4 py-2 text-left">Asignatura</th>
            <th class="px-4 py-2 text-left">Nombre</th>
            <th class="px-4 py-2 text-center">Acciones</th>
        </tr>
        </thead>
        <tbody>
        @forelse($categories as $index => $category)
            <tr class="border-b">
                <td class="px-4 py-2">{{ $index + 1 }}</td>
                <td class="px-4 py-2">
                    {{ $category->area->team->name ?? '-' }}
                </td>
                <td class="px-4 py-2">
                    {{ $category->area->name ?? '-' }}
                </td>
                <td class="px-4 py-2">{{ $category->name }}</td>
                <td class="px-4 py-2 text-center">
                    <a href="{{ route('admin.categorias.edit', $category->id) }}"
                       class="w-8 h-8 inline-flex items-center justify-center rounded text-white bg-green-500 hover:bg-green-600"
                       title="Editar Categoría">
                        <i class="fa-solid fa-pen"></i>
                    </a>
                </td>
            </tr>
        @empty
            <tr>
                <td class="px-4 py-2" colspan="5">No se encontraron categorías.</td>
            </tr>
        @endforelse
        </tbody>
    </table>

    <!-- Paginación -->
    @if($perPage !== 'all')
        <div class="mt-4">
            {{ $categories->links() }}
        </div>
    @endif
</div>

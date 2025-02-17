@php
    // Se espera que $categories sea una colección (puede ser Eloquent o Collection) de categorías con la propiedad 'children'.
@endphp

<div>
    <!-- Lista de pestañas para el nivel actual -->
    <ul class="flex border-b">
        @foreach($categories as $category)
            <li class="mr-2">
                <!-- Aquí podrías usar un enlace que establezca la categoría activa (por ejemplo, agregando un parámetro a la URL) -->
                <a href="{{ route('examenes.index', ['area' => request('area'), 'category' => $category->id]) }}"
                   class="inline-block px-4 py-2 text-gray-500 hover:text-green-500 border-b-2 {{ request('category', $categories->first()->id) == $category->id ? 'border-green-500 text-green-500' : 'border-transparent' }}">
                    {{ $category->name }}
                </a>
            </li>
        @endforeach
    </ul>
    <!-- Renderizamos de forma recursiva las subcategorías del nivel actual -->
    @foreach($categories as $category)
        @if($category->children->count())
            <div class="ml-4 mt-2">
                @include('partials.category-tabs', ['categories' => $category->children])
            </div>
        @endif
    @endforeach
</div>

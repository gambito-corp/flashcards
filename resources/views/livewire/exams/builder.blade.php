<div class="mb-8">

<div class="bg-white p-6 rounded container-askt mb-8">
    <h1 class="text-2xl font-semibold mb-4 primary-color title-ask-container">Examen</h1>
    <hr>
    <div class="carousel-container overflow-x-auto mb-5 relative">
    <ul class="carousel-list flex gap-4">
        @foreach ($areas as $area)
            <li
                class="carousel-item min-w-fit cursor-pointer pb-4 pt-4 pl-6 pr-6 rounded-[5px] text-sm flex justify-center items-center font-medium text-center duration-300 ease-in {{ isset($selectedArea) && $selectedArea->id === $area->id
                    ? 'font-semibold bg-[#195b81] text-white'
                    : 'bg-[#f7f7f7] border-transparent text-gray-600 hover:text-white hover:bg-[#195b81]' }}"
                wire:click="getCategories({{ $area->id }})">
                {{ $area->name }}
            </li>
        @endforeach
    </ul>

    <!-- Flechas de navegación -->
    <button class="prev absolute left-0 top-1/2 transform -translate-y-1/2 bg-gray-600 text-white p-2 rounded-full">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button class="next absolute right-0 top-1/2 transform -translate-y-1/2 bg-gray-600 text-white p-2 rounded-full">
        <i class="fas fa-chevron-right"></i>
    </button>
</div>



<!-- Carrusel de Categorías -->
<div class="carousel-container overflow-x-auto mb-5 relative">
    <ul class="carousel-list flex space-x-4">
        @foreach ($categories as $category)
            <li
                class="carousel-item min-w-fit cursor-pointer pb-4 pt-4 pl-6 pr-6 rounded-[5px] text-sm flex justify-center items-center font-medium text-center duration-300 ease-in {{ isset($selectedCategory) && $selectedCategory->id === $category->id
                    ? 'font-semibold bg-[#157b80] text-white'
                    : 'bg-[#f7f7f7] border-transparent text-gray-600 hover:text-white hover:bg-[#157b80]' }}"
                wire:click="getTypes({{ $category->id }})">
                {{ $category->name }}
            </li>
        @endforeach
    </ul>

    <!-- Flechas de navegación -->
    <button class="prev absolute left-0 top-1/2 transform -translate-y-1/2 bg-gray-600 text-white p-2 rounded-full">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button class="next absolute right-0 top-1/2 transform -translate-y-1/2 bg-gray-600 text-white p-2 rounded-full">
        <i class="fas fa-chevron-right"></i>
    </button>
</div>

<!-- Carrusel de Tipos -->
<div class="carousel-container overflow-x-auto mb-5 relative">
    <ul class="carousel-list flex space-x-4">
        @foreach ($tipos as $tipo)
            <li
                class="carousel-item min-w-fit cursor-pointer pb-4 pt-4 pl-6 pr-6 rounded-[5px] text-sm flex justify-center items-center font-medium text-center duration-300 ease-in {{ isset($selectedTipo) && $selectedTipo->id === $tipo->id
                    ? 'font-semibold bg-[#5b8080] text-white'
                    : 'bg-[#f7f7f7] border-transparent text-gray-600 hover:text-white hover:bg-[#5b8080]' }}"
                wire:click="setTypes({{ $tipo->id }})">
                {{ $tipo->name }}
            </li>
        @endforeach
    </ul>

    <!-- Flechas de navegación -->
    <button class="prev absolute left-0 top-1/2 transform -translate-y-1/2 bg-gray-600 text-white p-2 rounded-full">
        <i class="fas fa-chevron-left"></i>
    </button>
    <button class="next absolute right-0 top-1/2 transform -translate-y-1/2 bg-gray-600 text-white p-2 rounded-full">
        <i class="fas fa-chevron-right"></i>
    </button>
</div>



    <div class="mt-10 form-container-ask ">
        <label for="university" class="">
            Universidad
        </label>
        <select id="university" wire:model="selectedUniversity" wire:change="filterQuestions" class="mt-1 block w-full rounded border-gray-300 focus:border-[#195b81] focus:ring-[#195b81] ">
            <option value="">Todas las universidades</option>
            @foreach ($universities as $university)
                <option value="{{ $university->id }}">{{ $university->name }}</option>
            @endforeach
        </select>
    </div>

    <!-- Texto que muestra el total de preguntas disponibles -->
    <div class="mt-2 mb-6">
        <p class="bg-[#e4f1f1] rounded-md p-4 text-[#333333] text-sm font-normal">
            Preguntas Disponibles:
            @if($selectedUniversity)
                {{ optional($questions->firstWhere('universidad_id', $selectedUniversity))->question_count ?? 0 }}
            @else
                {{ optional($questions->firstWhere('universidad_id', null))->question_count ?? 0 }}
            @endif
        </p>
    </div>

    @php
        // Se obtiene el conteo de preguntas disponibles según la selección actual.
        $availableCount = $selectedUniversity
            ? optional($questions->firstWhere('universidad_id', $selectedUniversity))->question_count
            : optional($questions->firstWhere('universidad_id', null))->question_count;
        // Si no se obtiene un count, se usa 200 por defecto.
        $maxQuestions = $availableCount ? $availableCount : 200;
    @endphp



    <div class="mt-4 form-container-ask">
        <label for="questionCount" class="block text-gray-700 text-sm font-bold mb-2">
            Número de preguntas a utilizar
        </label>
        <input type="number"
               id="questionCount"
               wire:model="selectedQuestionCount"
               min="1"
               max="{{ $maxQuestions }}"
               class="mt-1 block w-full rounded border-gray-300 focus:border-[#195b81] focus:ring-[#195b81] "
               placeholder="Ingrese el número de preguntas">
        @error('selectedQuestionCount')
        <span class="text-red-600 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <!-- Botón para agregar la combinación -->
    <div class="mt-5">
        <button type="button" wire:click="addCombination" class="text-white rounded boton-success-m button-c2">
            Agregar Combinación
        </button>
    </div>

    <!-- Mensajes de error o éxito -->
    @if(session()->has('error'))
        <div class="mt-2 text-red-600">
            {{ session('error') }}
        </div>
    @endif
    @if(session()->has('success'))
        <div class="mt-2 text-green-600">
            {{ session('success') }}
        </div>
    @endif
    </div>
    <hr class="border-gray-300">
    <div class="bg-white p-6 rounded container-askt">
    <!-- Mostrar la colección de combinaciones agregadas -->
    <div class="">
        <h2 class="text-2xl font-semibold mb-4 primary-color title-ask-container">Combinaciones Agregadas</h2>
        <hr>
        <ul>
            @foreach($examCollection as $exam)
                <li class="border-b border-gray-300 py-2">
                    Área: {{ $exam['area_name'] }},
                    Categoría: {{ $exam['category_name'] }},
                    Tipo: {{ $exam['tipo_name'] }},
                    Universidad: {{ $exam['university_id'] ? $exam['university_id'] : 'Todas' }},
                    Preguntas: {{ $exam['question_count'] }}
                </li>
            @endforeach
            <li class="bg-[#e4f1f1] rounded-md p-4 text-[#333333] text-sm font-normal">
                Total de preguntas:
                @if(count($examCollection))
                    {{ collect($examCollection)->sum('question_count') }}
                @else
                    Agregar Preguntas
                @endif
            </li>
        </ul>
    </div>

    <!-- Campo para Título del Examen -->
    <div class="mt-4 form-container-ask">
        <label for="examTitle" class="block text-gray-700 text-sm font-bold mb-2">
            Título del Examen
        </label>
        <input type="text"
               wire:model="examTitle"
               id="examTitle"
               name="examTitle"
               placeholder="Ingrese el título del examen"
               class="block w-full px-4 py-2 border border-gray-300 rounded focus:border-[#195b81] focus:ring-[#195b81] ">
        @error('examTitle')
        <span class="text-red-600 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <!-- Campo para Tiempo del Examen -->
    <div class="mt-6 form-container-ask">
        <label for="examTime" class="block text-gray-700 text-sm font-bold mb-2">
            Tiempo del Examen (minutos)
        </label>
        <input type="number"
               wire:model="examTime"
               id="examTime"
               name="examTime"
               min="1"
               placeholder="Ingrese el tiempo en minutos"
               class="block w-full px-4 py-2 border border-gray-300 rounded focus:border-[#195b81] focus:ring-[#195b81] ">
        @error('examTime')
        <span class="text-red-600 text-sm">{{ $message }}</span>
        @enderror
    </div>

    <!-- Formulario para enviar la información mediante POST -->
    <form action="{{route('examenes.create')}}" method="POST">
        @csrf
        @method('POST')
        <input type="hidden" name="examCollection" value="{{ json_encode($examCollection) }}"/>
        <input type="hidden" name="examTitle" value="{{ $examTitle }}"/>
        <input type="hidden" name="examTime" value="{{ $examTime }}"/>
        @error('examCollection')
        <span class="text-red-600 text-sm">{{ $message }}</span>
        @enderror
        <!-- Botón para Realizar el Examen -->
        <div class="mt-4">
            <input type="submit" value="Realizar Examen"  class=" text-white cursor-pointer  rounded boton-success-m"/>
        </div>
    </form>
</div>
</div>


@push('styles')

<style>
.carousel-container {
    position: relative;
    overflow: hidden;
}

.carousel-list {
    display: flex;
    transition: transform 0.3s ease-in-out;
}

.carousel-item {
    flex-shrink: 0;
    transition: transform 0.3s ease;
}

.prev,
.next {
    position: absolute;
    top: 50%;
    z-index: 5;
    width: 30px;
    height: 30px;
    background-color: rgba(0, 0, 0, 0.4);
    color: white;
    border: none;
    padding: 10px;
    cursor: pointer;
    border-radius: 50%;
    transform: translateY(-50%);
}

@media (min-width:992px){
.prev,
.next {
  
    display: none;
}
}
.carousel-container button i {
    line-height: 12px;
    font-size: 12px;
}

.prev:hover,
.next:hover {
    background-color: rgba(0, 0, 0, 0.8);
}

/* Mostrar flechas cuando sea necesario */
.carousel-container:hover .prev,
.carousel-container:hover .next {
    display: block;
}


</style>
@endpush

<script>

document.addEventListener('DOMContentLoaded', () => {
    // Seleccionar todos los carruseles
    const carousels = document.querySelectorAll('.carousel-container');
    
    carousels.forEach(carousel => {
        const carouselList = carousel.querySelector('.carousel-list');
        const prevButton = carousel.querySelector('.prev');
        const nextButton = carousel.querySelector('.next');
        
        // Mover el carrusel con las flechas
        let scrollAmount = 0;
        const itemWidth = carousel.querySelector('.carousel-item').offsetWidth + 16; // Se agrega el padding entre los items

        nextButton.addEventListener('click', () => {
            if (scrollAmount < carouselList.scrollWidth - carouselList.offsetWidth) {
                scrollAmount += itemWidth;
                carouselList.style.transform = `translateX(-${scrollAmount}px)`;
            }
        });

        prevButton.addEventListener('click', () => {
            if (scrollAmount > 0) {
                scrollAmount -= itemWidth;
                carouselList.style.transform = `translateX(-${scrollAmount}px)`;
            }
        });

        // Funcionalidad de arrastre
        let isDown = false;
        let startX;
        let scrollLeft;

        carouselList.addEventListener('mousedown', (e) => {
            isDown = true;
            startX = e.pageX - carouselList.offsetLeft;
            scrollLeft = carouselList.scrollLeft;
        });

        carouselList.addEventListener('mouseleave', () => {
            isDown = false;
        });

        carouselList.addEventListener('mouseup', () => {
            isDown = false;
        });

        carouselList.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - carouselList.offsetLeft;
            const walk = (x - startX) * 2; // 2 es la velocidad de arrastre
            carouselList.scrollLeft = scrollLeft - walk;
        });
    });
});

</script>
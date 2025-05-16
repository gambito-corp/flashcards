<div class="mb-8">
    <div class="bg-white  rounded container-askt mb-8">
        <div class="flex mb-4 flex md:justify-around justify-start items-center flex-wrap">
            <h1 class="text-2xl font-semibold primary-color title-ask-container md:mb-0 mb-2">Examen</h1>
            @if(!Auth::user()->hasAnyRole('root') && Auth::user()->status == 0)
                <h3 class="flex justify-around items-center flex-wrap md:text-base text-[14px] px-5 md:px-0">Los
                    usuarios Fremium solo pueden solo seleecionar 10 preguntas. ¿Quieres preguntas ilimitadas?
                    <div
                        class="px-0 md:px-3 rounded-lg md:w-auto w-full">
                        <a href="{{route('planes')}}"
                           target="_blank"
                           class="pointer-events-auto px-6 py-4 md:px-6 md:py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-bold rounded-full shadow-lg  hover:scale-105 transition flex items-center justify-center text-[13px] md:tex-base mt-3 md:mt-0">
                            🔒 Hazte PRO
                        </a>
                    </div>
                </h3>
            @endif
        </div>
        <hr>
        <div class="carousel-container overflow-x-auto mb-5 relative">
            <ul class="carousel-list flex gap-4   overflow-x-auto scroll-smooth scroll">
                @foreach ($areas as $area)
                    <li
                        title="{{ $area->description }}"
                        class="carousel-item min-w-fit cursor-pointer pb-4 pt-4 pl-6 pr-6 rounded-[5px] text-sm flex
                justify-center items-center font-medium text-center duration-300 ease-in
                {{ isset($selectedArea) && $selectedArea->id === $area->id
                    ? 'font-semibold bg-[#195b81] text-white'
                    : 'bg-[#f7f7f7] border-transparent text-gray-600 hover:text-white hover:bg-[#195b81]' }}
                    "
                        wire:click="selectArea({{$area->id}})">
                        {{ $area->name }}
                    </li>
                @endforeach
            </ul>

            {{--    <!-- Flechas de navegación -->--}}
            {{--    <button class="prev absolute left-0 top-1/2 transform -translate-y-1/2 w-[35px] h-[35px] bg-[#00000059] text-white p-2 rounded-full">--}}
            {{--        <i class="fas fa-chevron-left"></i>--}}
            {{--    </button>--}}
            {{--    <button class="next absolute right-0 top-1/2 transform -translate-y-1/2 w-[35px] h-[35px] bg-[#00000059] text-white p-2 rounded-full">--}}
            {{--        <i class="fas fa-chevron-right"></i>--}}
            {{--    </button>--}}
        </div>


        <!-- Carrusel de Categorías -->
        <div class="carousel-container overflow-x-auto mb-5 relative">
            <ul class="carousel-list flex space-x-4  overflow-x-auto scroll-smooth">
                @foreach ($categories as $category)
                    <li
                        title="{{ $category->description }}"
                        class="carousel-item min-w-fit cursor-pointer pb-4 pt-4 pl-6 pr-6 rounded-[5px] text-sm flex justify-center
                 items-center font-medium text-center duration-300 ease-in
                 {{ isset($selectedCategory) && $selectedCategory->id === $category->id
                    ? 'font-semibold bg-[#157b80] text-white'
                    : 'bg-[#f7f7f7] border-transparent text-gray-600 hover:text-white hover:bg-[#157b80]' }}"
                        wire:click="selectCategory({{$category->id}})">
                        {{ $category->name }}
                    </li>
                @endforeach
            </ul>

            {{--    <!-- Flechas de navegación -->--}}
            {{--    <button class="prev absolute left-0 top-1/2 transform -translate-y-1/2 w-[35px] h-[35px] bg-[#00000059] text-white p-2 rounded-full">--}}
            {{--        <i class="fas fa-chevron-left"></i>--}}
            {{--    </button>--}}
            {{--    <button class="next absolute right-0 top-1/2 transform -translate-y-1/2 w-[35px] h-[35px] bg-[#00000059] text-white p-2 rounded-full">--}}
            {{--        <i class="fas fa-chevron-right"></i>--}}
            {{--    </button>--}}
        </div>

        <!-- Carrusel de Tipos -->
        <div class="carousel-container overflow-x-auto mb-5 relative">
            <ul class="carousel-list flex space-x-4 overflow-x-auto scroll-smooth">
                @if(count($categories) > 0)
                    @foreach ($tipos as $tipo)
                        <li
                            class="carousel-item min-w-fit cursor-pointer pb-4 pt-4 pl-6 pr-6 rounded-[5px] text-sm flex justify-center items-center font-medium text-center duration-300 ease-in {{ isset($selectedTipo) && $selectedTipo->id === $tipo->id
                        ? 'font-semibold bg-[#5b8080] text-white'
                        : 'bg-[#f7f7f7] border-transparent text-gray-600 hover:text-white hover:bg-[#5b8080]' }}"
                            wire:click="selectTipo({{$tipo->id}})">
                            {{ $tipo->name }}
                        </li>
                    @endforeach
                @endif
            </ul>

            {{--    <!-- Flechas de navegación -->--}}
            {{--    <button class="prev absolute left-0 top-1/2 transform -translate-y-1/2 w-[35px] h-[35px] bg-[#00000059] text-white p-2 rounded-full">--}}
            {{--        <i class="fas fa-chevron-left"></i>--}}
            {{--    </button>--}}
            {{--    <button class="next absolute right-0 top-1/2 transform -translate-y-1/2 w-[35px] h-[35px] bg-[#00000059]  text-white p-2 rounded-full">--}}
            {{--        <i class="fas fa-chevron-right"></i>--}}
            {{--    </button>--}}
        </div>


        @if(count($categories) > 0)
            <div class="mt-10 form-container-ask ">
                <label for="university" class="">
                    Universidad
                </label>
                <select id="university" wire:model="selectedUniversity" wire:change="filterQuestions"
                        class="mt-1 block w-full rounded border-gray-300 focus:border-[#195b81] focus:ring-[#195b81] ">
                    <option value="">Todas las universidades</option>
                    @foreach ($universities as $university)
                        <option value="{{ $university->id }}">{{ $university->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif


        <!-- Texto que muestra el total de preguntas disponibles -->
        <div class="mt-2 mb-6">
            <p class="bg-[#e4f1f1] rounded-md p-4 text-[#333333] text-sm font-normal">
                Preguntas Disponibles:
                @if(count($categories) > 0)
                    @if(count($tipos) > 0)
                        @if($selectedUniversity)
                            {{ optional($questions->firstWhere('universidad_id', $selectedUniversity))->question_count ?? 0 }}
                        @else
                            {{ optional($questions->firstWhere('universidad_id', null))->question_count ?? 0 }}
                        @endif
                    @else
                        0
                    @endif
                @else
                    0
                @endif
            </p>
        </div>
        @if(count($categories) > 0)
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
        @endif
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
            <input wire:model.live="examTitle" type="hidden" name="examTitle" value="{{ $examTitle }}"/>
            <input wire:model.live="examTime" type="hidden" name="examTime" value="{{ $examTime }}"/>
            @error('examCollection')
            <span class="text-red-600 text-sm">{{ $message }}</span>
            @enderror
            <!-- Botón para Realizar el Examen -->
            <div class="mt-4">
                <input type="submit" value="Realizar Examen"
                       class=" text-white cursor-pointer  rounded boton-success-m"/>
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

        .carousel-list::-webkit-scrollbar {
            height: 0px;
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

        @media (min-width: 992px) {
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
    function initCarousels() {
        document.querySelectorAll('.carousel-container').forEach(carousel => {
            const list = carousel.querySelector('.carousel-list');
            if (!list) return;

            const item = list.querySelector('.carousel-item');
            const itemWidth = item ? item.offsetWidth + 16 : 200;

            const prevBtn = carousel.querySelector('.prev');
            const nextBtn = carousel.querySelector('.next');

            // Asegúrate de quitar listeners anteriores reemplazando nodos
            const newPrevBtn = prevBtn.cloneNode(true);
            const newNextBtn = nextBtn.cloneNode(true);
            prevBtn.replaceWith(newPrevBtn);
            nextBtn.replaceWith(newNextBtn);

            newNextBtn.addEventListener('click', () => {
                list.scrollBy({left: itemWidth, behavior: 'smooth'});
            });

            newPrevBtn.addEventListener('click', () => {
                list.scrollBy({left: -itemWidth, behavior: 'smooth'});
            });

            // Arrastre con mouse
            let isDown = false;
            let startX;
            let scrollLeft;

            list.addEventListener('mousedown', (e) => {
                isDown = true;
                startX = e.pageX - list.offsetLeft;
                scrollLeft = list.scrollLeft;
            });

            list.addEventListener('mouseleave', () => isDown = false);
            list.addEventListener('mouseup', () => isDown = false);

            list.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - list.offsetLeft;
                const walk = (x - startX) * 2;
                list.scrollLeft = scrollLeft - walk;
            });
        });
    }

    document.addEventListener('livewire:navigated', function () {
        initCarousels();
    });

    Livewire.hook('message.processed', () => {
        initCarousels();
    });
</script>

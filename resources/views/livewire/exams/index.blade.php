@if(Auth::user()->current_team_id === null)
    <script>
        window.location.href = "{{ route('dashboard') }}?error=Selecciona%20una%20materia%20primero";
    </script>
@else
    <div x-data="tabsComponent({{json_encode(auth()->user()->status) }})" x-init="init()" class="container mx-auto p-4 bg-white shadow rounded-lg container-ask">
        <h2 class="text-2xl font-bold mb-4 primary-color title-ask-container">Examen: Selección de Preguntas</h2>
        <hr>


    <!-- Pestañas de Áreas -->
<div class="relative">
    <button id="scrollLeft" class="absolute left-0 top-1/2 transform -translate-y-1/2 z-10 w-[30px] h-[30px] bg-[#00000073] rounded-full">
        <i class="fa-solid fa-chevron-left text-white text-[11px]"></i>
    </button>

    <div id="scrollContainer" class="relative flex gap-2 my-2 whitespace-nowrap scroll-smooth overflow-hidden cursor-grab">
        <template x-for="area in areas" :key="area.id">
            <div class="flex-none">
                <div class="areas-buttons cat-1">
                    <button
                        @click="setActiveArea(area.id)"
                        x-text="area.name"
                        :class="activeArea === area.id ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-500'"
                        class="px-4 py-2 focus:outline-none">
                    </button>
                </div>
            </div>
        </template>
    </div>

    <button id="scrollRight" class="absolute right-0 top-1/2 transform -translate-y-1/2 w-[30px] h-[30px] bg-[#00000073] rounded-full">
        <i class="fa-solid fa-chevron-right text-white text-[11px]"></i>
    </button>
</div>

<!-- Pestañas de Categorías -->
<div class="relative">
    <button id="scrollLeftCat" class="absolute left-0 top-1/2 transform -translate-y-1/2 z-10 w-[30px] h-[30px] bg-[#00000073] rounded-full">
        <i class="fa-solid fa-chevron-left text-white text-[11px]"></i>
    </button>

    <div id="scrollContainerCat" class="relative flex gap-2 my-2 whitespace-nowrap scroll-smooth overflow-hidden cursor-grab">
        <template x-for="cat in activeCategories" :key="cat.id">
            <div class="flex-none">
                <div class="areas-buttons cat-2">
                    <button
                        @click="setActiveCategory(cat.id)"
                        x-text="cat.name"
                        :class="activeCategory === cat.id ? 'border-b-2 border-green-500 text-green-500' : 'text-gray-500'"
                        class="px-4 py-2 focus:outline-none">
                    </button>
                </div>
            </div>
        </template>
    </div>

    <button id="scrollRightCat" class="absolute right-0 top-1/2 transform -translate-y-1/2 w-[30px] h-[30px] bg-[#00000073] rounded-full">
        <i class="fa-solid fa-chevron-right text-white text-[11px]"></i>
    </button>
</div>

<!-- Pestañas de Tipos -->
<div class="relative">
    <button id="scrollLeftTipo" class="absolute left-0 top-1/2 transform -translate-y-1/2 z-10 w-[30px] h-[30px] bg-[#00000073] rounded-full">
        <i class="fa-solid fa-chevron-left text-white text-[11px]"></i>
    </button>

    <div id="scrollContainerTipo" class="relative flex gap-2 my-2 whitespace-nowrap scroll-smooth overflow-hidden cursor-grab">
        <template x-for="tipo in activeTipos" :key="tipo.id">
            <div class="flex-none">
                <div class="areas-buttons mb-0 cat-3">
                    <button
                        @click="setActiveTipo(tipo.id)"
                        x-text="tipo.name"
                        :class="activeTipo === tipo.id ? 'border-b-2 border-purple-500 text-purple-500' : 'text-gray-500'"
                        class="px-4 py-2 focus:outline-none">
                    </button>
                </div>
            </div>
        </template>
    </div>

    <button id="scrollRightTipo" class="absolute right-0 top-1/2 transform -translate-y-1/2 w-[30px] h-[30px] bg-[#00000073] rounded-full">
        <i class="fa-solid fa-chevron-right text-white text-[11px]"></i>
    </button>
</div>


        <!-- Sección de selección de universidad y cantidad para el tipo activo -->
        <template x-if="activeTipoObject">
            <div class="rounded mb-4 mt-45">
                <p class="font-semibold mb-2 primary-color title-ask-container">
                    Tipo seleccionado: <span x-text="activeTipoObject.name"></span>
                </p>
                <hr>

                <!-- Select de Universidad para el tipo activo -->
                <label class="block mb-2 form-container-ask">
                    <span class="text-sm font-semibold">Universidad (opcional):</span>
                    <select class="mt-1 block w-full border-gray-300 rounded"
                            x-model="questionSelections[activeTipo].university"
                    >
                        <option value="">Todas las Universidades</option>
                        <template x-for="uni in filteredUniversities" :key="uni.id">
                            <option :value="uni.id" x-text="uni.name"></option>
                        </template>
                    </select>
                </label>

                <!-- Mostrar cuántas preguntas hay disponibles -->
                <p class="text-sm text-gray-500 mb-2 m-25">
                    Preguntas disponibles: <span x-text="filteredQuestionsCount"></span>
                </p>

                <!-- Select de Cantidad de Preguntas -->
                <label class="block form-container-ask">
                    <span class="text-sm font-semibold">Cantidad de preguntas:</span>
                    <select class="mt-1 block w-full border-gray-300 rounded"
                            x-model.number="questionSelections[activeTipo].quantity"
                    >
                        <template x-for="option in getQuantityOptions" :key="option">
                            <option :value="option" x-text="option"></option>
                        </template>
                    </select>
                </label>
            </div>
        </template>

        <!-- Campo para seleccionar el tiempo del examen -->
        <div class="mb-4">
            <label class="block form-container-ask">
                <span class="text-sm font-semibold">Tiempo para realizar el examen (minutos):</span>
                <input type="number" min="1" x-model.number="timeExam" class="mt-1 block w-full border-gray-300 rounded placeholder-former">
            </label>
        </div>

        <!-- Campo para introducir el título del examen -->
        <div class="mb-4">
            <label class="block form-container-ask">
                <span class="text-sm font-semibold">Título del examen:</span>
                <input type="text" x-model="examTitle" class="mt-1 block w-full border-gray-300 rounded placeholder-former" placeholder="Ej: Examen de Prueba">
            </label>
        </div>

        <!-- Resumen y Botón para realizar el examen -->
        <div class="mt-6">
            <p class="mb-2 m-25 fw-500 color-text">
                Total de preguntas seleccionadas: <span x-text="totalSelected"></span>
            </p>
            @if(!$overLimit)
                <button
                    @click="realizarExamen"
                    :disabled="totalSelected === 0"
                    class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed boton-success-m">
                    Realizar Examen
                </button>
            @else
                <p class="text-red-800 font-bold">*** Supero La Cantidad De Examenes Permitidos Por Semana Desea Adquirir Premium ***</p>
                <x-pago/>
            @endif
        </div>

        <!-- Lista de resumen de selecciones -->
        <div class="mt-4" x-show="totalSelected > 0">
            <h3 class="text-lg font-semibold mb-2 primary-color title-ask-container resumen-s">Resumen de Selecciones</h3>
            <ul class="list-disc list-inside">
                <template x-for="area in areas" :key="area.id">
                    <template x-for="cat in area.categories" :key="cat.id">
                        <template x-for="tipo in cat.tipos" :key="tipo.id">
                            <template x-if="questionSelections[tipo.id].quantity > 0">
                                <li class="list-exams">
                                    <span x-text="area.name"></span> -
                                    <span x-text="cat.name"></span> -
                                    <span x-text="tipo.name"></span> -
                                    <span x-text="questionSelections[tipo.id].quantity "></span> preguntas
                                </li>
                            </template>
                        </template>
                    </template>
                </template>
            </ul>
        </div>
    </div>
@endif

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        function tabsComponent(userStatus) {
            console.log('este es el resultado de la query ' + userStatus+ ' este es su Tipo '+ typeof (userStatus)+ ' y esto es en bruto... ');
            return {
                areas: @json($areas),
                userStatus: userStatus,
                user: @json(auth()->user()),


                // Pestañas
                activeArea: null,
                activeCategory: null,
                activeTipo: null,

                // Estructura donde guardamos {quantity, university} por cada tipo
                questionSelections: {},

                // Campos extra
                timeExam: 60,
                examTitle: "Examen de Prueba",

                init() {
                    this.initializeSelections();
                    if (this.areas.length > 0) {
                        this.setActiveArea(this.areas[0].id);
                    }
                },

                // Devuelve las categorías del área activa
                get activeCategories() {
                    const area = this.areas.find(a => a.id === this.activeArea);
                    return area ? area.categories : [];
                },

                // Devuelve los tipos de la categoría activa
                get activeTipos() {
                    const cat = this.activeCategories.find(c => c.id === this.activeCategory);
                    return cat ? cat.tipos : [];
                },

                // Devuelve el objeto del tipo activo (o null si no hay)
                get activeTipoObject() {
                    return this.activeTipos.find(t => t.id === this.activeTipo) || null;
                },

                // Cantidad de preguntas filtradas según la universidad elegida
                get filteredQuestionsCount() {
                    const tipo = this.activeTipoObject;
                    if (!tipo) return 0;

                    const selectedUni = this.questionSelections[this.activeTipo].university;
                    if (!selectedUni) {
                        // Si no hay universidad seleccionada, todas las preguntas
                        return tipo.questions.length;
                    } else {
                        // Filtra las preguntas por la universidad
                        return tipo.questions.filter(q =>
                            q.universidades.some(u => u.id === parseInt(selectedUni))
                        ).length;
                    }
                },

                // Genera las opciones [0,5,10,...] según la cantidad de preguntas filtradas
                get getQuantityOptions() {
                    const tipo = this.activeTipoObject;
                    if (!tipo) return [0];

                    const selectedUni = this.questionSelections[this.activeTipo].university;
                    let filteredQuestions;
                    if (!selectedUni) {
                        filteredQuestions = tipo.questions;
                    } else {
                        filteredQuestions = tipo.questions.filter(q =>
                            q.universidades.some(u => u.id === parseInt(selectedUni))
                        );
                    }

                    const max = filteredQuestions.length;
                    const step = 5;
                    let options = [0];
                    if (max > 0) {
                        for (let i = step; i <= Math.min(max, 200); i += step) {
                            options.push(i);
                        }
                        if (max < 200 && options[options.length - 1] < max) {
                            options.push(max);
                        }
                    }
                    return options;
                },

                // Seleccionar área, categoría y tipo
                setActiveArea(areaId) {
                    this.activeArea = areaId;
                    this.setActiveCategory(this.activeCategories[0]?.id);
                },
                setActiveCategory(catId) {
                    this.activeCategory = catId;
                    const tipos = this.activeTipos;
                    if (tipos.length > 0) {
                        this.setActiveTipo(tipos[0].id);
                    }
                },
                setActiveTipo(tipoId) {
                    this.activeTipo = tipoId;
                    // no reseteamos la universidad porque puede querer persistir
                    //this.questionSelections[tipoId].university = "";
                },

                // Inicializamos las selecciones: { quantity: 0, university: "" } para cada tipo
                initializeSelections() {
                    this.areas.forEach(area => {
                        area.categories.forEach(cat => {
                            cat.tipos.forEach(tipo => {
                                this.questionSelections[tipo.id] = {
                                    quantity: 0,
                                    university: ""
                                };
                            });
                        });
                    });
                },

                // Devuelve las universidades únicas asociadas al tipo activo
                get filteredUniversities() {
                    const tipo = this.activeTipoObject;
                    if (!tipo) return [];
                    const universityMap = new Map();
                    tipo.questions.forEach(question => {
                        question.universidades.forEach(u => {
                            if (!universityMap.has(u.id)) {
                                universityMap.set(u.id, { id: u.id, name: u.name });
                            }
                        });
                    });
                    return Array.from(universityMap.values());
                },

                // Suma total de preguntas
                get totalSelected() {
                    return Object.values(this.questionSelections)
                        .reduce((acc, val) => acc + (parseInt(val.quantity) || 0), 0);
                },

                // Botón "Realizar Examen"
                realizarExamen() {
                    if (this.totalSelected === 0) {
                        alert('Debes seleccionar al menos una pregunta.');
                        return;
                    }

                    // Definir el límite según el estado del usuario: si status == 1, límite de 200; de lo contrario, 10
                    const limit = this.userStatus === 1 ? 200 : 10;
                    if (this.totalSelected > limit) {
                        alert(`El total de preguntas no puede exceder ${limit}.`);
                        return;
                    }

                    // Estructurar questionSelections en un array
                    const questionSelectionsArray = Object.entries(this.questionSelections)
                        .filter(([typeId, sel]) => sel.quantity > 0)
                        .map(([typeId, sel]) => ({
                            typeId: parseInt(typeId),
                            quantity: sel.quantity,
                            university: sel.university ? parseInt(sel.university) : null
                        }));

                    // Payload final
                    const payload = {
                        questionSelections: questionSelectionsArray,
                        time_exam: this.timeExam,
                        title: this.examTitle
                    };

                    console.log("Payload para el backend:", payload);

                    fetch('{{ route('examenes.create') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(payload)
                    })
                        .then(response => response.json())
                        .then(data => {
                            if (data.examen) {
                                window.location.href = `/examenes/${data.examen}`;
                            } else {
                                alert('Error al crear el examen.');
                            }
                        })
                        .catch(error => {
                            console.error('Error al enviar el examen:', error);
                        });
                }
            };
        }

        document.addEventListener("DOMContentLoaded", function () {
        function setupScroll(containerId, leftButtonId, rightButtonId) {
            const scrollContainer = document.getElementById(containerId);
            const scrollLeft = document.getElementById(leftButtonId);
            const scrollRight = document.getElementById(rightButtonId);

            let isDown = false;
            let startX;
            let scrollLeftPosition;

            // Función para verificar y actualizar la visibilidad de los botones
            function updateButtons() {
                const maxScrollLeft = scrollContainer.scrollWidth - scrollContainer.clientWidth;
                const hasScrollableContent = maxScrollLeft > 0;

                // Mostrar u ocultar botones según si hay contenido desplazable
                scrollLeft.classList.toggle("hidden", !hasScrollableContent || scrollContainer.scrollLeft <= 0);
                scrollRight.classList.toggle("hidden", !hasScrollableContent || scrollContainer.scrollLeft >= maxScrollLeft);
            }

            // Eventos para el desplazamiento con botones
            scrollLeft.addEventListener("click", () => {
                scrollContainer.scrollBy({ left: -100, behavior: "smooth" });
            });

            scrollRight.addEventListener("click", () => {
                scrollContainer.scrollBy({ left: 100, behavior: "smooth" });
            });

            // Eventos para el arrastre con el mouse
            scrollContainer.addEventListener("mousedown", (e) => {
                isDown = true;
                scrollContainer.classList.add("cursor-grabbing");
                startX = e.pageX - scrollContainer.offsetLeft;
                scrollLeftPosition = scrollContainer.scrollLeft;
            });

            scrollContainer.addEventListener("mouseleave", () => {
                isDown = false;
                scrollContainer.classList.remove("cursor-grabbing");
            });

            scrollContainer.addEventListener("mouseup", () => {
                isDown = false;
                scrollContainer.classList.remove("cursor-grabbing");
            });

            scrollContainer.addEventListener("mousemove", (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - scrollContainer.offsetLeft;
                const walk = (x - startX) * 2; // Velocidad del desplazamiento
                scrollContainer.scrollLeft = scrollLeftPosition - walk;
            });

            // Verificar si hay contenido desplazable
            scrollContainer.addEventListener("scroll", updateButtons);

            // Verificar el estado de los botones después de que los elementos hayan sido renderizados
            setTimeout(updateButtons, 500);  // Usamos un timeout para esperar que Alpine.js renderice

            // Observar cambios en los elementos hijos (por ejemplo, con Alpine.js)
            const observer = new MutationObserver(updateButtons);
            observer.observe(scrollContainer, { childList: true, subtree: true });
        }

        // Aplicar la función a Áreas, Categorías y Tipos
        setupScroll("scrollContainer", "scrollLeft", "scrollRight");
        setupScroll("scrollContainerCat", "scrollLeftCat", "scrollRightCat");
        setupScroll("scrollContainerTipo", "scrollLeftTipo", "scrollRightTipo");
    });
    </script>
@endpush

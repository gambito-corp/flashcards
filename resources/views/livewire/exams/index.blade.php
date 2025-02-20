@if(Auth::user()->current_team_id === null)
    <script>
        window.location.href = "{{ route('dashboard') }}?error=Selecciona%20una%20materia%20primero";
    </script>
@else
    <div x-data="tabsComponent()" x-init="init()" class="container mx-auto p-4 bg-white shadow rounded-lg">
        <h2 class="text-2xl font-bold mb-4">Examen: Selección de Preguntas</h2>

        <!-- Pestañas de Áreas -->
        <div class="mb-4">
            <div class="flex border-b">
                <template x-for="area in areas" :key="area.id">
                    <button
                        @click="setActiveArea(area.id)"
                        x-text="area.name"
                        :class="activeArea === area.id ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-500'"
                        class="px-4 py-2 focus:outline-none">
                    </button>
                </template>
            </div>
        </div>

        <!-- Pestañas de Categorías del Área Activa -->
        <div class="mb-4">
            <div class="flex border-b">
                <template x-for="cat in activeCategories" :key="cat.id">
                    <button
                        @click="setActiveCategory(cat.id)"
                        x-text="cat.name"
                        :class="activeCategory === cat.id ? 'border-b-2 border-green-500 text-green-500' : 'text-gray-500'"
                        class="px-4 py-2 focus:outline-none">
                    </button>
                </template>
            </div>
        </div>

        <!-- Pestañas de Tipos de la Categoría Activa -->
        <div class="mb-4">
            <div class="flex border-b">
                <template x-for="tipo in activeTipos" :key="tipo.id">
                    <button
                        @click="setActiveTipo(tipo.id)"
                        x-text="tipo.name"
                        :class="activeTipo === tipo.id ? 'border-b-2 border-purple-500 text-purple-500' : 'text-gray-500'"
                        class="px-4 py-2 focus:outline-none">
                    </button>
                </template>
            </div>
        </div>

        <!-- Sección de selección de universidad y cantidad para el tipo activo -->
        <template x-if="activeTipoObject">
            <div class="p-4 border rounded mb-4">
                <p class="font-semibold mb-2">
                    Tipo seleccionado: <span x-text="activeTipoObject.name"></span>
                </p>

                <!-- Select de Universidad para el tipo activo -->
                <label class="block mb-2">
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
                <p class="text-sm text-gray-500 mb-2">
                    Preguntas disponibles: <span x-text="filteredQuestionsCount"></span>
                </p>

                <!-- Select de Cantidad de Preguntas -->
                <label class="block">
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
            <label class="block">
                <span class="text-sm font-semibold">Tiempo para realizar el examen (minutos):</span>
                <input type="number" min="1" x-model.number="timeExam" class="mt-1 block w-full border-gray-300 rounded">
            </label>
        </div>

        <!-- Campo para introducir el título del examen -->
        <div class="mb-4">
            <label class="block">
                <span class="text-sm font-semibold">Título del examen:</span>
                <input type="text" x-model="examTitle" class="mt-1 block w-full border-gray-300 rounded" placeholder="Ej: Examen de Prueba">
            </label>
        </div>

        <!-- Resumen y Botón para realizar el examen -->
        <div class="mt-6">
            <p class="mb-2">
                Total de preguntas seleccionadas: <span x-text="totalSelected"></span>
            </p>
            <button
                @click="realizarExamen"
                :disabled="totalSelected === 0"
                class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed">
                Realizar Examen
            </button>
        </div>

        <!-- Lista de resumen de selecciones -->
        <div class="mt-4" x-show="totalSelected > 0">
            <h3 class="text-lg font-semibold mb-2">Resumen de Selecciones</h3>
            <ul class="list-disc list-inside">
                <template x-for="area in areas" :key="area.id">
                    <template x-for="cat in area.categories" :key="cat.id">
                        <template x-for="tipo in cat.tipos" :key="tipo.id">
                            <template x-if="questionSelections[tipo.id].quantity > 0">
                                <li>
                                    <span x-text="area.name"></span> -
                                    <span x-text="cat.name"></span> -
                                    <span x-text="tipo.name"></span> -
                                    <span x-text="questionSelections[tipo.id].quantity"></span> preguntas
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
        function tabsComponent() {
            return {
                areas: @json($areas),

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
                    if (this.totalSelected > 20) {
                        alert('El total de preguntas no puede exceder 20.');
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
    </script>
@endpush

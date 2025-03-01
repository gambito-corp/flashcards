@if(Auth::user()->current_team_id === null)
    <script>
        window.location.href = "{{ route('dashboard') }}?error=Selecciona%20una%20materia%20primero";
    </script>
@else
    <div x-data="examComponent()" x-init="init()" class="container mx-auto p-4 relative">
        <div class="max-w-3xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden container-ask container-exmans">
            <!-- Encabezado de la tarjeta -->
            <div class="bg-blue-500 text-white px-6 py-4 flex justify-between items-center header-examns">
                <!-- Título dinámico: si ya se envió el examen, se muestra examTitle; de lo contrario, "Realizar Examen" -->
                <h4 class="text-xl font-semibold" x-text="examSubmitted ? examTitle : 'Realizar Examen'"></h4>
                <!-- Tiempo restante solo si no se ha enviado el examen -->
                <div class="text-lg font-semibold " x-show="!examSubmitted">
                    Tiempo restante: <span x-text="formattedTime"></span>
                </div>
            </div>

            <!-- Cuerpo de la tarjeta -->
            <div class=" tarjeta-box">
                <template x-for="(question, index) in paginatedQuestions" :key="question.id">
                    <div class="mb-6 mb-45">
                        <!-- Título de la pregunta con badge -->
                        <h5 class="flex items-center text-lg font-semibold m-25">
                            <span class="inline-block bg-gray-200 text-gray-500 rounded-full px-3 py-1 mr-3 number-question">
                                <span x-text="(currentPage - 1) * questionsPerPage + index + 1"></span>
                            </span>
                            <span class="text-ask" x-text="question.content"></span>
                            
                        </h5>
                        <hr>

                        <!-- Opciones de la pregunta -->
                        <ul class="mt-2 space-y-2 choise-ask">
                            <template x-for="option in question.options" :key="option.id">
                                <li class="p-3 border rounded cursor-pointer"
                                    :class="getOptionClass(question.id, option.id)"
                                    @click="!examSubmitted ? selectAnswer(question.id, option.id) : null"
                                >
                                    <span x-text="option.content"></span>
                                </li>
                            </template>
                        </ul>

                        <!-- Medios: se muestran después de enviar el examen, debajo de las opciones -->
                        <template x-if="examSubmitted && question.media_iframe">
                            <div class="mt-2 m-25 mt-25">
                                <div x-html="question.media_iframe"></div>
                            </div>
                           
                        </template>
                
                        <template x-if="examSubmitted && !question.media_iframe && question.media_url">
                            <div class="mt-2 m-25 mt-25">
                                <iframe :src="getEmbedUrl(question.media_url)" class="w-full" height="315" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                            </div>
                        </template>
                        <template x-if="examSubmitted && !question.media_iframe && !question.media_url && question.media_type">
                            <div class="mt-2">
                                <p x-text="question.media_type"></p>
                            </div>
                        </template>

                        <!-- Explicación:
                             Se muestra si el examen está enviado,
                             existe question.explanation,
                             y la respuesta es incorrecta O no se respondió -->
                        <template x-if="examSubmitted
                                        && question.explanation
                                        && ( !selectedAnswers[question.id]
                                             || !correctAnswers.includes(selectedAnswers[question.id]) )">
                            <div class="mt-2">
                                <strong class="text-red-500">*<span x-text="question.explanation"></span>*</strong>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Controles de paginación (1 pregunta por página) -->
                <div class="flex justify-between items-center mt-6 m-25">
                
                    <div class="buttons-pagination">
                    <button @click="prevPage()"
                            :disabled="currentPage === 1"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded disabled:opacity-50 boton-success-m button-c3">
                        Anterior
                    </button>
                  
                    <button @click="nextPage()"
                            :disabled="currentPage === totalPages"
                            class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded disabled:opacity-50 boton-success-m button-c2">
                        Siguiente
                    </button>

</div>

    <!-- Navegación con cuadrados numerados -->
    <div class="flex flex-wrap gap-2 mt-4  buttons-numbers">
                    <template x-for="(question, index) in questions" :key="question.id">
                        <div @click="currentPage = index + 1"
                             class="w-10 h-10 flex items-center justify-center border cursor-pointer rounded buttons-nv"
                             :class="getSquareClass(question)">
                            <span x-text="index + 1"></span>
                        </div>
                    </template>
                </div>
                </div>

                <hr>
                <div class="text-sm">
                        Página <span x-text="currentPage"></span> de <span x-text="totalPages"></span>
                    </div>
            
            </div>

            <!-- Pie de la tarjeta -->
            <div class="px-6 py-4 bg-gray-100 text-right">
                <!-- Botón para enviar examen si aún no se envió -->
                <template x-if="!examSubmitted">
                    <button class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded boton-success-m button-c2"
                            @click="submitExam()">
                        Enviar Examen
                    </button>
                </template>
                <!-- Una vez enviado, mostrar score y botón para ir al Home -->
                <template x-if="examSubmitted">
                    <div class="results-exam">
                        <p class="mt-4 font-semibold text-lg">Puntuación: <span x-text="score"></span>/100</p>
                        <a href="{{ route('dashboard') }}" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded inline-block mt-4 boton-success-m button-c2">
                            Ir al Home
                        </a>
                    </div>
                </template>
            </div>
        </div>

        <!-- Cuadrado flotante para el tiempo restante (oculto al enviar) -->
        <div x-show="!examSubmitted" class="fixed top-4 right-4 bg-blue-500 text-white p-4 rounded shadow-lg " x-cloak>
            <div class="text-lg font-bold mb-1">Tiempo restante</div>
            <div class="text-2xl font-extrabold" x-text="formattedTime"></div>
        </div>
    </div>
@endif

@push('scripts')
    <script>
        function examComponent() {
            return {
                // Preguntas y examId
                questions: @json($examen->questions),
                examId: {{ $examen->id }},

                selectedAnswers: {},
                correctAnswers: [],
                score: 0,
                examSubmitted: false,

                // Ajustamos el tiempo en segundos
                remainingTime: {{ $examen->time_limit * 60 }},
                timerInterval: null,

                // Paginación
                currentPage: 1,
                questionsPerPage: 1,

                // Título del examen
                examTitle: "{{ $examen->title }}",

                init() {
                    this.startTimer();
                },

                getEmbedUrl(url) {
                    if (url.includes("youtube.com/watch")) {
                        let videoId = url.split("v=")[1].split("&")[0];
                        return "https://www.youtube.com/embed/" + videoId;
                    } else if (url.includes("youtu.be/")) {
                        let videoId = url.split("youtu.be/")[1].split("?")[0];
                        return "https://www.youtube.com/embed/" + videoId;
                    }
                    return url;
                },

                get formattedTime() {
                    let minutes = Math.floor(this.remainingTime / 60);
                    let seconds = this.remainingTime % 60;
                    return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
                },

                get paginatedQuestions() {
                    const start = (this.currentPage - 1) * this.questionsPerPage;
                    return this.questions.slice(start, start + this.questionsPerPage);
                },

                get totalPages() {
                    return Math.ceil(this.questions.length / this.questionsPerPage);
                },

                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.currentPage++;
                    }
                },
                prevPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                    }
                },

                startTimer() {
                    this.timerInterval = setInterval(() => {
                        if (this.remainingTime > 0) {
                            this.remainingTime--;
                        } else {
                            clearInterval(this.timerInterval);
                            if (!this.examSubmitted) {
                                this.submitExam();
                            }
                        }
                    }, 1000);
                },

                selectAnswer(questionId, optionId) {
                    this.selectedAnswers[questionId] = optionId;
                },

                submitExam() {
                    if (this.examSubmitted) return;

                    const payload = {
                        exam_id: this.examId,
                        respuestas: this.selectedAnswers
                    };

                    fetch('{{ route('examenes.evaluar') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(payload)
                    })
                        .then(response => response.json())
                        .then(data => {
                            this.examSubmitted = true;
                            this.score = data.puntuacion;
                            this.correctAnswers = data.respuestas_correctas;
                            clearInterval(this.timerInterval);

                            // REINICIAR PAGINACIÓN A LA PRIMERA PREGUNTA
                            this.currentPage = 1;
                        })
                        .catch(error => {
                            console.error('Error en la evaluación:', error);
                        });
                },

                // Clase para cada OPCIÓN de la pregunta
                getOptionClass(questionId, optionId) {
                    if (!this.examSubmitted) {
                        // Durante el examen
                        return this.selectedAnswers[questionId] === optionId
                            ? "bg-blue-500 text-white"
                            : "bg-white";
                    } else {
                        // Después de enviar
                        if (this.correctAnswers.includes(optionId)) {
                            return "bg-green-500 text-white";
                        } else if (this.selectedAnswers[questionId] === optionId) {
                            return "bg-red-500 text-white";
                        } else {
                            return "bg-white";
                        }
                    }
                },

                // Clase para el cuadradito de navegación
                getSquareClass(question) {
                    // Antes de enviar: si la pregunta está contestada => azul, si no => blanco
                    if (!this.examSubmitted) {
                        if (this.selectedAnswers[question.id]) {
                            return "bg-blue-500 text-white border-blue-500";
                        } else {
                            return "bg-white text-gray-800 border-gray-300";
                        }
                    } else {
                        // Después de enviar: correcto => verde, incorrecto => rojo, sin respuesta => gris
                        const answer = this.selectedAnswers[question.id];
                        if (!answer) {
                            return "bg-gray-400 text-gray-800 border-gray-400";
                        }
                        if (this.correctAnswers.includes(answer)) {
                            return "bg-green-500 text-white border-green-500";
                        } else {
                            return "bg-red-500 text-white border-red-500";
                        }
                    }
                }
            };
        }
    </script>
@endpush

@if(Auth::user()->current_team_id === null)
    <script>
        window.location.href = "{{ route('dashboard') }}?error=Selecciona%20una%20materia%20primero";
    </script>
@else
    <div x-data="examComponent()" x-init="init()" class="container mx-auto p-4 relative">
        <div class="max-w-3xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
            <!-- Encabezado -->
            <div class="bg-blue-500 text-white px-6 py-4 flex justify-between items-center">
                <h4 class="text-xl font-semibold" x-text="examSubmitted ? examTitle : 'Realizar Examen'"></h4>
                <div class="text-lg font-semibold" x-show="!examSubmitted">
                    Tiempo restante: <span x-text="formattedTime"></span>
                </div>
            </div>

            <!-- Preguntas/Paginación -->
            <div>
                <template x-for="(question, index) in paginatedQuestions" :key="question.id">
                    <div class="mb-6">
                        <h5 class="flex items-center text-lg font-semibold">
                            <span class="inline-block bg-gray-200 text-gray-500 rounded-full px-3 py-1 mr-3">
                                <span x-text="(currentPage - 1) * questionsPerPage + index + 1"></span>
                            </span>
                            <span x-text="question.content"></span>
                        </h5>
                        <hr>
                        <ul class="mt-2 space-y-2">
                            <template x-for="option in question.options" :key="option.id">
                                <li class="p-3 border rounded cursor-pointer"
                                    :class="getOptionClass(question.id, option.id)"
                                    @click="!examSubmitted ? selectAnswer(question.id, option.id) : null"
                                >
                                    <span x-text="option.content"></span>
                                </li>
                            </template>
                        </ul>
                        <!-- Explicación incorrecta -->
                        <template x-if="examSubmitted && question.explanation && (
                            !selectedAnswers[question.id] ||
                            !correctAnswers.includes(selectedAnswers[question.id])
                        )">
                            <div class="mt-2">
                                <strong class="text-red-500">*</strong>
                                <span x-text="question.explanation"></span>
                                <strong class="text-red-500">*</strong>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Paginación -->
                <div class="flex justify-between items-center mt-6">
                    <div>
                        <button @click="prevPage()" :disabled="currentPage === 1"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded disabled:opacity-50">
                            Anterior
                        </button>
                        <button @click="nextPage()" :disabled="currentPage === totalPages"
                                class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded disabled:opacity-50">
                            Siguiente
                        </button>
                    </div>
                    <!-- Navegación con cuadrados -->
                    <div class="flex flex-wrap gap-2 mt-4">
                        <template x-for="(question, index) in questions" :key="question.id">
                            <div @click="currentPage = index + 1"
                                 class="w-10 h-10 flex items-center justify-center border cursor-pointer rounded"
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

            <!-- Footer -->
            <div class="px-6 py-4 bg-gray-100 text-right">
                <template x-if="!examSubmitted">
                    <button type="button"
                            class="bg-green-500 hover:bg-green-600 text-white font-bold py-2 px-4 rounded"
                            @click="submitExam()">
                        Enviar Examen
                    </button>
                </template>
                <template x-if="examSubmitted">
                    <div>
                        <p class="mt-4 font-semibold text-lg">Puntuación: <span x-text="score"></span>/100</p>
                        <a href="{{ route('dashboard') }}"
                           class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded inline-block mt-4">
                            Ir al Home
                        </a>
                    </div>
                </template>
            </div>
        </div>

        <!-- Flotante de tiempo -->
        <div x-show="!examSubmitted" class="fixed bottom-4 right-4 bg-white shadow-lg rounded p-4" x-cloak>
            <div class="text-base font-bold mb-1">Tiempo restante</div>
            <div class="text-2xl font-extrabold" x-text="formattedTime"></div>
        </div>
    </div>
@endif

@push('scripts')
    <script>
        function examComponent() {
            return {
                questions: @json($examen['questions']),
                examId: {{ $examen['examId'] ?? 0 }},
                selectedAnswers: {},
                correctAnswers: @json(collect($examen['questions'])->pluck('correct_option_id')->filter()->values()),
                score: 0,
                examSubmitted: false,
                remainingTime: {{ $examen['examTime'] * 60 }},
                timerInterval: null,
                currentPage: 1,
                questionsPerPage: 1,
                examTitle: "{{ $examen['examTitle'] }}",

                init() {
                    this.startTimer();
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
                    if (this.currentPage < this.totalPages) this.currentPage++;
                },
                prevPage() {
                    if (this.currentPage > 1) this.currentPage--;
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

                    let totalCorrectas = 0;
                    let respuestasCorrectas = [];

                    this.questions.forEach(q => {
                        respuestasCorrectas.push(q.correct_option_id);
                        if (parseInt(this.selectedAnswers[q.id]) === parseInt(q.correct_option_id)) {
                            totalCorrectas++;
                        }
                    });

                    this.score = this.questions.length > 0
                        ? Math.round((totalCorrectas / this.questions.length) * 100)
                        : 0;

                    this.examSubmitted = true;
                    this.correctAnswers = respuestasCorrectas;
                    clearInterval(this.timerInterval);
                    this.currentPage = 1;

                    // Enviar respuestas a Livewire para guardado persistente
                    if (window.Livewire) {
                        window.Livewire.dispatch('guardarExamen', {
                            respuestas: this.selectedAnswers,
                            score: this.score,
                            exam_id: this.examId,
                        });
                    } else if (this.$wire) {
                        this.$wire.guardarExamen({
                            respuestas: this.selectedAnswers,
                            score: this.score,
                            exam_id: this.examId,
                        });
                    }
                },

                getOptionClass(questionId, optionId) {
                    if (!this.examSubmitted) {
                        return this.selectedAnswers[questionId] === optionId
                            ? "bg-blue-500 text-white"
                            : "bg-white";
                    } else {
                        if (this.correctAnswers.includes(optionId)) {
                            return "bg-green-500 text-white";
                        } else if (this.selectedAnswers[questionId] === optionId) {
                            return "bg-red-500 text-white";
                        } else {
                            return "bg-white";
                        }
                    }
                },

                getSquareClass(question) {
                    if (!this.examSubmitted) {
                        if (this.selectedAnswers[question.id]) {
                            return "bg-blue-500 text-white border-blue-500";
                        } else {
                            return "bg-white text-gray-800 border-gray-300";
                        }
                    } else {
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

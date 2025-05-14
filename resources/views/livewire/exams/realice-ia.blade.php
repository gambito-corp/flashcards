@if(Auth::user()->current_team_id === null)
    <script>
        window.location.href = "{{ route('dashboard') }}?error=Selecciona%20una%20materia%20primero";
    </script>
@else
    <div x-data="examComponent()" x-init="init()" class="container mx-auto p-4 relative">
        <div class="max-w-7xl mx-auto  overflow-hidden">
            <!-- Encabezado -->
            <div class="bg-[#5b8080] p-[30px] text-white flex justify-between items-center rounded-t-[20px]">
                <h4 class="text-[17px] md:text-xl font-semibold" x-text="examSubmitted ? examTitle : 'Realizar Examen'"></h4>
                <div class="text-[17px] md:text-xl font-semibold" x-show="!examSubmitted">
                    Tiempo restante: <span x-text="formattedTime"></span>
                </div>
            </div>

            <!-- Preguntas/Paginación -->
             <div class="p-[40px] bg-white rounded-b-[20px]">
            <div>
                <template x-for="(question, index) in paginatedQuestions" :key="question.id">
                    <div class="mb-6">
                        <h5 class="flex items-center flex-wrap text-lg font-semibold">
                            <span class="inline-block  text-gray-500 rounded-full px-3 py-1 mr-3">
                                <span x-text="(currentPage - 1) * questionsPerPage + index + 1" class=" w-[35px] h-[35px] flex justify-center items-center text-white bg-[var(--accent-color)] rounded-full"></span>
                            </span>
                            <span x-text="question.content" class="text-[17px] md:text-[18px] font-medium rounded-full"></span>
                        </h5>
                        <hr>
                        <ul class="mt-2 space-y-2 flex flex-wrap md:gap-[20px] gap-[15px]">
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
                            <div class="mt-4">
                                <strong class="text-red-500">*</strong>
                                <span class="text-[15px]" x-text="question.explanation"></span>
                                <strong class="text-red-500">*</strong>
                            </div>
                        </template>
                    </div>
                </template>

                <!-- Paginación -->
                <div class="flex justify-between items-center mt-6  flex-wrap mb-8">
                    <div class="flex gap-5">
                        <button @click="prevPage()" :disabled="currentPage === 1"
                                class="bg-[#5b8080] text-white px-[35px] py-[10px] text-[14px] md:text-[16px] font-semibold rounded rounded disabled:opacity-50">
                            Anterior
                        </button>
                        <button @click="nextPage()" :disabled="currentPage === totalPages"
                                class="bg-[var(--primary-color)] text-white px-[35px] py-[10px] text-[14px] md:text-[16px] font-semibold rounded disabled:opacity-50">
                            Siguiente
                        </button>
                    </div>
                    <!-- Navegación con cuadrados -->
                    <div class="flex flex-wrap gap-2 mt-4">
                        <template x-for="(question, index) in questions" :key="question.id">
                            <div @click="currentPage = index + 1"
                                 class="w-10 h-10 flex items-center justify-center border cursor-pointer rounded border-[#f1f1f1]"
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
</div>
            <!-- Footer -->
            <div class="px-6 py-4 text-right">
                <template x-if="!examSubmitted">
                    <button type="button"
                            class="bg-[#157b80] py-[10px] px-[35px] rounded-[8px] text-white font-semibold"
                            @click="submitExam()">
                        Enviar Examen
                    </button>
                </template>
                <template x-if="examSubmitted">
                    <div class="flex justify-between">
                        <p class="mt-4 font-semibold text-lg">Puntuación: <span x-text="score"></span>/100</p>
                        <a href="{{ route('dashboard') }}"
                           class="bg-[#195b81] py-[10px] px-[35px] rounded-[8px] text-white rounded font-semibold">
                            Ir al Home
                        </a>
                    </div>
                </template>
            </div>
        </div>

        <!-- Flotante de tiempo -->
        <div x-show="!examSubmitted" class="flex justify-center items-center gap-[30px] md:flex-row w-full md:w-auto md:top-[88px] top-auto bottom-0 md:bottom-auto right-0 md:right-[20px] bg-[var(--accent-color)] md:rounded-[10px] rounded-[0px] text-white text-center p-[20px] fixed" x-cloak>
            <div class="text-base font-bold md:mb-1">Tiempo restante</div>
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
                            ? "bg-[#157b80] text-white"
                            : "bg-white";
                    } else {
                        if (this.correctAnswers.includes(optionId)) {
                            return "bg-[#157b80] text-white";
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
                            return "bg-[#157b80] text-white border-blue-500";
                        } else {
                            return "bg-white text-gray-800 border-gray-300";
                        }
                    } else {
                        const answer = this.selectedAnswers[question.id];
                        if (!answer) {
                            return "bg-[#f1f1f1] text-gray-800 border-gray-400";
                        }
                        if (this.correctAnswers.includes(answer)) {
                            return "bg-[#157b80] text-white border-green-500";
                        } else {
                            return "bg-red-500 text-white border-red-500";
                        }
                    }
                }
            };
        }
        
    </script>
@endpush

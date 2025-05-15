<div class="relative">
    <!-- @dump('soy yo...') -->
    <!-- Temporizador en la esquina superior derecha fuera de la card -->
    <div wire:poll.1s="decrementTimer" class="md:w-auto w-full text-center justify-center fixed flex gap-[10px] top-auto bottom-0 md:bottom-auto md:top-[100px] font-semibold right-auto md:right-4 bg-[#5b8080] text-white px-4 py-2 rounded shadow-lg z-10">
        Tiempo restante: {{ $formattedTime }}
    </div>

    <!-- Card de la pregunta -->
    <div class="max-w-7xl w-full mx-auto mt-3 md:mt-7  rounded-lg mb-5">
        <h2 class="text-xl text-white font-bold bg-[#5b8080] p-[25px] md:p-[30px] rounded-[20px_20px_0px_0px]">{{ $examTitle }}</h2>

        @foreach($paginatedQuestions as $question)
            <div class="mb-6  rounded p-8 md:p-10 bg-white rounded-[0px_0px_20px_20px]">
                <h3 class="font-medium mb-5">Pregunta: {{ $question['content'] }}</h3>
                <hr>
                <div class="flex flex-wrap gap-4 mb-8">
                    @foreach($question['options'] as $option)
                        @php
                            $esModoCorreccion = $mostrar_correccion ?? false;
                            $seleccionada = $selectedAnswers[$question['id']] ?? null;
                            $esCorrecta = $option['id'] == $question['correct_option_id'];
                            $esSeleccionada = $seleccionada == $option['id'];
                            $correccion = $correcciones[$question['id']] ?? null;
                        @endphp

                        <button
                            wire:click="selectAnswer({{ $question['id'] }}, {{ $option['id'] }})"
                            @if($esModoCorreccion) disabled @endif
                            class="flex flex-wrap px-4 py-3 border rounded text-left transition text-base md:text-[15px]
                @if($esModoCorreccion)
                    @if($esCorrecta)
                        bg-[#157b80] text-white border-[#157b80]
                    @elseif($esSeleccionada)
                        bg-red-600 text-white border-red-600
                    @else
                        bg-white
                    @endif
                @else
                    {{ $esSeleccionada ? 'bg-[#5b8080] text-white border-[#5b8080]' : 'bg-white hover:bg-[#5b8080] hover:text-white' }}
                @endif
            ">
                            {{ $option['content'] }}
                        </button>
                    @endforeach
                </div>
                @if($mostrar_correccion && isset($correcciones[$question['id']]) && !$correcciones[$question['id']]['es_correcta'])
                    <div class="mt-2 text-sm text-red-700 mb-5">
                        <strong>Explicación:</strong>
                        <p class="text-red-700">{{ $question['explanation'] }}</p>
                    </div>
                @endif
<hr>

                <div class="flex justify-between items-center mt-4 flex-wrap">
                    <!-- Botones Anterior/Siguiente a la izquierda -->
                    <div class="flex gap-2">
                        <button
                            wire:click="prevPage"
                            @disabled($currentPage == 1)
                            class="bg-[#5b8080] tw-button text-white font-semibold text-base rounded disabled:opacity-50">
                            Anterior
                        </button>
                        <button
                            wire:click="nextPage"
                            @disabled($currentPage == $totalPages)
                            class="bg-[#195b81] tw-button text-white font-semibold text-base rounded disabled:opacity-50">
                            Siguiente
                        </button>
                    </div>


                    <div class="flex justify-between items-center mt-4 gap-2 flex-wrap">
                        @php $showArrows = $totalPages > 10; @endphp
                            <!-- Navegación izquierda (solo si hay más de 10 preguntas) -->
                        @if($showArrows)
                            <div class="flex gap-1">
                                <button
                                    wire:click="goToPage(1)"
                                    @disabled($currentPage == 1)
                                    class="w-8 h-8 flex items-center justify-center border border-gray-300 bg-white text-gray-700 rounded-none disabled:opacity-50 font-bold text-xs">
                                    «
                                </button>
                                <button
                                    wire:click="prevPage"
                                    @disabled($currentPage == 1)
                                    class="w-8 h-8 flex items-center justify-center border border-gray-300 bg-white text-gray-700 rounded-none disabled:opacity-50 font-bold text-xs">
                                    ‹
                                </button>
                            </div>
                        @else
                            <div class="w-0"></div>
                        @endif

                        <!-- Paginación central -->
                        <div class="flex gap-1">
                            @foreach($this->getVisiblePages() as $page)
                                @php
                                    // Calcula el ID de la pregunta asociada a esta página
                                    $questionIndex = $page - 1;
                                    $question = $examen['questions'][$questionIndex];
                                    $correccion = $correcciones[$question['id']] ?? null;
                                    $color = 'bg-white text-gray-700 hover:bg-blue-50';
                                    if($mostrar_correccion && $correccion) {
                                        $color = $correccion['es_correcta'] ? 'bg-[#5b8080] text-white font-bold border-green-600' : 'bg-red-600 text-white font-bold border-red-600';
                                    } elseif($currentPage == $page) {
                                        $color = 'bg-[#5b8080] text-white font-bold border-[#5b8080]';
                                    }
                                @endphp
                                <button
                                    wire:click="goToPage({{ $page }})"
                                    class="w-8 h-8 flex items-center justify-center border border-gray-300 rounded-none text-xs {{ $color }}">
                                    {{ $page }}
                                </button>
                            @endforeach

                        </div>

                        <!-- Navegación derecha (solo si hay más de 10 preguntas) -->
                        @if($showArrows)
                            <div class="flex gap-1">
                                <button
                                    wire:click="nextPage"
                                    @disabled($currentPage == $totalPages)
                                    class="w-8 h-8 flex items-center justify-center border border-[#f1f1f1] bg-white text-gray-700 rounded-none disabled:opacity-50 font-bold text-xs">
                                    ›
                                </button>
                                <button
                                    wire:click="goToPage({{ $totalPages }})"
                                    @disabled($currentPage == $totalPages)
                                    class="w-8 h-8 flex items-center justify-center border border-[#f1f1f1] bg-white text-gray-700 rounded-none disabled:opacity-50 font-bold text-xs">
                                    »
                                </button>
                            </div>
                        @else
                            <div class="w-0"></div>
                        @endif
                    </div>


                </div>

                @endforeach

            </div>
            <div class="flex gap-4 mt-6 md:justify-end justify-center">
                @if($mostrar_correccion)
                    <a href="{{ route('dashboard') }}"
                       class="tw-button rounded font-semibold button-primary transition">
                        Volver al inicio
                    </a>
                @else
                    <button
                        wire:click="$set('showFinishModal', true)"
                        class="tw-button rounded font-semibold button-primary transition">
                        Finalizar Examen
                    </button>
                @endif
                <button
                    wire:click="$set('showExitModal', true)"
                    class="tw-button button-secundary rounded font-semibold transition">
                    Salir
                </button>
            </div>
    </div>
    @if($showFinishModal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-lg shadow-lg max-w-xl w-full p-6">
                <h3 class=" mb-4 tw-title">Finalizar Examen</h3>
                <hr>
                @php
                    $total = count($examen['questions']);
                    $respondidas = count($selectedAnswers);
                @endphp
                @if($respondidas < $total)
                    <p class="mb-4">
                        Has respondido <span class="font-bold">{{ $respondidas }}</span> de <span
                            class="font-bold">{{ $total }}</span> preguntas.<br>
                        ¿Seguro que quieres terminar?
                    </p>
                @else
                    <p class="mb-4">
                        ¡Respondiste todas las preguntas!<br>
                        ¿Finalizar examen?
                    </p>
                @endif
                <div class="flex justify-end gap-4 mt-6">
                    <button
                        wire:click="$set('showFinishModal', false)"
                        class="rounded tw-button  w-1/2 button-secundary text-white font-semibold">
                        Cancelar
                    </button>
                    <button
                        wire:click="finalizarExamen"
                        class="rounded tw-button w-1/2 button-primary text-white font-semibold">
                        Finalizar Examen
                    </button>
                </div>
            </div>
        </div>
    @endif
    @if($showExitModal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-lg shadow-lg max-w-xl w-full p-6">
                <h3 class="font-bold mb-4 tw-title">Salir del Examen</h3>
                <hr>
                <p class="mb-4">
                    ¿Seguro que quieres salir? Perderás tu progreso.
                </p>
                <div class="flex justify-end gap-4 mt-6">
                    <button
                        wire:click="$set('showExitModal', false)"
                        class="rounded tw-button  w-1/2 button-secundary text-white font-semibold">
                        Cancelar
                    </button>
                    <button
                        wire:click="salirExamen"
                        class="rounded tw-button w-1/2 button-primary text-white font-semibold">
                        Salir
                    </button>
                </div>
            </div>
        </div>
    @endif
    @if($mostrar_correccion)
        <div class="mb-6 p-4 rounded text-lg font-bold flex items-center justify-between
    {{ $score > 50 ? 'text-green-800 bg-green-100 border border-green-300' : 'text-red-800 bg-red-100 border border-red-300' }}">
            <span>Puntuación: {{ $score }}/100</span>
        </div>

    @endif
</div>

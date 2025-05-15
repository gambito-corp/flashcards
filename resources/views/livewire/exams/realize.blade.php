<div class="relative">
    @dump('soy yo...')
    <!-- Temporizador en la esquina superior derecha fuera de la card -->
    <div wire:poll.1s="decrementTimer" class="fixed top-4 right-4 bg-blue-600 text-white px-4 py-2 rounded shadow-lg">
        Tiempo restante: {{ $formattedTime }}
    </div>

    <!-- Card de la pregunta -->
    <div class="max-w-5xl w-full mx-auto mt-16 p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-4">{{ $examTitle }}</h2>

        @foreach($paginatedQuestions as $question)
            <div class="mb-6 border rounded p-4 shadow-sm">
                <h3 class="font-semibold mb-3">Pregunta: {{ $question['content'] }}</h3>
                <div class="flex flex-row gap-4 mb-4">
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
                            class="flex-1 px-4 py-3 border rounded text-left transition
                @if($esModoCorreccion)
                    @if($esCorrecta)
                        bg-green-600 text-white border-green-600
                    @elseif($esSeleccionada)
                        bg-red-600 text-white border-red-600
                    @else
                        bg-white
                    @endif
                @else
                    {{ $esSeleccionada ? 'bg-blue-600 text-white border-blue-600' : 'bg-white hover:bg-blue-100' }}
                @endif
            ">
                            {{ $option['content'] }}
                        </button>
                    @endforeach
                </div>
                @if($mostrar_correccion && isset($correcciones[$question['id']]) && !$correcciones[$question['id']]['es_correcta'])
                    <div class="mt-2 text-sm text-red-700">
                        <strong>Explicación:</strong>
                        <p class="text-red-700">{{ $question['explanation'] }}</p>
                    </div>
                @endif


                <div class="flex justify-between items-center mt-4">
                    <!-- Botones Anterior/Siguiente a la izquierda -->
                    <div class="flex gap-2">
                        <button
                            wire:click="prevPage"
                            @disabled($currentPage == 1)
                            class="bg-gray-300 px-4 py-2 rounded disabled:opacity-50">
                            Anterior
                        </button>
                        <button
                            wire:click="nextPage"
                            @disabled($currentPage == $totalPages)
                            class="bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-50">
                            Siguiente
                        </button>
                    </div>


                    <div class="flex justify-between items-center mt-4 gap-2">
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
                                        $color = $correccion['es_correcta'] ? 'bg-green-600 text-white font-bold border-green-600' : 'bg-red-600 text-white font-bold border-red-600';
                                    } elseif($currentPage == $page) {
                                        $color = 'bg-blue-600 text-white font-bold border-blue-600';
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
                                    class="w-8 h-8 flex items-center justify-center border border-gray-300 bg-white text-gray-700 rounded-none disabled:opacity-50 font-bold text-xs">
                                    ›
                                </button>
                                <button
                                    wire:click="goToPage({{ $totalPages }})"
                                    @disabled($currentPage == $totalPages)
                                    class="w-8 h-8 flex items-center justify-center border border-gray-300 bg-white text-gray-700 rounded-none disabled:opacity-50 font-bold text-xs">
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
            <div class="flex gap-4 mt-6 justify-end">
                @if($mostrar_correccion)
                    <a href="{{ route('dashboard') }}"
                       class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                        Volver al inicio
                    </a>
                @else
                    <button
                        wire:click="$set('showFinishModal', true)"
                        class="bg-blue-600 text-white px-6 py-2 rounded font-bold hover:bg-blue-700 transition">
                        Finalizar Examen
                    </button>
                @endif
                <button
                    wire:click="$set('showExitModal', true)"
                    class="bg-gray-300 text-gray-800 px-6 py-2 rounded font-bold hover:bg-gray-400 transition">
                    Salir
                </button>
            </div>
    </div>
    @if($showFinishModal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
                <h3 class="text-lg font-bold mb-4">Finalizar Examen</h3>
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
                        class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-800">
                        Cancelar
                    </button>
                    <button
                        wire:click="finalizarExamen"
                        class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white font-bold">
                        Finalizar Examen
                    </button>
                </div>
            </div>
        </div>
    @endif
    @if($showExitModal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50">
            <div class="bg-white rounded-lg shadow-lg max-w-md w-full p-6">
                <h3 class="text-lg font-bold mb-4">Salir del Examen</h3>
                <p class="mb-4">
                    ¿Seguro que quieres salir? Perderás tu progreso.
                </p>
                <div class="flex justify-end gap-4 mt-6">
                    <button
                        wire:click="$set('showExitModal', false)"
                        class="px-4 py-2 rounded bg-gray-200 hover:bg-gray-300 text-gray-800">
                        Cancelar
                    </button>
                    <button
                        wire:click="salirExamen"
                        class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white font-bold">
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

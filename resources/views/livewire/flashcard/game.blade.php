<div class="max-w-xl mx-auto p-6 bg-gray-50 min-h-screen"
     x-data="{
         showAnswer: false,
         slideDirection: null,
         startX: 0,
         threshold: 50,
         // Función para detectar el inicio del touch
         touchStart(e) {
             this.startX = e.touches[0].clientX;
         },
         // Función para detectar el final del touch y calcular el swipe
         touchEnd(e) {
             let diff = e.changedTouches[0].clientX - this.startX;
             if(diff < -this.threshold) {
                 // Swipe a la izquierda → respuesta correcta
                 this.slideDirection = 'left';
                 setTimeout(() => {
                     $wire.markCorrect();
                     this.slideDirection = null;
                     this.showAnswer = false;
                 }, 400);
             } else if(diff > this.threshold) {
                 // Swipe a la derecha → respuesta incorrecta
                 this.slideDirection = 'right';
                 setTimeout(() => {
                     $wire.markIncorrect();
                     this.slideDirection = null;
                     this.showAnswer = false;
                 }, 400);
             }
         }
     }"
     x-on:touchstart="touchStart($event)"
     x-on:touchend="touchEnd($event)">
    <h1 class="text-3xl font-bold text-center mb-6 text-indigo-700">Juego de Flashcards</h1>

    @if($cards->isNotEmpty())
        @php
            $currentCard = $cards[$currentIndex] ?? null;
        @endphp

        @if($currentCard)
            <div class="bg-white rounded-lg shadow-lg p-6">
                <!-- Contenedor 3D con perspectiva y efecto slide -->
                <div class="relative perspective-1000 w-full h-64"
                     :class="{
                         'slide-left': slideDirection === 'left',
                         'slide-right': slideDirection === 'right'
                     }">
                    <!-- Contenedor interno que rota en 3D -->
                    <div class="w-full h-full transition-transform duration-500 transform-style-3d"
                         :class="{ 'rotate-y-180': showAnswer }">
                        <!-- Cara frontal (pregunta) -->
                        <div class="absolute w-full h-full backface-hidden bg-white p-4 border rounded shadow flex items-center justify-center">
                            <h2 class="text-2xl font-semibold text-gray-800 text-center">
                                {{ $currentCard->pregunta }}
                            </h2>
                        </div>
                        <!-- Cara trasera (respuesta) -->
                        <div class="absolute w-full h-full backface-hidden bg-white p-4 border rounded shadow flex items-center justify-center transform rotate-y-180">
                            <h2 class="text-2xl font-semibold text-gray-800 text-center">
                                {{ $currentCard->respuesta }}
                            </h2>
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="mt-6">
                    <!-- Botón "Revelar Respuesta" (visible si no se ha revelado) -->
                    <div x-show="!showAnswer">
                        <button wire:click="revealAnswer"
                                @click="showAnswer = true"
                                class="w-full py-2 px-4 bg-blue-600 text-white rounded hover:bg-blue-700 transition duration-200">
                            Revelar Respuesta
                        </button>
                    </div>
                    <!-- Botones Correcto/Incorrecto (visible si se ha revelado) -->
                    <div x-show="showAnswer" class="flex space-x-4">
                        <button @click="
                                slideDirection = 'left';
                                setTimeout(() => {
                                    $wire.markCorrect();
                                    slideDirection = null;
                                    showAnswer = false;
                                }, 400);
                            "
                                class="w-1/2 py-2 px-4 bg-green-600 text-white rounded hover:bg-green-700 transition duration-200">
                            ✓ Correcto
                        </button>
                        <button @click="
                                slideDirection = 'right';
                                setTimeout(() => {
                                    $wire.markIncorrect();
                                    slideDirection = null;
                                    showAnswer = false;
                                }, 400);
                            "
                                class="w-1/2 py-2 px-4 bg-red-600 text-white rounded hover:bg-red-700 transition duration-200">
                            ✗ Incorrecto
                        </button>
                    </div>
                </div>
            </div>
        @else
            <p class="text-center text-gray-700">No se encontró la flashcard actual.</p>
        @endif
    @else
        <p class="text-center text-gray-700">No hay flashcards seleccionadas para el juego.</p>
    @endif
</div>

@push('styles')
    <style>
        /* Perspectiva 3D */
        .perspective-1000 {
            perspective: 1000px;
        }
        /* Permite que el contenedor interno mantenga su estilo 3D */
        .transform-style-3d {
            transform-style: preserve-3d;
        }
        /* Oculta la cara que no se está mostrando */
        .backface-hidden {
            backface-visibility: hidden;
        }
        /* Rota la tarjeta 180° en el eje Y */
        .rotate-y-180 {
            transform: rotateY(180deg);
        }
        /* Efectos de slide para el contenedor */
        .slide-left {
            transform: translateX(-100%);
            opacity: 0;
            transition: transform 0.4s ease, opacity 0.4s ease;
        }
        .slide-right {
            transform: translateX(100%);
            opacity: 0;
            transition: transform 0.4s ease, opacity 0.4s ease;
        }
    </style>
@endpush

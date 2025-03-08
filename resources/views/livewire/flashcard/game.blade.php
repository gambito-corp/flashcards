<div class="max-w-xl mx-auto p-6 container-full "
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
     <h1 class="text-3xl font-bold mb-6 text-indigo-700 primary-color title-ask-container">Juego de Flashcards</h1>
    <hr>


    @if($cards->isNotEmpty())
        @php
            $currentCard = $cards[$currentIndex] ?? null;
        @endphp

        @if($currentCard)
            <div class="pb-10 m-25">
                <!-- Contenedor 3D con perspectiva y efecto slide -->
                <div class="relative perspective-1000 w-full "
                     :class="{
                         'slide-left': slideDirection === 'left',
                         'slide-right': slideDirection === 'right'
                     }">
                    <!-- Contenedor interno que rota en 3D -->
                    <div class="w-full h-full transition-transform duration-500 transform-style-3d"
                         :class="{ 'rotate-y-180': showAnswer }">
                        <!-- Cara frontal (pregunta) -->
                        <div class="absolute w-full  backface-hidden bg-white p-10 rounded-[20px] flex items-center justify-start bg-[#195b81] box-answer p-10">
                            <div class=" box-content-answer">                               <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyNC4yLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0ic3ZnMiIgeG1sbnM6c3ZnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyINCgkgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCAxMjYuMiAxMjYuMiINCgkgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMTI2LjIgMTI2LjI7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiNGRkZGRkY7fQ0KPC9zdHlsZT4NCjxnPg0KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik02Ni44LDEyNi4yYy0yLjUsMC00LjksMC03LjQsMGMtMC4zLTAuMS0wLjYtMC4yLTEtMC4yYy04LjgtMC42LTE3LjItMi45LTI1LTcuMkMxNS43LDEwOSw0LjgsOTQuMSwxLDc0LjENCgkJYy0wLjUtMi40LTAuNy00LjktMS03LjNjMC0yLjUsMC00LjksMC03LjRjMC4xLTAuNCwwLjItMC43LDAuMi0xLjFjMC42LTguOCwyLjktMTcuMiw3LjItMjQuOUMxOSwxMi44LDM2LjgsMS42LDYwLjQsMC4xDQoJCUM3MC43LTAuNSw4MC41LDEuNCw4OS43LDZjMjIuNSwxMS4yLDM0LjcsMjkuNCwzNi40LDU0LjVjMC44LDExLjUtMS44LDIyLjQtNy40LDMyLjVjLTkuOCwxNy43LTI0LjgsMjguNS00NC43LDMyLjMNCgkJQzcxLjYsMTI1LjcsNjkuMiwxMjUuOSw2Ni44LDEyNi4yeiBNMTE2LjMsNjMuMkMxMTYuNCwzMy44LDkyLjYsMTAsNjMuMiw5LjlDMzMuOCw5LjgsOS45LDMzLjYsOS45LDYzLjENCgkJQzkuOCw5Mi40LDMzLjYsMTE2LjIsNjMsMTE2LjNDOTIuMywxMTYuNCwxMTYuMyw5Mi42LDExNi4zLDYzLjJ6Ii8+DQoJPHBhdGggY2xhc3M9InN0MCIgZD0iTTYzLjEsMzEuN2M5LjUsMCwxNy45LDcsMTkuNSwxNi4zYzEuNiw5LjctMy45LDE5LTEzLjEsMjJjLTEuMSwwLjQtMS41LDAuOC0xLjQsMmMwLjEsMi4xLDAsNC4yLDAsNi4zDQoJCWMwLDMtMi4xLDUuMi01LDUuMWMtMi44LDAtNC45LTIuMS00LjktNS4xYzAtNCwwLTgsMC0xMS45YzAtMi44LDEuNy00LjgsNC41LTVjMy4zLTAuMyw2LjEtMS4yLDguMS0zLjljMi40LTMuMiwyLjgtNi43LDEtMTAuMw0KCQljLTEuOC0zLjctNS01LjYtOS4yLTUuNWMtNSwwLjItOC43LDMuNy05LjQsOC43Yy0wLjEsMC40LTAuMSwwLjktMC4xLDEuM2MtMC4zLDIuOC0yLjQsNC43LTUuMSw0LjZjLTIuNy0wLjEtNC44LTIuMi00LjctNQ0KCQljMC4yLTcsMy4yLTEyLjUsOS4xLTE2LjRDNTUuNywzMi43LDU5LjIsMzEuNyw2My4xLDMxLjd6Ii8+DQoJPHBhdGggY2xhc3M9InN0MCIgZD0iTTYzLjMsODcuMmMzLjQsMC4xLDYuMSwzLDYsNi40Yy0wLjEsMy40LTMsNi02LjMsNS45Yy0zLjQtMC4xLTYuMS0zLTYtNi40QzU3LjEsODkuNyw1OS45LDg3LjEsNjMuMyw4Ny4yeiIvPg0KPC9nPg0KPC9zdmc+DQo="/>
                            <h2 class="text-xl font-[500] text-gray-800 text-white">
                                {{ $currentCard->pregunta }}
                            </h2>
                            <div class="cursor-pointer">
                                @if ($currentCard->imagen)
                                    <img class="img-answer" src="{{ Storage::disk('s3')->temporaryUrl($currentCard->imagen, now()->addMinutes(10)) }}" alt="Imagen de la pregunta" onclick="openModal(this)">
                                @elseif($currentCard->url)
                                    <img class="img-answer" src="{{$currentCard->url}}" alt="Imagen de la pregunta" onclick="openModal(this)" />
                                @else

                                @endif
                            </div>
                            </div>
                       
                            <div x-show="!showAnswer" class="button-revelar">
                        <button wire:click="revealAnswer"
                                @click="showAnswer = true"
                                class="w-full py-2 px-4 bg-blue-600 text-white rounded hover:bg-blue-700 transition duration-200 bg-white w-full">
                            Revelar Respuesta
                        </button>
                    </div>
                        </div>
                        <!-- Cara trasera (respuesta) -->
                        <div class="absolute w-full backface-hidden bg-white p-4  rounded  flex items-center justify-start transform rotate-y-180 answer-c p-10 ">
                        <div class="box-content-answer">
                            <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyNC4yLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0ic3ZnMiIgeG1sbnM6c3ZnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyINCgkgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCAxMjYuMiAxMjYuMiINCgkgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMTI2LjIgMTI2LjI7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiMxOTVCODE7fQ0KPC9zdHlsZT4NCjxnPg0KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik04NC4xLDQxLjhjMy45LDAuNyw4LDEsMTEuNywyLjNjMTYuOCw1LjUsMjYuNywxNy4yLDI5LjQsMzQuN2MxLjEsNy41LDAsMTQuNy0zLDIxLjcNCgkJYy0wLjIsMC41LTAuMywxLjItMC4yLDEuOGMxLjEsNi4zLDIuMywxMi42LDMuNCwxOC45YzAuNSwzLTEuMyw0LjktNC4zLDQuNGMtNS41LTAuOS0xMC45LTEuOS0xNi4zLTNjLTIuMi0wLjQtNC4yLTAuNC02LjQsMC41DQoJCWMtMjQsOC45LTUwLjItNS42LTU1LjQtMzAuN2MtMC42LTIuNy0wLjctNS40LTEtOC4xYy0yLjctMC4zLTUuNS0wLjUtOC4yLTEuMWMtMi42LTAuNi01LjItMS42LTcuNy0yLjNjLTAuOC0wLjItMS43LTAuNC0yLjUtMC4zDQoJCWMtNi4xLDEuMS0xMi4yLDIuMi0xOC40LDMuNGMtMy4yLDAuNi01LjItMS4zLTQuNi00LjVDMS44LDczLDMsNjYuOCw0LjEsNjAuNWMwLjEtMC42LDAtMS4zLTAuMi0xLjhDLTQuNiwzOC40LDMuNywxNS4zLDIzLjIsNS4yDQoJCUM0Ny4zLTcuMyw3Nyw2LjYsODIuOSwzMy4xQzgzLjUsMzYuMSw4My43LDM5LjEsODQuMSw0MS44eiBNMTE3LjcsMTE3LjhjMC0wLjQsMC0wLjYtMC4xLTAuOGMtMC44LTQuNC0xLjYtOC45LTIuNS0xMy4zDQoJCWMtMC40LTIuMi0wLjMtNC4xLDAuNy02LjJjMi41LTUuNSwzLjMtMTEuNCwyLjYtMTcuNGMtMS43LTE0LjktMTIuMy0yNi43LTI2LjktMzAuMWMtMi43LTAuNi01LjUtMS04LjEtMC44DQoJCWMtNC4zLDE4LjctMTUuNywzMC0zNC4zLDM0LjNjMC4xLDEuNywwLjEsMy40LDAuNCw1LjFjMy4yLDIzLjMsMjcuNywzNi42LDQ5LjEsMjYuOWMxLTAuNSwyLjMtMC41LDMuNS0wLjUNCgkJYzEuNiwwLjEsMy4yLDAuNSw0LjgsMC44QzExMC40LDExNi41LDExNCwxMTcuMiwxMTcuNywxMTcuOHogTTguMyw3Ni4yQzksNzYuMSw5LjUsNzYsOS45LDc1LjljNC42LTAuOCw5LjMtMS43LDEzLjktMi42DQoJCWMxLjQtMC4zLDIuNy0wLjEsNCwwLjVjNS42LDIuNiwxMS41LDMuNSwxNy43LDIuOWMxOS42LTEuOSwzMy44LTE5LjYsMzEuMi0zOUM3NC4xLDE4LjYsNTYuNSw1LjIsMzcuMyw3LjkNCgkJYy0yMi41LDMuMi0zNiwyNy0yNy4xLDQ3LjhjMSwyLjIsMS4yLDQuMywwLjcsNi42QzEwLDY2LjgsOS4yLDcxLjQsOC4zLDc2LjJ6Ii8+DQoJPHBhdGggY2xhc3M9InN0MCIgZD0iTTEwMS4zLDEwMWMwLDItMSwzLjMtMi42LDMuN2MtMS43LDAuNC0zLjMtMC4zLTQtMmMtMS0yLjQtMi00LjgtMi45LTcuM2MtMC4zLTAuNy0wLjYtMS4xLTEuNS0xDQoJCWMtNC4yLDAtOC40LDAtMTIuNiwwYy0wLjgsMC0xLjIsMC4zLTEuNSwxLjFjLTAuOSwyLjMtMS44LDQuNS0yLjcsNi44Yy0wLjksMi4yLTIuOCwzLjEtNC43LDIuM2MtMS45LTAuOC0yLjYtMi43LTEuOC00LjkNCgkJYzQuNS0xMS4zLDkuMS0yMi43LDEzLjYtMzRjMC42LTEuNSwxLjctMi41LDMuNC0yLjVjMS43LDAsMi44LDEsMy40LDIuNWM0LjUsMTEuMyw5LDIyLjYsMTMuNSwzMy45DQoJCUMxMDEuMSwxMDAuMSwxMDEuMiwxMDAuNywxMDEuMywxMDF6IE04OC41LDg3LjRjLTEuNS0zLjgtMy03LjUtNC42LTExLjRjLTEuNiw0LTMsNy43LTQuNSwxMS40QzgyLjUsODcuNCw4NS40LDg3LjQsODguNSw4Ny40eiIvPg0KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik01OS4yLDU0LjVjMSwwLjksMS45LDEuNywyLjgsMi42YzEuNSwxLjYsMS41LDMuNywwLjEsNS4xYy0xLjQsMS40LTMuNCwxLjQtNS0wLjFjLTAuOS0wLjktMS43LTEuOS0yLjUtMi44DQoJCWMtMTAuOSw3LjItMjMuMSwzLjUtMjktNC41Yy02LjItOC40LTUuMy0yMC4xLDIuMS0yNy4zYzcuNi03LjMsMTkuMi04LDI3LjQtMS42QzYzLDMyLDY2LjEsNDQuMSw1OS4yLDU0LjV6IE01NCw0OS4zDQoJCWMzLjgtNS45LDIuMy0xMy45LTMuMy0xOC4yYy01LjgtNC40LTE0LTMuNy0xOC45LDEuOGMtNC44LDUuMy00LjYsMTMuNywwLjMsMTguOWM0LjUsNC42LDEyLjcsNS44LDE2LjksMi4zDQoJCWMtMC43LTAuNy0xLjUtMS40LTIuMi0yLjJjLTEuNS0xLjYtMS42LTMuNy0wLjMtNS4xYzEuNC0xLjUsMy41LTEuNSw1LjEsMC4xQzUyLjYsNDcuNiw1My4yLDQ4LjUsNTQsNDkuM3oiLz4NCjwvZz4NCjwvc3ZnPg0K"/>
                            <h2 class="text-xl font-[500] text-[#33333]">
                                {{ $currentCard->respuesta }}
                            </h2>
                            <div class="cursor-pointer">
                                @if ($currentCard->imagen_respuesta)
                                    <img class="img-answer" src="{{ Storage::disk('s3')->temporaryUrl($currentCard->imagen_respuesta, now()->addMinutes(10)) }}" alt="Imagen de la Respuesta" onclick="openModal(this)">
                                @elseif($currentCard->url)
                                    <img class="img-answer" src="{{$currentCard->url}}" alt="Imagen de la Respuesta" onclick="openModal(this)" />
                                @else

                                @endif
                            </div>
                        </div>
                    
                        
                                      <!-- Botones de acción -->
                <div class="mt-6 w-full">
                    <!-- Botón "Revelar Respuesta" (visible si no se ha revelado) -->
                  
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
                                class="w-1/2 py-2 px-4 bg-green-600 text-white rounded hover:bg-green-700 transition duration-200 boton-success-m">
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
                                class="w-1/2 py-2 px-4 bg-red-600 text-white rounded hover:bg-red-700 transition duration-200 button-incorrect">
                            ✗ Incorrecto
                        </button>
                    </div>
                </div>
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



@push('scripts')
<script>
// Función para abrir el modal
function openModal(imgElement) {
    var modal = document.getElementById("lightboxModal");
    var modalImg = document.getElementById("modalImage");
    modal.style.display = "flex"; // Mostramos el modal
    modalImg.src = imgElement.src; // Establecemos la imagen del modal
}

// Función para cerrar el modal
function closeModal() {
    var modal = document.getElementById("lightboxModal");
    modal.style.display = "none"; // Ocultamos el modal
}

</script>

@endpush
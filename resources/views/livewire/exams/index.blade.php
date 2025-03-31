<div>
    @if(Auth::user()->current_team_id === null)
        <script>
            window.location.href = "{{ route('dashboard') }}?error=Selecciona%20una%20materia%20primero";
        </script>
    @else
        <div class="container mx-auto p-4 bg-white shadow rounded-lg container-ask">
            <h2 class="text-2xl font-bold mb-4 primary-color title-ask-container">Examen: Selección de Preguntas</h2>
            <hr>


            <!-- Pestañas de Áreas -->
            <div class="relative">
                <button id="scrollLeft" class="absolute left-0 top-1/2 transform -translate-y-1/2 z-10 w-[30px] h-[30px] bg-[#00000073] rounded-full">
                    <i class="fa-solid fa-chevron-left text-white text-[11px]"></i>
                </button>

                <div id="scrollContainer" class="relative flex gap-2 my-2 whitespace-nowrap scroll-smooth overflow-hidden cursor-grab">
                    @forelse($areas as $area)
                        <div class="flex-none" wire:key="area-{{$area->id}}">
                            <div class="areas-buttons cat-1">
                                <button
                                    wire:click="setActiveArea({{ $area->id }})"
                                    class="px-4 py-2 focus:outline-none {{ $activeArea == $area->id ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-500' }}">
                                    {{$area->name}}
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="flex-none">
                            <div class="areas-buttons cat-1">
                                <button
                                    :class="activeArea === area.id ? 'border-b-2 border-blue-500 text-blue-500' : 'text-gray-500'"
                                    class="px-4 py-2 focus:outline-none">
                                    Sin Datos
                                </button>
                            </div>
                        </div>
                    @endforelse
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
                    @forelse($categorias as $categoria)
                        <div class="flex-none" wire:key="categoria-{{$categoria->id}}">
                            <div class="areas-buttons cat-2">
                                <button
                                    wire:click="setActiveCategoria({{ $categoria->id }})"
                                    class="px-4 py-2 focus:outline-none {{ $activeCategoria == $categoria->id ? 'border-b-2 border-green-500 text-green-500' : 'text-gray-500' }}">
                                    {{$categoria->name}}
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="flex-none">
                            <div class="areas-buttons cat-2">
                                <button
                                    class="px-4 py-2 focus:outline-none text-gray-500">
                                    Sin Datos
                                </button>
                            </div>
                        </div>
                    @endforelse
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
                    @forelse($tipos as $tipo)
                        <div class="flex-none" wire:key="tipo-{{$tipo->id}}">
                            <div class="areas-buttons mb-0 cat-3">
                                <button
                                    wire:click="setActiveTipo({{ $tipo->id }})"
                                    class="px-4 py-2 focus:outline-none {{ $activeTipo == $tipo->id ? 'border-b-2 border-purple-500 text-purple-500' : 'text-gray-500' }}">
                                    {{$tipo->name}}
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="flex-none">
                            <div class="areas-buttons mb-0 cat-3">
                                <button
                                    class="px-4 py-2 focus:outline-none text-gray-500">
                                    Sin Datos
                                </button>
                            </div>
                        </div>
                    @endforelse
                </div>

                <button id="scrollRightTipo" class="absolute right-0 top-1/2 transform -translate-y-1/2 w-[30px] h-[30px] bg-[#00000073] rounded-full">
                    <i class="fa-solid fa-chevron-right text-white text-[11px]"></i>
                </button>
            </div>



            <!-- Sección de selección de universidad y cantidad para el tipo activo -->
            @if($activeTipoObject)
                <div class="rounded mb-4 mt-45">
                    <p class="font-semibold mb-2 primary-color title-ask-container">
                        Tipo seleccionado: <span>{{ $activeTipoObject['name'] }}</span>
                    </p>
                    <hr>

                    <!-- Select de Universidad para el tipo activo -->
                    <label class="block mb-2 form-container-ask">
                        <span class="text-sm font-semibold">Universidad (opcional):</span>
                        <select class="mt-1 block w-full border-gray-300 rounded"
                                wire:model="questionSelections.{{ $activeTipo }}.university">
                            <option value="">Todas las Universidades</option>
                            @foreach($filteredUniversities as $uni)
                                <option value="{{ $uni['id'] }}">{{ $uni['name'] }}</option> <!-- Usar ID -->
                            @endforeach
                        </select>
                    </label>

                    <!-- Mostrar cuántas preguntas hay disponibles -->
                    <p class="text-sm text-gray-500 mb-2 m-25">
                        Preguntas disponibles: {{ $filteredQuestionsCount }}
                    </p>

                    <!-- Select de Cantidad de Preguntas -->
                    <label class="block form-container-ask">
                        <span class="text-sm font-semibold">Cantidad de preguntas:</span>
                        <select class="mt-1 block w-full border-gray-300 rounded"
                                wire:model="questionSelections.{{ $activeTipo }}.quantity">
                            @if($filteredQuestionsCount > 0)
                                @for($i = 1; $i <= min($filteredQuestionsCount, 100); $i++)
                                    <option value="{{ $i }}">{{ $i }}</option>
                                @endfor
                            @endif
                        </select>
                    </label>
                </div>
            @endif


            <!-- Campo para seleccionar el tiempo del examen -->
            <div class="mb-4">
                <label class="block form-container-ask">
                    <span class="text-sm font-semibold">Tiempo para realizar el examen (minutos):</span>
                    <input type="number" wire:model="timeExam"
                           class="mt-1 block w-full border-gray-300 rounded placeholder-former">
                </label>
            </div>

            <!-- Campo para introducir el título del examen -->
            <div class="mb-4">
                <label class="block form-container-ask">
                    <span class="text-sm font-semibold">Título del examen:</span>
                    <input type="text" wire:model="examTitle"
                           class="mt-1 block w-full border-gray-300 rounded placeholder-former"
                           placeholder="Ej: Examen de Prueba">
                </label>
            </div>

            <!-- Resumen y Botón para realizar el examen -->
            <div class="mt-6">
                <p class="mb-2 m-25 fw-500 color-text">
                    Total de preguntas seleccionadas: <span x-text="totalSelected"></span>
                </p>
                @if(!$overLimit)
                    <!-- Botón para enviar el formulario -->
                    <button wire:click.prevent="realizarExamen"
                            wire:loading.attr="disabled"
                            class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 disabled:opacity-50">
                        Realizar Examen
                    </button>
                    <div wire:loading.remove>
                        Total de preguntas seleccionadas: {{ $totalSelected }}
                    </div>
                @else
                    <p class="text-red-800 font-bold">*** Supero La Cantidad De Examenes Permitidos Por Semana Desea Adquirir Premium ***</p>
                    <x-pago/>
                @endif
            </div>
        </div>
    @endif
</div>

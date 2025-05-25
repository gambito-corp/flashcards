@if($cardsToShow->count())
<div class="relative overflow-x-hidden">
    <div class="flex gap-[15px] mb-2 flex-wrap">
        <input type="text"
            wire:model.live.debounce.300ms="searchTerms.{{ $tabId }}"
            placeholder="Buscar..."
            class="bg-[var(--background-color)] text-[15px] h-[50px] outline-none rounded-[5px] w-1/2 border-none search-input mb-5 md:mb-0"
            style="">

        @php
        $tabCardIds = $cardsToShow->pluck('id')->all();
        $allSelected = count(array_intersect($tabCardIds, $selectedCards)) === count($tabCardIds) && $tabCardIds;
        @endphp
        @if(!Auth::user()->hasAnyRole('root') && Auth::user()->status == 0)
        <div class="relative inline-block">
            <button type="button"
                wire:click="toggleSelectAllTab('{{ $tabId }}')"
                class="flex items-center justify-center gap-2 transition duration-300 rounded-[8px] text-[15px] font-medium px-[25px] py-[10px] text-white md:w-auto w-full
        {{ $allSelected ? 'bg-[#0e5d60]' : 'bg-[#157b80] hover:bg-[#0e5d60]' }}">

                {{-- Ícono dinámico --}}
                @if($allSelected)
                {{-- Icono X (deseleccionar) --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
                @else
                {{-- Icono Check (seleccionar) --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 13l4 4L19 7" />
                </svg>
                @endif

                <span>
                    {{ $allSelected ? 'Deseleccionar' : 'Seleccionar' }} {{ count($tabCardIds) }} flashcards
                </span>
            </button>
            <div
                class="absolute inset-0 bg-black/30 backdrop-blur-[1px] z-10 flex items-center justify-center rounded-lg pointer-events-auto h-max">
                <a href="{{route('planes')}}"
                    target="_blank"
                    class="h-px-4 h-10 py-2 z-10 md:px-6 md:py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-bold rounded shadow-lg hover:scale-105 transition flex items-center justify-center text-[13px] md:tex-base opacity:1 md:opacity-0 hover:opacity-100 absolute top-[-21px] w-full text-[11px] p-0 h-[31px] md:static md:top-auto md:w-auto  md:p-4 md:h-auto ">
                    🔒 Hazte PRO
                </a>
            </div>
        </div>
        @else
        <div class="relative inline-block">

            <button type="button"
                wire:click="toggleSelectAllTab('{{ $tabId }}')"
                class="flex items-center justify-center gap-2 transition duration-300 rounded-[8px] text-[15px] font-medium px-[25px] py-[10px] text-white md:w-auto w-full
        {{ $allSelected ? 'bg-[#0e5d60]' : 'bg-[#157b80] hover:bg-[#0e5d60]' }}">

                {{-- Ícono dinámico --}}
                @if($allSelected)
                {{-- Icono X (deseleccionar) --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M6 18L18 6M6 6l12 12" />
                </svg>
                @else
                {{-- Icono Check (seleccionar) --}}
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5 13l4 4L19 7" />
                </svg>
                @endif

                <span>
                    {{ $allSelected ? 'Deseleccionar' : 'Seleccionar' }} {{ count($tabCardIds) }} flashcards
                </span>
            </button>
        </div>
        @endif


    </div>
    @if(Auth::user()->hasAnyRole('root') || Auth::user()->status == 1)
    @else
    <div class="flex items-center gap-[10px] flex-wrap md:mt-0 mt-6 bg-[#e1eff7] p-3 rounded inline-flex">
      <a href="{{route('planes')}}"
                    target="_blank"
                    class="h-px-4 h-10 py-2 z-10 md:px-6 md:py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-bold rounded shadow-lg hover:scale-103 transition flex items-center justify-center text-[11px] h-[30px] md:tex-base w-full  p-0  md:static md:top-auto md:w-auto  md:p-4 ">
                    🔒 Hazte PRO
                </a> <span class="text-[15px]">Para desbloquear funciones de edición, borrado y otras opciones que te ayudaran con tu aprendizaje.</span> </div>
    @endif

    <div class="relative">
        <!-- Botones Izquierda/Derecha -->
        <div class="absolute top-[-5px] md:top-[-65px] right-0 flex flex-row gap-2">
            <button type="button" id="slide-left{{ $tabId }}"
                class="p-2 bg-[#f7f7f7] rounded-full hover:bg-gray-300">
                <!-- SVG izquierda -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 19l-7-7 7-7" />
                </svg>
            </button>
            <button type="button" id="slide-right{{ $tabId }}"
                class="p-2 rounded-full bg-[#f7f7f7] hover:bg-gray-300">
                <!-- SVG derecha -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none"
                    viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 5l7 7-7 7" />
                </svg>
            </button>
        </div>

        <!-- Slider Cards -->
        <div id="card-slider{{ $tabId }}" data-tab="{{ $tabId }}"
            class="flex space-x-4 overflow-x-auto py-2 flash-c-g gap-[10px] mt-5 w-full transition-all duration-300">

            @foreach($cardsToShow as $card)
            <div
                wire:click="toggleCard({{ $card->id }})"
                class="relative cursor-pointer box-flashcard-game flex-shrink-0 w-64 p-4 border rounded shadow transition duration-200
            hover:shadow-lg {{ in_array($card->id, $selectedCards) ? 'bg-green-100 border-green-400' : 'bg-white' }}">
                <div>
                    <h2 class="font-bold text-lg">{{ $card->pregunta }}</h2>
                </div>

                @if(Auth::user()->hasAnyRole('root') || Auth::user()->status == 1)
                <div class="flex justify-end space-x-2 absolute right-3 top-3">
                    <button
                        type="button"
                        wire:click.stop="editCard({{ $card->id }})"
                        class="px-2 py-1 bg-[#5b8080] text-white rounded hover:bg-[#fffff] hover:text[#5b8080] text-xs flex items-center gap-1"
                        title="Editar">
                        <img class="w-[17px]" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAMAAADXqc3KAAAAAXNSR0IB2cksfwAAAAlwSFlzAAAAnQAAAJ0Bj3LnbgAAAKVQTFRFAAAA////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////uP3jVQAAADd0Uk5TAA0zEQWU/P+rrf6WTIT6nmsSeE0y+c8TmAyubT700BRmg2xF9nn4ofuRSESHC8bECtQwxYZHLXliBKEAAADPSURBVHicbc9fC8FQGMfx58dwsSQp5g4jJSQX3v9bkOQKnRDbhZBo7Z9zhthz9lxs6/M9Pe2AtAEQyidnQzrBi3kwCkjmCs2LF6JyWALzyDzLd+1eBjtvAg6R9TD/g1G/VNV+0XIbW6TOWxuycascYj8AcyI7L3J+8LuH2nNQHy1Xnv9dkPs3aP4Jur9Dhichy1XIdBX6u+T/qXP6cxmaFax0l2Gw7i75niQMHWuhuwxjzCd+JJgTRsKOtl47XqedMF2WesD+SGxAs+eCo5oXGpptV/4P7iMAAAAASUVORK5CYII=" alt="Boton Eliminar">Editar
                    </button>
                    <button
                        type="button"
                        wire:click.stop="deleteCard({{ $card->id }})"
                        wire:confirm="¿Seguro que deseas eliminar esta flashcard?"
                        class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-xs flex items-center gap-1"
                        title="Eliminar">
                        <img class="w-[17px]" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAMAAADXqc3KAAAAAXNSR0IB2cksfwAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAMxQTFRFAAAA////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////OpLhCAAAAER0Uk5TAB6guwXf7sLeSv9QZd3A6/Kp1teomuXmmYv09Yp9BG0SvxNsXiEiXU8wMU5AP2AgDXR1C/aOy8puUgbIaGnH0blHk7WaBofeAAABGklEQVR4nHXRWUsDMRAA4BldkLbrg9SDUtpla+tRpIhHPZ70l+uTWLRIK15V67rWUrT64tZ9iYybjLskigMhk3xDQjII/wQmCcqUiH6BFYEAi8aEMCGFOIqmDFEYg62y9JelQIx/qrpAgo0fxr2TFEgAmMJ3A7I45Dtm8MWAORww5LBvQB57DIW3aR/AhkANKA6zTwzO66wH4MKDGlAa5O4Zyv38rQYLvcINw5LvXGlQ9dwLhpXu/LkGvJTAJQnwARIqz+W2BrW7SothtbN4psHa9XLz53c38ER7xyadxt9ex4b28C1oxLDdVs3g2GnVjmPYRc/1fbVddML0kUg6WHqcqGbCJqynRpd1v6u3dh/xMJr2iA7Mnv+Jb5RQaBkMZdeJAAAAAElFTkSuQmCC" alt="Boton Eliminar">Eliminar
                    </button>
                </div>
                @else
                <div class="flex justify-end  absolute right-3 top-3 gap-[10px] cursor-not-allowed">
                    <div class="bg-black/50 absolute  w-full h-full rounded-[5px]"></div>
                    <button
                        type="button"
                        class="px-2 py-1 bg-[#5b8080] text-white rounded hover:bg-[#fffff] hover:text[#5b8080] text-xs flex items-center gap-1 cursor-not-allowed"
                        title="Editar">
                        <img class="w-[17px]" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAMAAADXqc3KAAAAAXNSR0IB2cksfwAAAAlwSFlzAAAAnQAAAJ0Bj3LnbgAAAKVQTFRFAAAA////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////uP3jVQAAADd0Uk5TAA0zEQWU/P+rrf6WTIT6nmsSeE0y+c8TmAyubT700BRmg2xF9nn4ofuRSESHC8bECtQwxYZHLXliBKEAAADPSURBVHicbc9fC8FQGMfx58dwsSQp5g4jJSQX3v9bkOQKnRDbhZBo7Z9zhthz9lxs6/M9Pe2AtAEQyidnQzrBi3kwCkjmCs2LF6JyWALzyDzLd+1eBjtvAg6R9TD/g1G/VNV+0XIbW6TOWxuycascYj8AcyI7L3J+8LuH2nNQHy1Xnv9dkPs3aP4Jur9Dhichy1XIdBX6u+T/qXP6cxmaFax0l2Gw7i75niQMHWuhuwxjzCd+JJgTRsKOtl47XqedMF2WesD+SGxAs+eCo5oXGpptV/4P7iMAAAAASUVORK5CYII=" alt="Boton Eliminar">Editar
                    </button>
                    <button
                        type="button"
                        class="px-2 py-1 bg-red-500 text-white rounded hover:bg-red-600 text-xs flex items-center gap-1 cursor-not-allowed"
                        title="Eliminar">
                        <img class="w-[17px]" src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABgAAAAYCAMAAADXqc3KAAAAAXNSR0IB2cksfwAAAAlwSFlzAAAOxAAADsQBlSsOGwAAAMxQTFRFAAAA////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////OpLhCAAAAER0Uk5TAB6guwXf7sLeSv9QZd3A6/Kp1teomuXmmYv09Yp9BG0SvxNsXiEiXU8wMU5AP2AgDXR1C/aOy8puUgbIaGnH0blHk7WaBofeAAABGklEQVR4nHXRWUsDMRAA4BldkLbrg9SDUtpla+tRpIhHPZ70l+uTWLRIK15V67rWUrT64tZ9iYybjLskigMhk3xDQjII/wQmCcqUiH6BFYEAi8aEMCGFOIqmDFEYg62y9JelQIx/qrpAgo0fxr2TFEgAmMJ3A7I45Dtm8MWAORww5LBvQB57DIW3aR/AhkANKA6zTwzO66wH4MKDGlAa5O4Zyv38rQYLvcINw5LvXGlQ9dwLhpXu/LkGvJTAJQnwARIqz+W2BrW7SothtbN4psHa9XLz53c38ER7xyadxt9ex4b28C1oxLDdVs3g2GnVjmPYRc/1fbVddML0kUg6WHqcqGbCJqynRpd1v6u3dh/xMJr2iA7Mnv+Jb5RQaBkMZdeJAAAAAElFTkSuQmCC" alt="Boton Eliminar">Eliminar
                    </button>
                </div>
                @endif
            </div>
            @endforeach

        </div>

    </div>
</div>
@else
<p class="text-gray-600">No hay flashcards en esta sección.</p>
@endif

<script>
    function initSliderEvents() {
        document.querySelectorAll('[id^="card-slider"]').forEach((slider) => {
            const tabId = slider.getAttribute('data-tab') || '';
            const leftBtn = document.querySelector(`#slide-left${tabId}`);
            const rightBtn = document.querySelector(`#slide-right${tabId}`);
            const scrollAmount = 300;

            if (leftBtn && slider) {
                leftBtn.onclick = () => slider.scrollBy({
                    left: -scrollAmount,
                    behavior: 'smooth'
                });
            }
            if (rightBtn && slider) {
                rightBtn.onclick = () => slider.scrollBy({
                    left: scrollAmount,
                    behavior: 'smooth'
                });
            }
        });
    }

    document.addEventListener('DOMContentLoaded', initSliderEvents);

    document.addEventListener('livewire:init', () => {
        Livewire.hook('commit', () => {
            initSliderEvents();
        });
    });
</script>

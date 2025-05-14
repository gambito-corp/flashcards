@if($cardsToShow->count())
    <div class="relative overflow-x-hidden">
        <div class="flex gap-[15px] mb-2">
            <input type="text"
                   wire:model.live.debounce.300ms="searchTerms.{{ $tabId }}"
                   placeholder="Buscar..."
                   class="bg-[var(--background-color)] text-[15px] h-[50px] outline-none rounded-[5px] w-1/2 border-none search-input"
                   style=""
            >
            @php
                $tabCardIds = $cardsToShow->pluck('id')->all();
                $allSelected = count(array_intersect($tabCardIds, $selectedCards)) === count($tabCardIds) && $tabCardIds;
            @endphp
<button type="button"
    wire:click="toggleSelectAllTab('{{ $tabId }}')"
    class="flex items-center justify-center gap-2 transition duration-300 rounded-[8px] text-[15px] font-medium px-[25px] py-[10px] text-white
        {{ $allSelected ? 'bg-[#0e5d60]' : 'bg-[#157b80] hover:bg-[#0e5d60]' }}">
    
    {{-- Ícono dinámico --}}
    @if($allSelected)
        {{-- Icono X (deseleccionar) --}}
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
        </svg>
    @else
        {{-- Icono Check (seleccionar) --}}
        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
        </svg>
    @endif

    <span>
        {{ $allSelected ? 'Deseleccionar' : 'Seleccionar' }} {{ count($tabCardIds) }} flashcards
    </span>
</button>


        </div>

        <div class="flex space-x-4 overflow-x-hidden py-2 flash-c-g gap-[10px] mt-5 w-full"
             style="transform: translateX(-{{ $slidersScroll[$tabId] ?? 0 }}px); transition: all 0.3s;">
            @foreach($cardsToShow as $card)
                <div
                    wire:click="toggleCard({{ $card->id }})"
                    class="box-flashcard-game flex-shrink-0 w-64 p-4 border rounded shadow cursor-pointer transition duration-200
                        hover:shadow-lg {{ in_array($card->id, $selectedCards) ? 'bg-green-100 border-green-400' : 'bg-white' }}">
                    <h2 class="font-bold text-lg">{{ $card->pregunta }}</h2>
                </div>
            @endforeach
        </div>
        <div class="arrow-flash flex flex-row gap-2 mt-2">
            <button type="button" class="p-2 rounded-full hover:bg-gray-300"
                    wire:click="moveSlider('{{ $tabId }}', 'left')">
                <!-- SVG izquierda -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <button type="button" class="p-2 rounded-full hover:bg-gray-300"
                    wire:click="moveSlider('{{ $tabId }}', 'right')">
                <!-- SVG derecha -->
                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </div>
@else
    <p class="text-gray-600">No hay flashcards en esta sección.</p>
@endif

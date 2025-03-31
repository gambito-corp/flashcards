<div class="relative mb-4">
    <div class="flex gap-2 overflow-x-auto scroll-smooth"
         x-data="{
             scroll($el, direction) {
                 $el.scrollBy({ left: direction * 100, behavior: 'smooth' })
             }
         }">
        @foreach($options as $option)
            <button
                wire:key="{{ $option['id'] }}"
                wire:click="updatedSelected('{{ $option['id'] }}')"
                :class="{ 'border-blue-500 text-blue-500': '{{ $option['id'] }}' === $selected }"
                class="px-4 py-2 border-b-2 transition-colors whitespace-nowrap">
                {{ $option['name'] }}
            </button>
        @endforeach
    </div>
</div>

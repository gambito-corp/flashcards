<div class="max-w-7xl mx-auto bg-white rounded  p-8 mb-10 md:md-5 md:p-10 mt-5 w-full rounded-[20px] ">
    <h1 class="text-[var(--primary-color)] font-black text-[17px] md:text-[20px] mb-5">¿Qué quieres hacer?</h1>
    <hr>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <button wire:click="selectMode('normal')"
                class="p-[40px] rounded-[10px] bg-[#5b8080] text-white border-none">
            <span class="block text-base md:text-lg font-bold mb-2">Examen Normal</span>
            <span class="text-sm text-gray-600 text-white">Elige áreas y tipos, como siempre.</span>
        </button>
        <button wire:click="selectMode('usuario')"
                class="p-[40px] rounded-[10px] bg-[#ff6363] text-white border-none text-white">
            <span class="block text-base md:text-lg font-bold mb-2">Preguntas que más fallas</span>
            <span class="text-sm text-gray-600 text-white">Examen solo con tus preguntas más falladas.</span>
        </button>
        <button wire:click="selectMode('global')"
                class="p-[40px] rounded-[10px] bg-[#ff6363] text-white border-none text-white">
            <span class="block text-base md:text-lg font-bold mb-2">Preguntas más falladas (todos)</span>
            <span class="text-sm text-gray-600 text-white">Examen con las preguntas más falladas por todos los usuarios.</span>
        </button>
        <button wire:click="selectMode('ia')" class="p-[40px] rounded-[10px] bg-[#195b81] text-white border-none text-white" >
            <span class="block text-base md:text-lg font-bold mb-2">Examen IA</span>
            <span class="text-sm text-gray-600 text-white">Las preguntas las genera una IA.</span>
        </button>
 <div class="relative col-span-1 md:col-span-2">
    <button disabled
        class="p-[40px] rounded-[10px] bg-[#5b8080] text-white border-none w-full opacity-50 cursor-not-allowed">
        <span class="block text-base md:text-lg font-bold mb-2">Conócete a ti mismo</span>
        <span class="text-sm text-white">Métricas y análisis de tu rendimiento.</span>
    </button>

    <!-- Overlay -->
    <div class="absolute inset-0 bg-black/30 rounded-[10px] flex items-center justify-center pointer-events-none">
        <div class="text-center text-white">
            <div class="flex items-center justify-center gap-2 text-sm md:text-base font-semibold">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" stroke-width="2"
                     viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M13 16h-1v-4h-1m1-4h.01M12 2a10 10 0 100 20 10 10 0 000-20z" />
                </svg>
                Próximamente
            </div>
        </div>
    </div>
</div>

    </div>
</div>

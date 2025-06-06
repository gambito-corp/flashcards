<div class="max-w-7xl mx-auto bg-white rounded  p-8 mb-10 md:md-5 md:p-10 mt-5 w-full rounded-[20px] ">
    <h1 class="text-[var(--primary-color)] font-black text-[17px] md:text-[20px] mb-5">¿Qué quieres hacer?</h1>
    @if(session()->has('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{!! session('error') !!}</span>
            <span class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <svg class="fill-current h-6 w-6 text-red-500" role="button" xmlns="http://www.w3.org/2000/svg"
                     viewBox="0 0 20 20"><title>Close</title><path>
                </svg>
            </span>
        </div>
    @endif
    <hr>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <button
            wire:click="selectMode('normal')"
            class="p-[40px] rounded-[10px] bg-[#5b8080] text-white border-none">
            <span class="block text-base md:text-lg font-bold mb-2">Examen Normal</span>
            <span class="text-sm text-gray-600 text-white">Elige áreas y tipos, como siempre.</span>
        </button>
        @if(!Auth::user()->hasAnyRole('root') && Auth::user()->status == 0)
            <button
                class="p-[40px] rounded-[10px] bg-[#ff6363] text-white border-none  relative">
                <div
                    class="absolute inset-0 bg-black/30 backdrop-blur-[1px] z-10 flex items-center justify-center rounded-lg">
                    <a href="{{route('planes')}}"
                       target="_blank"
                       class="pointer-events-auto px-4 py-2 md:px-6 md:py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-bold rounded-full shadow-lg  hover:scale-105 transition flex items-center justify-center text-[13px] md:tex-base">
                        🔒 Hazte PRO
                    </a>
                </div>
                <span class="block text-base md:text-lg font-bold mb-2">Preguntas que más fallas</span>
                <span class="text-sm text-gray-600 text-white">Examen solo con tus preguntas más falladas.</span>
            </button>
        @else
            <button
                wire:click="selectMode('usuario')"
                class="p-[40px] rounded-[10px] bg-[#ff6363] text-white border-none">
                <span class="block text-base md:text-lg font-bold mb-2">Preguntas que más fallas</span>
                <span class="text-sm ">Examen solo con tus preguntas más falladas.</span>
            </button>
        @endif
        @if(!Auth::user()->hasAnyRole('root') && Auth::user()->status == 0)
            <button
                class="p-[40px] rounded-[10px] bg-[#ff6363] text-white border-none text-white relative">
                <div
                    class="absolute inset-0 bg-black/30 backdrop-blur-[1px] z-10 flex items-center justify-center rounded-lg">
                    <a href="{{route('planes')}}"
                       target="_blank"
                       class="pointer-events-auto px-4 py-2 md:px-6 md:py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-bold rounded-full shadow-lg  hover:scale-105 transition flex items-center justify-center text-[13px] md:tex-base">
                        🔒 Hazte PRO
                    </a>
                </div>
                <span class="block text-base md:text-lg font-bold mb-2">Preguntas más falladas (todos)</span>
                <span
                    class="text-sm text-gray-600 text-white">Examen con las preguntas más falladas por todos los usuarios.</span>
            </button>
        @else
            <button
                wire:click="selectMode('global')"
                class="p-[40px] rounded-[10px] bg-[#ff6363] text-white border-none">
                <span class="block text-base md:text-lg font-bold mb-2">
                    Preguntas más falladas (todos)
                </span>
                <span
                    class="text-sm ">Examen con las preguntas más falladas por todos los usuarios.
                </span>
            </button>
        @endif

        <button wire:click="selectMode('ia')"
                class="p-[40px] rounded-[10px] bg-[#195b81] text-white border-none text-white relative">
            <span
                class="bg-white p-[10px] rounded-l-[5px] absolute right-0 top-0 text-[#195b81] uppercase font-bold text-[12px] border border-[#195b81]"><i
                    class="fa-regular fa-face-smile mr-1"></i>Freemium</span>
            <span class="block text-base md:text-lg font-bold mb-2">Examen IA</span>
            <span class="text-sm text-gray-600 text-white">Las preguntas las genera una IA.</span>
        </button>
        <div class="relative col-span-1 md:col-span-2">
            @if(!Auth::user()->hasAnyRole('root') && Auth::user()->status == 0)
                <button
                    class="p-[40px] rounded-[10px] bg-[#5b8080] text-white border-none w-full opacity-50">
                    <div
                        class="absolute inset-0 bg-black/30 backdrop-blur-[1px] z-10 flex items-center justify-center rounded-lg">
                        <a href="{{route('planes')}}"
                           target="_blank"
                           class="pointer-events-auto px-4 py-2 md:px-6 md:py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-bold rounded-full shadow-lg  hover:scale-105 transition flex items-center justify-center text-[13px] md:tex-base">
                            🔒 Hazte PRO
                        </a>
                    </div>
                    <span class="block text-base md:text-lg font-bold mb-2">Conócete a ti mismo</span>
                    <span class="text-sm text-white">Métricas y análisis de tu rendimiento.</span>
                </button>
            @else
                <button
                    wire:click="selectMode('analisis')"
                    class="p-[40px] rounded-[10px] bg-[#5b8080] text-white border-none w-full opacity-50">
                    <span class="block text-base md:text-lg font-bold mb-2">Conócete a ti mismo</span>
                    <span class="text-sm text-white">Métricas y análisis de tu rendimiento.</span>
                </button>
            @endif
        </div>
    </div>
</div>

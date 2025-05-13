<div class="max-w-2xl mx-auto bg-white rounded shadow p-8 mt-8">
    <h1 class="text-2xl font-semibold mb-6 text-[#195b81]">¿Qué quieres hacer?</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <button wire:click="selectMode('normal')"
                class="rounded-lg p-6 bg-[#e4f1f1] hover:bg-[#d1e7e7] shadow transition">
            <span class="block text-lg font-bold mb-2">Examen Normal</span>
            <span class="text-sm text-gray-600">Elige áreas y tipos, como siempre.</span>
        </button>
        <button wire:click="selectMode('usuario')"
                class="rounded-lg p-6 bg-[#ffe4e1] hover:bg-[#ffd6d2] shadow transition">
            <span class="block text-lg font-bold mb-2">Preguntas que más fallas</span>
            <span class="text-sm text-gray-600">Examen solo con tus preguntas más falladas.</span>
        </button>
        <button wire:click="selectMode('global')"
                class="rounded-lg p-6 bg-[#f3e8ff] hover:bg-[#e9d7fd] shadow transition">
            <span class="block text-lg font-bold mb-2">Preguntas más falladas (todos)</span>
            <span class="text-sm text-gray-600">Examen con las preguntas más falladas por todos los usuarios.</span>
        </button>
        <button wire:click="selectMode('ia')" class="rounded-lg p-6 bg-[#fffbe4] hover:bg-[#fff3c2] shadow transition">
            <span class="block text-lg font-bold mb-2">Examen IA</span>
            <span class="text-sm text-gray-600">Las preguntas las genera una IA (próximamente).</span>
        </button>
        <button wire:click="selectMode('analisis')"
                class="rounded-lg p-6 bg-[#e4f1f1] hover:bg-[#d1e7e7] shadow transition col-span-1 md:col-span-2">
            <span class="block text-lg font-bold mb-2">Conócete a ti mismo</span>
            <span class="text-sm text-gray-600">Métricas y análisis de tu rendimiento.</span>
        </button>
    </div>
</div>

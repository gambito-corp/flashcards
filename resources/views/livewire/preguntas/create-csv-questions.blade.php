<div>
    <h2 class="text-xl font-bold mb-4">Cargar CSV de Preguntas</h2>
    <form wire:submit.prevent="importCsv">
        <div class="mb-4">
            <label for="csvFile" class="block text-sm font-medium text-gray-700">Archivo CSV</label>
            <input type="file" id="csvFile" wire:model="csvFile" class="mt-1 block w-full border-gray-300 rounded">
            @error('csvFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <button type="submit" class="bg-blue-500 text-white font-bold py-2 px-4 rounded">
            Importar CSV
        </button>
    </form>
    @if(session()->has('message'))
        <div class="mt-4 text-green-600">
            {{ session('message') }}
        </div>
    @endif
    <div class="mt-4">
        <a href="{{ route('csv-model.download') }}" class="bg-green-500 text-white font-bold py-2 px-4 rounded">
            Descargar Modelo CSV
        </a>
    </div>
</div>

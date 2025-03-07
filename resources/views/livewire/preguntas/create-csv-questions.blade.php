<div class="container-ask bg-white">
    <h2 class="text-xl font-bold mb-4 primary-color title-ask-container ">Cargar CSV de Preguntas</h2>
    
    <form wire:submit.prevent="importCsv">
        <div class="mb-4">
            <label for="csvFile" class="block text-sm font-medium text-gray-700">Archivo CSV</label>
            <hr>
           
            <input type="file" id="csvFile" wire:model="csvFile" class="text-sm text-grey-500 file:mr-5 file:py-2 file:px-6 file:rounded-full file:border-0 file:text-sm file:font-medium file:bg-blue-80 file:sky-950 hover:file:cursor-pointer">
            @error('csvFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div class="flex gap-[10px] items-center">
        <button type="submit" class="bg-blue-500 text-white font-bold py-2 px-4 rounded boton-success-m button-c2">
            Importar CSV
        </button>
    </form>
    @if(session()->has('message'))
        <div class="mt-4 text-green-600">
            {{ session('message') }}
        </div>
    @endif
    <div class="">
        <a href="{{ route('csv-model.download') }}" class="bg-green-500 text-white font-bold py-2 px-4 rounded boton-success-m">
            Descargar Modelo CSV
        </a>
    </div>

</div>
</div>

<div>
    <h2 class="text-lg font-semibold text-gray-700 text-center mb-4">
        📂 Subir Archivo CSV
    </h2>
    <form action="" method="post" enctype="multipart/form-data" class="space-y-4">
        @csrf
        <div>

            <label for="instrucciones" class="block text-sm font-medium text-gray-600">Selecciona un archivo CSV:</label>
            <input type="file" name="instrucciones" id="instrucciones" accept=".csv"
                   class="mt-2 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            @error('instrucciones')
            <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
            <label for="preguntas" class="block text-sm font-medium text-gray-600">Selecciona un archivo CSV:</label>
            <input type="file" name="preguntas" id="preguntas" accept=".csv" required
                   class="mt-2 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            @error('preguntas')
            <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
            <label for="respuestas" class="block text-sm font-medium text-gray-600">Selecciona un archivo CSV:</label>
            <input type="file" name="respuestas" id="respuestas" accept=".csv" required
                   class="mt-2 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
            @error('respuestas')
            <p class="text-red-500 text-xs italic">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-md transition">
            🚀 Subir Archivo
        </button>
    </form>
</div>



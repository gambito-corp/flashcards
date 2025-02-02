<div class="p-6 lg:p-8 bg-white border-b border-gray-200">

{{--    <x-application-logo class="block h-12 w-auto" />--}}

    <h1 class="mt-8 text-2xl font-medium text-gray-900">
        Titulo de la Tarjeta
    </h1>

    <p class="mt-6 text-gray-500 leading-relaxed">
        <span>
            Lorem ipsum dolor sit amet, consectetur adipisicing elit. Adipisci assumenda, commodi debitis deserunt
            doloribus excepturi maxime quaerat soluta totam ut? Consectetur pariatur totam veniam voluptatibus voluptatum! Id iusto magni quasi.
        </span>
        <span>
            Facere reiciendis tempora ullam vel. Commodi dicta maxime molestias repudiandae sed soluta. A asperiores
            consequatur dolores id in inventore nam, natus nulla odit officiis quasi quod sunt unde vel voluptates!
        </span>

    <h2 class="text-lg font-semibold text-gray-700 text-center mb-4">
        📂 Subir Archivo CSV
    </h2>
    <form action="{{route('carga-csv')}}" method="post" enctype="multipart/form-data" class="space-y-4">
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
    </p>
</div>

<div class="bg-gray-200 bg-opacity-25 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8 p-6 lg:p-8">
    <div>
        <div class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-6 h-6 stroke-gray-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
            </svg>
            <h2 class="ml-3 text-xl font-semibold text-gray-900">
                <a href="https://laravel.com/docs">Primera Parte</a>
            </h2>
        </div>

        <p class="mt-4 text-gray-500 text-sm leading-relaxed">
            En esta parte puedes
        </p>

        <p class="mt-4 text-sm">
            <a href="https://laravel.com/docs" class="inline-flex items-center font-semibold text-indigo-700">
                Explore the documentation

                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="ml-1 w-5 h-5 fill-indigo-500">
                    <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                </svg>
            </a>
        </p>
    </div>

    <div>
        <div class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-6 h-6 stroke-gray-400">
                <path stroke-linecap="round" d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z" />
            </svg>
            <h2 class="ml-3 text-xl font-semibold text-gray-900">
                <a href="https://laracasts.com">Laracasts</a>
            </h2>
        </div>

        <p class="mt-4 text-gray-500 text-sm leading-relaxed">
            Laracasts offers thousands of video tutorials on Laravel, PHP, and JavaScript development. Check them out, see for yourself, and massively level up your development skills in the process.
        </p>

        <p class="mt-4 text-sm">
            <a href="https://laracasts.com" class="inline-flex items-center font-semibold text-indigo-700">
                Start watching Laracasts

                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" class="ml-1 w-5 h-5 fill-indigo-500">
                    <path fill-rule="evenodd" d="M5 10a.75.75 0 01.75-.75h6.638L10.23 7.29a.75.75 0 111.04-1.08l3.5 3.25a.75.75 0 010 1.08l-3.5 3.25a.75.75 0 11-1.04-1.08l2.158-1.96H5.75A.75.75 0 015 10z" clip-rule="evenodd" />
                </svg>
            </a>
        </p>
    </div>

    <div>
        <div class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-6 h-6 stroke-gray-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
            <h2 class="ml-3 text-xl font-semibold text-gray-900">
                <a href="https://tailwindcss.com/">Tailwind</a>
            </h2>
        </div>

        <p class="mt-4 text-gray-500 text-sm leading-relaxed">
            Laravel Jetstream is built with Tailwind, an amazing utility first CSS framework that doesn't get in your way. You'll be amazed how easily you can build and maintain fresh, modern designs with this wonderful framework at your fingertips.
        </p>
    </div>

    <div>
        <div class="flex items-center">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" class="w-6 h-6 stroke-gray-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
            <h2 class="ml-3 text-xl font-semibold text-gray-900">
                Authentication
            </h2>
        </div>

        <p class="mt-4 text-gray-500 text-sm leading-relaxed">
            Authentication and registration views are included with Laravel Jetstream, as well as support for user email verification and resetting forgotten passwords. So, you're free to get started with what matters most: building your application.
        </p>
    </div>
</div>

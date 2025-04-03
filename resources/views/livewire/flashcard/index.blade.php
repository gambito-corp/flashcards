<div class="max-w-4xl mx-auto p-6 space-y-8 container-full">
    <!-- Mensaje de sesión -->
    @if (session()->has('message'))
    <div class="p-4 bg-green-100 text-green-700 rounded shadow">
        {{ session('message') }}
    </div>
    @endif

    <!-- Sección para crear una nueva categoría -->
    <div class="bg-white rounded container-askt">
        <h2 class="text-2xl font-semibold mb-4 primary-color title-ask-container">Crear Categoría</h2>
        <hr>
        <form wire:submit.prevent="createCategory">

            <div class="mb-4">
                <label for="categoryName" class="block text-gray-700">Nombre de la Categoría</label>
                <div class="group-formt">
                    <input type="text" id="categoryName" wire:model="categoryName" class="mt-1 block w-full rounded border-gray-300  focus:border-[#195b81] focus:ring-[#195b81]  ">
                    @error('categoryName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 boton-success-m">Crear Categoría</button>
                </div>
            </div>


        </form>
    </div>

    <hr class="border-gray-300">

    <!-- Formulario de creación de flashcard -->
    <div class="bg-white p-6 rounded container-askt">
        <h1 class="text-2xl font-semibold mb-4 primary-color title-ask-container">Crear Flashcard</h1>
        <hr>
        <form wire:submit.prevent="createCard" class="form-container-ask">

            <div class="mb-4">
                <label for="pregunta" class="block text-gray-700">Pregunta <span class="text-red-500">*</span></label>
                <input type="text" id="pregunta" wire:model="pregunta" class="mt-1 block w-full rounded border-gray-300 focus:border-[#195b81] focus:ring-[#195b81] ">
                @error('pregunta') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <div class="mb-4">
                <label for="respuesta" class="block text-gray-700">Respuesta <span class="text-red-500">*</span></label>
                <input type="text" id="respuesta" wire:model="respuesta" class="mt-1 block w-full rounded border-gray-300 focus:border-[#195b81] focus:ring-[#195b81] ">
                @error('respuesta') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>

            <!-- Campos opcionales -->
            <div class="group-form">
                <div class="mb-4">
                    <label for="url" class="block text-gray-700">URL</label>
                    <input type="text" id="url" wire:model="url" class="mt-1 block w-full rounded border-gray-300  focus:border-[#195b81] focus:ring-[#195b81] ">
                    @error('url') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label for="imagen" class="block text-gray-700">Imagen</label>
                    <input type="file" id="imagen" wire:model="imagen" accept="image/*" class=" w-full text-sm text-grey-500 file:mr-5 file:py-2 file:px-6 file:rounded-full file:border-0 file:text-sm file:font-medium file:bg-blue-80 file:sky-950 hover:file:cursor-pointer">
                    @error('imagen') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="group-form">
                <div class="mb-4">
                    <label for="url_respuesta" class="block text-gray-700">URL Respuesta</label>
                    <input type="text" id="url_respuesta" wire:model="url_respuesta" class="focus:border-[#195b81] focus:ring-[#195b81]  mt-1 block w-full rounded border-gray-300  ">
                    @error('url_respuesta') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label for="imagen_respuesta" class="block text-gray-700">Imagen Respuesta</label>
                    <input type="file" id="imagen_respuesta" wire:model="imagen_respuesta" accept="image/*" class=" w-full text-sm text-grey-500 file:mr-5 file:py-2 file:px-6 file:rounded-full file:border-0 file:text-sm file:font-medium file:bg-blue-80 file:sky-950 hover:file:cursor-pointer ">
                    @error('imagen_respuesta') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
            <!-- Sección opcional para asignar categorías a la flashcard -->
            <div class="mb-4">
                <label class="block text-gray-700">Categorías (opcional)</label>
                <div class="mt-2 space-x-4">
                    @foreach ($availableCategories as $category)
                    <label class="inline-flex items-center">
                        <input type="checkbox" wire:model="selectedCategories" value="{{ $category->id }}" class="focus:border-[#195b81] focus:ring-[#195b81]  rounded border-gray-300 text-indigo-600  checkbox-form ">
                        <span class="ml-2">{{ $category->nombre }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 boton-success-m">Crear Flashcard</button>
        </form>
    </div>

    <hr class="border-gray-300">
    {{--SELECCION DE TARJETAS--}}
    <div x-data="{ activeTab: 'sin-categoria' }" class="bg-white p-6 rounded container-askt">
        <h1 class="text-2xl font-semibold mb-4 primary-color title-ask-container">Seleccionar Flashcards para el juego</h1>
        <hr>
        <form wire:submit.prevent="startGame" class="form-container-ask">
            <!-- Pestañas de categorías -->
            <div class="border-b box-cat-flash">
                <nav class="-mb-px flex space-x-4" aria-label="Tabs">
                    <button
                        type="button"
                        @click="activeTab = 'sin-categoria'"
                        :class="activeTab === 'sin-categoria' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                        Sin Categoría
                    </button>
                    @foreach($availableCategories as $cat)
                    <button
                        type="button"
                        @click="activeTab = 'cat-{{ $cat->id }}'"
                        :class="activeTab === 'cat-{{ $cat->id }}' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                        class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm">
                        {{ $cat->nombre }}
                    </button>
                    @endforeach
                </nav>
            </div>

            <!-- Contenido de las pestañas -->
            <div class="mt-6">
                @php
                $cardsWithoutCategory = $cards->filter(fn($card) => $card->categories->isEmpty());
                @endphp

                <!-- Pestaña Sin Categoría -->
                <div x-show="activeTab === 'sin-categoria'">
                    @if($cardsWithoutCategory->count())
                    <!-- Contenedor del slider de "Sin Categoría" -->
                    <div x-data class="relative">
                        <div
                            class="flex space-x-4 overflow-x-auto py-2  flash-c-g gap-[10px]"
                            x-ref="slider">
                            @foreach($cardsWithoutCategory as $card)
                            <div
                                wire:click="toggleCard({{ $card->id }})"
                                class="box-flashcard-game flex-shrink-0 w-64 p-4 border rounded shadow cursor-pointer transition duration-200 ease-in-out hover:shadow-lg
                                        {{ in_array($card->id, $selectedCards) ? 'bg-green-100 border-green-400' : 'bg-white' }}">
                                <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyNC4yLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0ic3ZnMiIgeG1sbnM6c3ZnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyINCgkgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCAxMjYuMiAxMjYuMiINCgkgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMTI2LjIgMTI2LjI7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiNGRkZGRkY7fQ0KPC9zdHlsZT4NCjxnPg0KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik02Ni44LDEyNi4yYy0yLjUsMC00LjksMC03LjQsMGMtMC4zLTAuMS0wLjYtMC4yLTEtMC4yYy04LjgtMC42LTE3LjItMi45LTI1LTcuMkMxNS43LDEwOSw0LjgsOTQuMSwxLDc0LjENCgkJYy0wLjUtMi40LTAuNy00LjktMS03LjNjMC0yLjUsMC00LjksMC03LjRjMC4xLTAuNCwwLjItMC43LDAuMi0xLjFjMC42LTguOCwyLjktMTcuMiw3LjItMjQuOUMxOSwxMi44LDM2LjgsMS42LDYwLjQsMC4xDQoJCUM3MC43LTAuNSw4MC41LDEuNCw4OS43LDZjMjIuNSwxMS4yLDM0LjcsMjkuNCwzNi40LDU0LjVjMC44LDExLjUtMS44LDIyLjQtNy40LDMyLjVjLTkuOCwxNy43LTI0LjgsMjguNS00NC43LDMyLjMNCgkJQzcxLjYsMTI1LjcsNjkuMiwxMjUuOSw2Ni44LDEyNi4yeiBNMTE2LjMsNjMuMkMxMTYuNCwzMy44LDkyLjYsMTAsNjMuMiw5LjlDMzMuOCw5LjgsOS45LDMzLjYsOS45LDYzLjENCgkJQzkuOCw5Mi40LDMzLjYsMTE2LjIsNjMsMTE2LjNDOTIuMywxMTYuNCwxMTYuMyw5Mi42LDExNi4zLDYzLjJ6Ii8+DQoJPHBhdGggY2xhc3M9InN0MCIgZD0iTTYzLjEsMzEuN2M5LjUsMCwxNy45LDcsMTkuNSwxNi4zYzEuNiw5LjctMy45LDE5LTEzLjEsMjJjLTEuMSwwLjQtMS41LDAuOC0xLjQsMmMwLjEsMi4xLDAsNC4yLDAsNi4zDQoJCWMwLDMtMi4xLDUuMi01LDUuMWMtMi44LDAtNC45LTIuMS00LjktNS4xYzAtNCwwLTgsMC0xMS45YzAtMi44LDEuNy00LjgsNC41LTVjMy4zLTAuMyw2LjEtMS4yLDguMS0zLjljMi40LTMuMiwyLjgtNi43LDEtMTAuMw0KCQljLTEuOC0zLjctNS01LjYtOS4yLTUuNWMtNSwwLjItOC43LDMuNy05LjQsOC43Yy0wLjEsMC40LTAuMSwwLjktMC4xLDEuM2MtMC4zLDIuOC0yLjQsNC43LTUuMSw0LjZjLTIuNy0wLjEtNC44LTIuMi00LjctNQ0KCQljMC4yLTcsMy4yLTEyLjUsOS4xLTE2LjRDNTUuNywzMi43LDU5LjIsMzEuNyw2My4xLDMxLjd6Ii8+DQoJPHBhdGggY2xhc3M9InN0MCIgZD0iTTYzLjMsODcuMmMzLjQsMC4xLDYuMSwzLDYsNi40Yy0wLjEsMy40LTMsNi02LjMsNS45Yy0zLjQtMC4xLTYuMS0zLTYtNi40QzU3LjEsODkuNyw1OS45LDg3LjEsNjMuMyw4Ny4yeiIvPg0KPC9nPg0KPC9zdmc+DQo=" />
                                <h2 class="font-bold text-lg">{{ $card->pregunta }}</h2>
                            </div>
                            @endforeach
                        </div>
                        <div class="arrow-flash">
                            <!-- Flecha izquierda -->
                            <button type="button" class="transform -translate-y-1/2 p-2  rounded-full hover:bg-gray-300"
                                @click="$refs.slider.scrollBy({ left: -300, behavior: 'smooth' })">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <!-- Flecha derecha -->
                            <button type="button" class="transform -translate-y-1/2 p-2  rounded-full hover:bg-gray-300"
                                @click="$refs.slider.scrollBy({ left: 300, behavior: 'smooth' })">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    @else
                    <p class="text-gray-600">No hay flashcards sin categoría.</p>
                    @endif
                </div>

                <!-- Pestañas por categoría -->
                @foreach($availableCategories as $cat)
                @php
                $cardsInCategory = $cards->filter(function($card) use ($cat) {
                return $card->categories->contains($cat);
                });
                @endphp
                <div x-show="activeTab === 'cat-{{ $cat->id }}'">
                    @if($cardsInCategory->count())
                    <div x-data class="relative">
                        <div class="flex  overflow-x-auto py-2 flash-c-g" x-ref="slider">
                            @foreach($cardsInCategory as $card)
                            <div
                                wire:click="toggleCard({{ $card->id }})"
                                class="box-flashcard-game  flex-shrink-0 w-64 p-4 border rounded shadow cursor-pointer transition duration-200 ease-in-out hover:shadow-lg {{ in_array($card->id, $selectedCards) ? 'bg-green-100 border-green-400' : 'bg-white' }}">
                                <img src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyNC4yLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0ic3ZnMiIgeG1sbnM6c3ZnPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyINCgkgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayIgeD0iMHB4IiB5PSIwcHgiIHZpZXdCb3g9IjAgMCAxMjYuMiAxMjYuMiINCgkgc3R5bGU9ImVuYWJsZS1iYWNrZ3JvdW5kOm5ldyAwIDAgMTI2LjIgMTI2LjI7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiNGRkZGRkY7fQ0KPC9zdHlsZT4NCjxnPg0KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik02Ni44LDEyNi4yYy0yLjUsMC00LjksMC03LjQsMGMtMC4zLTAuMS0wLjYtMC4yLTEtMC4yYy04LjgtMC42LTE3LjItMi45LTI1LTcuMkMxNS43LDEwOSw0LjgsOTQuMSwxLDc0LjENCgkJYy0wLjUtMi40LTAuNy00LjktMS03LjNjMC0yLjUsMC00LjksMC03LjRjMC4xLTAuNCwwLjItMC43LDAuMi0xLjFjMC42LTguOCwyLjktMTcuMiw3LjItMjQuOUMxOSwxMi44LDM2LjgsMS42LDYwLjQsMC4xDQoJCUM3MC43LTAuNSw4MC41LDEuNCw4OS43LDZjMjIuNSwxMS4yLDM0LjcsMjkuNCwzNi40LDU0LjVjMC44LDExLjUtMS44LDIyLjQtNy40LDMyLjVjLTkuOCwxNy43LTI0LjgsMjguNS00NC43LDMyLjMNCgkJQzcxLjYsMTI1LjcsNjkuMiwxMjUuOSw2Ni44LDEyNi4yeiBNMTE2LjMsNjMuMkMxMTYuNCwzMy44LDkyLjYsMTAsNjMuMiw5LjlDMzMuOCw5LjgsOS45LDMzLjYsOS45LDYzLjENCgkJQzkuOCw5Mi40LDMzLjYsMTE2LjIsNjMsMTE2LjNDOTIuMywxMTYuNCwxMTYuMyw5Mi42LDExNi4zLDYzLjJ6Ii8+DQoJPHBhdGggY2xhc3M9InN0MCIgZD0iTTYzLjEsMzEuN2M5LjUsMCwxNy45LDcsMTkuNSwxNi4zYzEuNiw5LjctMy45LDE5LTEzLjEsMjJjLTEuMSwwLjQtMS41LDAuOC0xLjQsMmMwLjEsMi4xLDAsNC4yLDAsNi4zDQoJCWMwLDMtMi4xLDUuMi01LDUuMWMtMi44LDAtNC45LTIuMS00LjktNS4xYzAtNCwwLTgsMC0xMS45YzAtMi44LDEuNy00LjgsNC41LTVjMy4zLTAuMyw2LjEtMS4yLDguMS0zLjljMi40LTMuMiwyLjgtNi43LDEtMTAuMw0KCQljLTEuOC0zLjctNS01LjYtOS4yLTUuNWMtNSwwLjItOC43LDMuNy05LjQsOC43Yy0wLjEsMC40LTAuMSwwLjktMC4xLDEuM2MtMC4zLDIuOC0yLjQsNC43LTUuMSw0LjZjLTIuNy0wLjEtNC44LTIuMi00LjctNQ0KCQljMC4yLTcsMy4yLTEyLjUsOS4xLTE2LjRDNTUuNywzMi43LDU5LjIsMzEuNyw2My4xLDMxLjd6Ii8+DQoJPHBhdGggY2xhc3M9InN0MCIgZD0iTTYzLjMsODcuMmMzLjQsMC4xLDYuMSwzLDYsNi40Yy0wLjEsMy40LTMsNi02LjMsNS45Yy0zLjQtMC4xLTYuMS0zLTYtNi40QzU3LjEsODkuNyw1OS45LDg3LjEsNjMuMyw4Ny4yeiIvPg0KPC9nPg0KPC9zdmc+DQo=" />
                                <h2 class="font-bold text-lg">{{ $card->pregunta }}</h2>
                            </div>
                            @endforeach
                        </div>

                        <div class="arrow-flash">
                            <!-- Flecha izquierda -->
                            <button type="button" class="transform -translate-y-1/2 p-2  rounded-full hover:bg-gray-300"
                                @click="$refs.slider.scrollBy({ left: -300, behavior: 'smooth' })">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                            </button>
                            <!-- Flecha derecha -->
                            <button type="button" class="transform -translate-y-1/2 p-2  rounded-full hover:bg-gray-300"
                                @click="$refs.slider.scrollBy({ left: 300, behavior: 'smooth' })">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-gray-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>
                        </div>
                    </div>
                    @else
                    <p class="text-gray-600">No hay flashcards en esta categoría.</p>
                    @endif
                </div>
                @endforeach
            </div>

            <!-- Botón para iniciar el juego -->
            <div class="mt-4">
                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 boton-success-m">
                    Iniciar Juego
                </button>
            </div>
        </form>
    </div>
</div>
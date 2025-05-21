<div class="max-w-4xl mx-auto p-6 space-y-8 container-full">

    <!-- Mensaje de sesión -->
    @if (session()->has('message'))
        <div class="mt-2 text-red-600 font-semibold">
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
                    <input type="text" id="categoryName" wire:model="categoryName"
                           class="mt-1 block w-full rounded border-gray-300  focus:border-[#195b81] focus:ring-[#195b81]  ">
                    @error('categoryName') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    <button type="submit"
                            class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 boton-success-m">Crear
                        Categoría
                    </button>
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

            <div class="mb-4 relative">
                <button
                    type="button"
                    @if(Auth::user()->hasAnyRole('root') || Auth::user()->status == 0)
                        wire:click="generarPreguntaIA"
                    @endif
                    wire:loading.attr="disabled"
                    wire:target="generarPreguntaIA"
                    class="flex bg-[#195b81] transition duration-300 hover:bg-[#0d4362] px-[25px] py-[10px] rounded-[8px] justify-center items-center gap-[8px] text-white font-semibold text-[12px] absolute bottom-[40px] right-[15px]">
                    <img class="w-[22px]"
                         src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhLS0gQ3JlYXRlZCB3aXRoIElua3NjYXBlIChodHRwOi8vd3d3Lmlua3NjYXBlLm9yZy8pIC0tPgoKPHN2ZwogICB2ZXJzaW9uPSIxLjEiCiAgIGlkPSJzdmcyIgogICB3aWR0aD0iMzcuMDc4OCIKICAgaGVpZ2h0PSIyOS42NDM2IgogICB2aWV3Qm94PSIwIDAgMzcuMDc4OCAyOS42NDM2IgogICB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciCiAgIHhtbG5zOnN2Zz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgogIDxkZWZzCiAgICAgaWQ9ImRlZnM2Ij4KICAgIDxjbGlwUGF0aAogICAgICAgY2xpcFBhdGhVbml0cz0idXNlclNwYWNlT25Vc2UiCiAgICAgICBpZD0iY2xpcFBhdGgxNiI+CiAgICAgIDxwYXRoCiAgICAgICAgIGQ9Ik0gMCwyMi4yMzMgSCAyNy44MDkgViAwIEggMCBaIgogICAgICAgICBpZD0icGF0aDE0IiAvPgogICAgPC9jbGlwUGF0aD4KICA8L2RlZnM+CiAgPGcKICAgICBpZD0iZzgiCiAgICAgdHJhbnNmb3JtPSJtYXRyaXgoMS4zMzMzMzMzLDAsMCwtMS4zMzMzMzMzLDAsMjkuNjQzNikiPgogICAgPGcKICAgICAgIGlkPSJnMTAiPgogICAgICA8ZwogICAgICAgICBpZD0iZzEyIgogICAgICAgICBjbGlwLXBhdGg9InVybCgjY2xpcFBhdGgxNikiPgogICAgICAgIDxnCiAgICAgICAgICAgaWQ9ImcxOCIKICAgICAgICAgICB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxMy45MjYzLDEuMzc3MikiPgogICAgICAgICAgPHBhdGgKICAgICAgICAgICAgIGQ9Ik0gMCwwIEMgMS44MDEsMCAzLjYwMywtMC4wMDYgNS40MDQsMC4wMDIgNy4xNjcsMC4wMSA4LjM1OCwxLjIxIDguMzYsMi45NzEgYyAwLjAwMiwzLjEyMiAwLjAwMiw2LjI0MyAwLDkuMzY1IC0wLjAwMiwxLjc2NCAtMS4xNzUsMi45ODIgLTIuOTQxLDIuOTkxIC0zLjYxMywwLjAxOCAtNy4yMjcsMC4wMTkgLTEwLjg0LDAgQyAtNy4xODcsMTUuMzE4IC04LjM1OSwxNC4wOTggLTguMzYsMTIuMzM0IC04LjM2Miw5LjIxMyAtOC4zNzMsNi4wOTEgLTguMzQ3LDIuOTcgLTguMzQzLDIuNTA0IC04LjI0MiwyLjAwMiAtOC4wNDUsMS41ODMgLTcuNTMxLDAuNDg4IC02LjYwMSwwIC01LjQwMywwIFogbSAtMC42ODIsMTYuNzAxIGMgMCwxLjE0IC0wLjAwMywyLjIxOSAwLjAwMywzLjI5OSAwLjAwMSwwLjE2IDAuMDEzLDAuMzM0IDAuMDc1LDAuNDc3IDAuMTMxLDAuMzAxIDAuNDQxLDAuNDMxIDAuNzY1LDAuMzU5IDAuMzA4LC0wLjA2NyAwLjUxNiwtMC4zMzMgMC41MTksLTAuNzAyIDAuMDA2LC0xLjAxNSAwLjAwMiwtMi4wMyAwLjAwMywtMy4wNDUgdiAtMC4zODggaCAwLjQyMSBjIDEuNDMsMCAyLjg2LDAuMDEyIDQuMjksLTAuMDAzIEMgNy4wMjYsMTYuNjgxIDguMzMzLDE2LjA0NiA5LjE4NSwxNC42MDYgOS41MjQsMTQuMDMzIDkuNzExLDEzLjQwMSA5LjcxNCwxMi43MzggOS43MjYsOS4zNTUgOS43NSw1Ljk3MiA5LjcwOSwyLjU4OSA5LjY4MywwLjQzOSA3LjkxMiwtMS4zMjkgNS43NjMsLTEuMzUgYyAtMy44NDEsLTAuMDM2IC03LjY4MywtMC4wMzcgLTExLjUyNSwwIC0yLjE2MiwwLjAyMSAtMy45MjYsMS44MDIgLTMuOTQ5LDMuOTcgLTAuMDM2LDMuMzYxIC0wLjAxMyw2LjcyMyAtMC4wMDQsMTAuMDg0IDEwZS00LDAuMjkgMC4wNTksMC41ODYgMC4xMjYsMC44NyAwLjM5MywxLjY3NCAxLjg1NCwyLjk4IDMuNTczLDMuMDY3IDEuNjIyLDAuMDgzIDMuMjUxLDAuMDQ1IDQuODc2LDAuMDYgMC4xMzksMC4wMDEgMC4yNzgsMCAwLjQ1OCwwIgogICAgICAgICAgICAgc3R5bGU9ImZpbGw6I2ZmZmZmZjtmaWxsLW9wYWNpdHk6MTtmaWxsLXJ1bGU6bm9uemVybztzdHJva2U6bm9uZSIKICAgICAgICAgICAgIGlkPSJwYXRoMjAiIC8+CiAgICAgICAgPC9nPgogICAgICAgIDxnCiAgICAgICAgICAgaWQ9ImcyMiIKICAgICAgICAgICB0cmFuc2Zvcm09InRyYW5zbGF0ZSgyLjc1NDksMTIuNTEwNSkiPgogICAgICAgICAgPHBhdGgKICAgICAgICAgICAgIGQ9Im0gMCwwIHYgLTEuMzY4IGMgLTAuMTc4LDAgLTAuMzQ3LDAuMDA1IC0wLjUxNiwtMTBlLTQgLTAuNTk1LC0wLjAyMiAtMC44NTQsLTAuMjgyIC0wLjg1NiwtMC44NzYgLTAuMDA0LC0xLjI1NSAtMC4wMDIsLTIuNTA5IC0xMGUtNCwtMy43NjQgMCwtMC43MzggMC4yMTgsLTAuOTU1IDAuOTU1LC0wLjk2MSAwLjEyOCwtMC4wMDEgMC4yNTcsMCAwLjQxMSwwIHYgLTEuMzIxIGMgLTEuMzM3LC0wLjIyMiAtMi42NzgsMC40MzYgLTIuNzI0LDIuMDkzIC0wLjAzOCwxLjM1MiAtMC4wMjQsMi43MDYgLTAuMDAzLDQuMDU4IDAuMDIsMS4yNTYgMC44ODksMi4wOTUgMi4xNzEsMi4xNCBDIC0wLjM5LDAuMDA1IC0wLjIxNywwIDAsMCIKICAgICAgICAgICAgIHN0eWxlPSJmaWxsOiNmZmZmZmY7ZmlsbC1vcGFjaXR5OjE7ZmlsbC1ydWxlOm5vbnplcm87c3Ryb2tlOm5vbmUiCiAgICAgICAgICAgICBpZD0icGF0aDI0IiAvPgogICAgICAgIDwvZz4KICAgICAgICA8ZwogICAgICAgICAgIGlkPSJnMjYiCiAgICAgICAgICAgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMjUuMTA0LDQuMjEyNikiPgogICAgICAgICAgPHBhdGgKICAgICAgICAgICAgIGQ9Ik0gMCwwIFYgMS4zMyBDIDAuMjA3LDEuMzMgMC40LDEuMzIxIDAuNTkyLDEuMzMyIDEuMDk4LDEuMzYgMS4zNTMsMS41OCAxLjM1OSwyLjA3OCAxLjM3NiwzLjQ1MiAxLjM3Myw0LjgyNiAxLjM1OCw2LjIgMS4zNTQsNi42MTggMS4xMSw2Ljg1MiAwLjY4Nyw2LjkxIDAuNDczLDYuOTM5IDAuMjUzLDYuOTM0IDAuMDA2LDYuOTQ2IFYgOC4yNDIgQyAxLjI4Miw4LjQ5MyAyLjY3NSw3LjgzIDIuNzI0LDYuMTczIDIuNzY0LDQuODExIDIuNzU1LDMuNDQ2IDIuNzI2LDIuMDgzIDIuNjk1LDAuNjQ2IDEuNTAxLC0wLjI1NiAwLDAiCiAgICAgICAgICAgICBzdHlsZT0iZmlsbDojZmZmZmZmO2ZpbGwtb3BhY2l0eToxO2ZpbGwtcnVsZTpub256ZXJvO3N0cm9rZTpub25lIgogICAgICAgICAgICAgaWQ9InBhdGgyOCIgLz4KICAgICAgICA8L2c+CiAgICAgICAgPGcKICAgICAgICAgICBpZD0iZzMwIgogICAgICAgICAgIHRyYW5zZm9ybT0idHJhbnNsYXRlKDE2LjcwMjEsMTEuMTI3NykiPgogICAgICAgICAgPHBhdGgKICAgICAgICAgICAgIGQ9Ik0gMCwwIEMgLTAuMDAxLC0wLjgwNyAwLjU5MywtMS40MDUgMS4zOTksLTEuNDA3IDIuMTk5LC0xLjQwOSAyLjgwNywtMC43OTkgMi44MDIsMCAyLjc5OCwwLjgxIDIuMjA0LDEuNDA1IDEuMzk5LDEuNDA1IDAuNTk2LDEuNDA1IDAuMDAyLDAuODA5IDAsMCBtIC0xLjM2NiwwLjAwNSBjIDAuMDEyLDEuNTA5IDEuMjg4LDIuNzc4IDIuNzgsMi43NjYgMS40OTUsLTAuMDEyIDIuNzU4LC0xLjI5MiAyLjc2MSwtMi43OTUgMC4wMDIsLTEuNDY3IC0xLjI5MywtMi43NTEgLTIuNzc0LC0yLjc1MSAtMS40OTksMCAtMi43NzksMS4yODYgLTIuNzY3LDIuNzgiCiAgICAgICAgICAgICBzdHlsZT0iZmlsbDojZmZmZmZmO2ZpbGwtb3BhY2l0eToxO2ZpbGwtcnVsZTpub256ZXJvO3N0cm9rZTpub25lIgogICAgICAgICAgICAgaWQ9InBhdGgzMiIgLz4KICAgICAgICA8L2c+CiAgICAgICAgPGcKICAgICAgICAgICBpZD0iZzM0IgogICAgICAgICAgIHRyYW5zZm9ybT0idHJhbnNsYXRlKDExLjE0OTksMTEuMTE0NSkiPgogICAgICAgICAgPHBhdGgKICAgICAgICAgICAgIGQ9Ik0gMCwwIEMgMC4wMDMsMC44MTIgLTAuNTg0LDEuNDEyIC0xLjM4NywxLjQxOCAtMi4xOTQsMS40MjMgLTIuNzg4LDAuODM5IC0yLjgwMiwwLjAyNSAtMi44MTUsLTAuNzc0IC0yLjIxOCwtMS4zODQgLTEuNDEzLC0xLjM5NSAtMC42MTMsLTEuNDA1IC0wLjAwMiwtMC44MDIgMCwwIG0gMS4zNjUsMC4wMTYgYyAwLjAwMSwtMS41MDEgLTEuMjc3LC0yLjc4MiAtMi43NjksLTIuNzc4IC0xLjQ5MywwLjAwNSAtMi43NzQsMS4yOTQgLTIuNzY5LDIuNzg2IDAuMDA1LDEuNDcgMS4yOTEsMi43NTUgMi43NjEsMi43NjEgMS40OTQsMC4wMDcgMi43NzYsLTEuMjczIDIuNzc3LC0yLjc2OSIKICAgICAgICAgICAgIHN0eWxlPSJmaWxsOiNmZmZmZmY7ZmlsbC1vcGFjaXR5OjE7ZmlsbC1ydWxlOm5vbnplcm87c3Ryb2tlOm5vbmUiCiAgICAgICAgICAgICBpZD0icGF0aDM2IiAvPgogICAgICAgIDwvZz4KICAgICAgICA8ZwogICAgICAgICAgIGlkPSJnMzgiCiAgICAgICAgICAgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMTMuOTQ3OCw1LjUyODUpIj4KICAgICAgICAgIDxwYXRoCiAgICAgICAgICAgICBkPSJtIDAsMCBjIDAuMjM5LDAgMC40OCwwLjAxNyAwLjcxNywtMC4wMDMgMC4zNzMsLTAuMDMyIDAuNjEyLC0wLjI4MyAwLjYzLC0wLjYyOCAwLjAyLC0wLjM4MSAtMC4xOCwtMC42NjMgLTAuNTc3LC0wLjY4OSAtMC41NCwtMC4wMzQgLTEuMDg2LC0wLjAzIC0xLjYyNiwwLjAwOCAtMC4zNTcsMC4wMjUgLTAuNTQzLDAuMjk5IC0wLjUzOCwwLjY1OSAwLjAwNSwwLjMxMyAwLjIyOSwwLjYgMC41NDcsMC42MzUgMC4yNzksMC4wMyAwLjU2NCwwLjAwNiAwLjg0NywwLjAwNiAwLDAuMDA0IDAsMC4wMDggMCwwLjAxMiIKICAgICAgICAgICAgIHN0eWxlPSJmaWxsOiNmZmZmZmY7ZmlsbC1vcGFjaXR5OjE7ZmlsbC1ydWxlOm5vbnplcm87c3Ryb2tlOm5vbmUiCiAgICAgICAgICAgICBpZD0icGF0aDQwIiAvPgogICAgICAgIDwvZz4KICAgICAgICA8ZwogICAgICAgICAgIGlkPSJnNDIiCiAgICAgICAgICAgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoOS43Nyw1LjUyOSkiPgogICAgICAgICAgPHBhdGgKICAgICAgICAgICAgIGQ9Im0gMCwwIGMgMC4yMzksMCAwLjQ4LDAuMDE3IDAuNzE4LC0wLjAwMyAwLjM3MywtMC4wMzIgMC42MzgsLTAuMzE4IDAuNjM4LC0wLjY2IDEwZS00LC0wLjM2IC0wLjIwMywtMC42MzQgLTAuNTgzLC0wLjY1OCAtMC41NCwtMC4wMzMgLTEuMDg1LC0wLjAzIC0xLjYyNSwwLjAwOSAtMC4zNjMsMC4wMjUgLTAuNTM1LDAuMjgzIC0wLjUzMiwwLjY1OCAwLjAwMywwLjMyNiAwLjIxLDAuNTk4IDAuNTM3LDAuNjM1IDAuMjc5LDAuMDMyIDAuNTY0LDAuMDA3IDAuODQ3LDAuMDA3IHoiCiAgICAgICAgICAgICBzdHlsZT0iZmlsbDojZmZmZmZmO2ZpbGwtb3BhY2l0eToxO2ZpbGwtcnVsZTpub256ZXJvO3N0cm9rZTpub25lIgogICAgICAgICAgICAgaWQ9InBhdGg0NCIgLz4KICAgICAgICA8L2c+CiAgICAgICAgPGcKICAgICAgICAgICBpZD0iZzQ2IgogICAgICAgICAgIHRyYW5zZm9ybT0idHJhbnNsYXRlKDE4LjEwMDEsNC4xNTYpIj4KICAgICAgICAgIDxwYXRoCiAgICAgICAgICAgICBkPSJNIDAsMCBDIC0wLjI4MiwwLjAxOCAtMC41NjcsMC4wMTcgLTAuODQ1LDAuMDU5IC0xLjE4MSwwLjExIC0xLjM2NiwwLjM1NSAtMS4zNzUsMC42OTkgLTEuMzgzLDEgLTEuMTUxLDEuMzMyIC0wLjg0NSwxLjM0OSAtMC4yODIsMS4zOCAwLjI4NiwxLjM4MSAwLjg0OSwxLjM0OCAxLjE2OCwxLjMyOSAxLjM3LDEuMDI3IDEuMzY2LDAuNjk4IDEuMzYyLDAuMzM5IDEuMTkzLDAuMTEyIDAuODQ1LDAuMDU5IDAuNTY3LDAuMDE3IDAuMjgzLDAuMDE4IDAsMCIKICAgICAgICAgICAgIHN0eWxlPSJmaWxsOiNmZmZmZmY7ZmlsbC1vcGFjaXR5OjE7ZmlsbC1ydWxlOm5vbnplcm87c3Ryb2tlOm5vbmUiCiAgICAgICAgICAgICBpZD0icGF0aDQ4IiAvPgogICAgICAgIDwvZz4KICAgICAgPC9nPgogICAgPC9nPgogIDwvZz4KPC9zdmc+Cg=="
                         alt="Generar por IA">
                    <span wire:loading.remove wire:target="generarPreguntaIA">Generar por IA</span>
                    <span wire:loading wire:target="generarPreguntaIA">Generando...</span>
                    @if(!Auth::user()->hasAnyRole('root') && Auth::user()->status == 0)
                        <!-- Overlay transparente -->
                        <span class="absolute inset-0 bg-black/30 z-10 rounded-[8px]">
                            <a href="{{route('planes')}}"
                               target="_blank"
                               class="px-4 py-2 z-10 md:px-6 md:py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-bold rounded-full shadow-lg hover:scale-105 transition flex items-center justify-center text-[13px] md:tex-base opacity-0 hover:opacity-100">
                                🔒 Hazte PRO
                            </a>
                        </span>
                    @endif
                </button>

                <label for="pregunta" class="block text-gray-700 mt-4">Pregunta <span
                        class="text-red-500">*</span></label>
                <textarea
                    id="pregunta"
                    wire:model.live="pregunta"
                    class="mt-1 block w-full rounded border-gray-300 focus:border-[#195b81] focus:ring-[#195b81]"
                    rows="1">{{$pregunta}}</textarea>
                @error('pregunta') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>


            <div class="mb-4 relative">
                <button
                    type="button"
                    @if(Auth::user()->hasAnyRole('root') || Auth::user()->status == 1)
                        wire:click="generarRespuestaIA"
                    @endif
                    wire:loading.attr="disabled"
                    wire:target="generarRespuestaIA"
                    class="flex bg-[#195b81] transition duration-300 hover:bg-[#0d4362] px-[25px] py-[10px] rounded-[8px] justify-center items-center gap-[8px] text-white font-semibold text-[12px] absolute bottom-[40px] right-[15px]">
                    <img class="w-[22px]"
                         src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0iVVRGLTgiIHN0YW5kYWxvbmU9Im5vIj8+CjwhLS0gQ3JlYXRlZCB3aXRoIElua3NjYXBlIChodHRwOi8vd3d3Lmlua3NjYXBlLm9yZy8pIC0tPgoKPHN2ZwogICB2ZXJzaW9uPSIxLjEiCiAgIGlkPSJzdmcyIgogICB3aWR0aD0iMzcuMDc4OCIKICAgaGVpZ2h0PSIyOS42NDM2IgogICB2aWV3Qm94PSIwIDAgMzcuMDc4OCAyOS42NDM2IgogICB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciCiAgIHhtbG5zOnN2Zz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciPgogIDxkZWZzCiAgICAgaWQ9ImRlZnM2Ij4KICAgIDxjbGlwUGF0aAogICAgICAgY2xpcFBhdGhVbml0cz0idXNlclNwYWNlT25Vc2UiCiAgICAgICBpZD0iY2xpcFBhdGgxNiI+CiAgICAgIDxwYXRoCiAgICAgICAgIGQ9Ik0gMCwyMi4yMzMgSCAyNy44MDkgViAwIEggMCBaIgogICAgICAgICBpZD0icGF0aDE0IiAvPgogICAgPC9jbGlwUGF0aD4KICA8L2RlZnM+CiAgPGcKICAgICBpZD0iZzgiCiAgICAgdHJhbnNmb3JtPSJtYXRyaXgoMS4zMzMzMzMzLDAsMCwtMS4zMzMzMzMzLDAsMjkuNjQzNikiPgogICAgPGcKICAgICAgIGlkPSJnMTAiPgogICAgICA8ZwogICAgICAgICBpZD0iZzEyIgogICAgICAgICBjbGlwLXBhdGg9InVybCgjY2xpcFBhdGgxNikiPgogICAgICAgIDxnCiAgICAgICAgICAgaWQ9ImcxOCIKICAgICAgICAgICB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxMy45MjYzLDEuMzc3MikiPgogICAgICAgICAgPHBhdGgKICAgICAgICAgICAgIGQ9Ik0gMCwwIEMgMS44MDEsMCAzLjYwMywtMC4wMDYgNS40MDQsMC4wMDIgNy4xNjcsMC4wMSA4LjM1OCwxLjIxIDguMzYsMi45NzEgYyAwLjAwMiwzLjEyMiAwLjAwMiw2LjI0MyAwLDkuMzY1IC0wLjAwMiwxLjc2NCAtMS4xNzUsMi45ODIgLTIuOTQxLDIuOTkxIC0zLjYxMywwLjAxOCAtNy4yMjcsMC4wMTkgLTEwLjg0LDAgQyAtNy4xODcsMTUuMzE4IC04LjM1OSwxNC4wOTggLTguMzYsMTIuMzM0IC04LjM2Miw5LjIxMyAtOC4zNzMsNi4wOTEgLTguMzQ3LDIuOTcgLTguMzQzLDIuNTA0IC04LjI0MiwyLjAwMiAtOC4wNDUsMS41ODMgLTcuNTMxLDAuNDg4IC02LjYwMSwwIC01LjQwMywwIFogbSAtMC42ODIsMTYuNzAxIGMgMCwxLjE0IC0wLjAwMywyLjIxOSAwLjAwMywzLjI5OSAwLjAwMSwwLjE2IDAuMDEzLDAuMzM0IDAuMDc1LDAuNDc3IDAuMTMxLDAuMzAxIDAuNDQxLDAuNDMxIDAuNzY1LDAuMzU5IDAuMzA4LC0wLjA2NyAwLjUxNiwtMC4zMzMgMC41MTksLTAuNzAyIDAuMDA2LC0xLjAxNSAwLjAwMiwtMi4wMyAwLjAwMywtMy4wNDUgdiAtMC4zODggaCAwLjQyMSBjIDEuNDMsMCAyLjg2LDAuMDEyIDQuMjksLTAuMDAzIEMgNy4wMjYsMTYuNjgxIDguMzMzLDE2LjA0NiA5LjE4NSwxNC42MDYgOS41MjQsMTQuMDMzIDkuNzExLDEzLjQwMSA5LjcxNCwxMi43MzggOS43MjYsOS4zNTUgOS43NSw1Ljk3MiA5LjcwOSwyLjU4OSA5LjY4MywwLjQzOSA3LjkxMiwtMS4zMjkgNS43NjMsLTEuMzUgYyAtMy44NDEsLTAuMDM2IC03LjY4MywtMC4wMzcgLTExLjUyNSwwIC0yLjE2MiwwLjAyMSAtMy45MjYsMS44MDIgLTMuOTQ5LDMuOTcgLTAuMDM2LDMuMzYxIC0wLjAxMyw2LjcyMyAtMC4wMDQsMTAuMDg0IDEwZS00LDAuMjkgMC4wNTksMC41ODYgMC4xMjYsMC44NyAwLjM5MywxLjY3NCAxLjg1NCwyLjk4IDMuNTczLDMuMDY3IDEuNjIyLDAuMDgzIDMuMjUxLDAuMDQ1IDQuODc2LDAuMDYgMC4xMzksMC4wMDEgMC4yNzgsMCAwLjQ1OCwwIgogICAgICAgICAgICAgc3R5bGU9ImZpbGw6I2ZmZmZmZjtmaWxsLW9wYWNpdHk6MTtmaWxsLXJ1bGU6bm9uemVybztzdHJva2U6bm9uZSIKICAgICAgICAgICAgIGlkPSJwYXRoMjAiIC8+CiAgICAgICAgPC9nPgogICAgICAgIDxnCiAgICAgICAgICAgaWQ9ImcyMiIKICAgICAgICAgICB0cmFuc2Zvcm09InRyYW5zbGF0ZSgyLjc1NDksMTIuNTEwNSkiPgogICAgICAgICAgPHBhdGgKICAgICAgICAgICAgIGQ9Im0gMCwwIHYgLTEuMzY4IGMgLTAuMTc4LDAgLTAuMzQ3LDAuMDA1IC0wLjUxNiwtMTBlLTQgLTAuNTk1LC0wLjAyMiAtMC44NTQsLTAuMjgyIC0wLjg1NiwtMC44NzYgLTAuMDA0LC0xLjI1NSAtMC4wMDIsLTIuNTA5IC0xMGUtNCwtMy43NjQgMCwtMC43MzggMC4yMTgsLTAuOTU1IDAuOTU1LC0wLjk2MSAwLjEyOCwtMC4wMDEgMC4yNTcsMCAwLjQxMSwwIHYgLTEuMzIxIGMgLTEuMzM3LC0wLjIyMiAtMi42NzgsMC40MzYgLTIuNzI0LDIuMDkzIC0wLjAzOCwxLjM1MiAtMC4wMjQsMi43MDYgLTAuMDAzLDQuMDU4IDAuMDIsMS4yNTYgMC44ODksMi4wOTUgMi4xNzEsMi4xNCBDIC0wLjM5LDAuMDA1IC0wLjIxNywwIDAsMCIKICAgICAgICAgICAgIHN0eWxlPSJmaWxsOiNmZmZmZmY7ZmlsbC1vcGFjaXR5OjE7ZmlsbC1ydWxlOm5vbnplcm87c3Ryb2tlOm5vbmUiCiAgICAgICAgICAgICBpZD0icGF0aDI0IiAvPgogICAgICAgIDwvZz4KICAgICAgICA8ZwogICAgICAgICAgIGlkPSJnMjYiCiAgICAgICAgICAgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMjUuMTA0LDQuMjEyNikiPgogICAgICAgICAgPHBhdGgKICAgICAgICAgICAgIGQ9Ik0gMCwwIFYgMS4zMyBDIDAuMjA3LDEuMzMgMC40LDEuMzIxIDAuNTkyLDEuMzMyIDEuMDk4LDEuMzYgMS4zNTMsMS41OCAxLjM1OSwyLjA3OCAxLjM3NiwzLjQ1MiAxLjM3Myw0LjgyNiAxLjM1OCw2LjIgMS4zNTQsNi42MTggMS4xMSw2Ljg1MiAwLjY4Nyw2LjkxIDAuNDczLDYuOTM5IDAuMjUzLDYuOTM0IDAuMDA2LDYuOTQ2IFYgOC4yNDIgQyAxLjI4Miw4LjQ5MyAyLjY3NSw3LjgzIDIuNzI0LDYuMTczIDIuNzY0LDQuODExIDIuNzU1LDMuNDQ2IDIuNzI2LDIuMDgzIDIuNjk1LDAuNjQ2IDEuNTAxLC0wLjI1NiAwLDAiCiAgICAgICAgICAgICBzdHlsZT0iZmlsbDojZmZmZmZmO2ZpbGwtb3BhY2l0eToxO2ZpbGwtcnVsZTpub256ZXJvO3N0cm9rZTpub25lIgogICAgICAgICAgICAgaWQ9InBhdGgyOCIgLz4KICAgICAgICA8L2c+CiAgICAgICAgPGcKICAgICAgICAgICBpZD0iZzMwIgogICAgICAgICAgIHRyYW5zZm9ybT0idHJhbnNsYXRlKDE2LjcwMjEsMTEuMTI3NykiPgogICAgICAgICAgPHBhdGgKICAgICAgICAgICAgIGQ9Ik0gMCwwIEMgLTAuMDAxLC0wLjgwNyAwLjU5MywtMS40MDUgMS4zOTksLTEuNDA3IDIuMTk5LC0xLjQwOSAyLjgwNywtMC43OTkgMi44MDIsMCAyLjc5OCwwLjgxIDIuMjA0LDEuNDA1IDEuMzk5LDEuNDA1IDAuNTk2LDEuNDA1IDAuMDAyLDAuODA5IDAsMCBtIC0xLjM2NiwwLjAwNSBjIDAuMDEyLDEuNTA5IDEuMjg4LDIuNzc4IDIuNzgsMi43NjYgMS40OTUsLTAuMDEyIDIuNzU4LC0xLjI5MiAyLjc2MSwtMi43OTUgMC4wMDIsLTEuNDY3IC0xLjI5MywtMi43NTEgLTIuNzc0LC0yLjc1MSAtMS40OTksMCAtMi43NzksMS4yODYgLTIuNzY3LDIuNzgiCiAgICAgICAgICAgICBzdHlsZT0iZmlsbDojZmZmZmZmO2ZpbGwtb3BhY2l0eToxO2ZpbGwtcnVsZTpub256ZXJvO3N0cm9rZTpub25lIgogICAgICAgICAgICAgaWQ9InBhdGgzMiIgLz4KICAgICAgICA8L2c+CiAgICAgICAgPGcKICAgICAgICAgICBpZD0iZzM0IgogICAgICAgICAgIHRyYW5zZm9ybT0idHJhbnNsYXRlKDExLjE0OTksMTEuMTE0NSkiPgogICAgICAgICAgPHBhdGgKICAgICAgICAgICAgIGQ9Ik0gMCwwIEMgMC4wMDMsMC44MTIgLTAuNTg0LDEuNDEyIC0xLjM4NywxLjQxOCAtMi4xOTQsMS40MjMgLTIuNzg4LDAuODM5IC0yLjgwMiwwLjAyNSAtMi44MTUsLTAuNzc0IC0yLjIxOCwtMS4zODQgLTEuNDEzLC0xLjM5NSAtMC42MTMsLTEuNDA1IC0wLjAwMiwtMC44MDIgMCwwIG0gMS4zNjUsMC4wMTYgYyAwLjAwMSwtMS41MDEgLTEuMjc3LC0yLjc4MiAtMi43NjksLTIuNzc4IC0xLjQ5MywwLjAwNSAtMi43NzQsMS4yOTQgLTIuNzY5LDIuNzg2IDAuMDA1LDEuNDcgMS4yOTEsMi43NTUgMi43NjEsMi43NjEgMS40OTQsMC4wMDcgMi43NzYsLTEuMjczIDIuNzc3LC0yLjc2OSIKICAgICAgICAgICAgIHN0eWxlPSJmaWxsOiNmZmZmZmY7ZmlsbC1vcGFjaXR5OjE7ZmlsbC1ydWxlOm5vbnplcm87c3Ryb2tlOm5vbmUiCiAgICAgICAgICAgICBpZD0icGF0aDM2IiAvPgogICAgICAgIDwvZz4KICAgICAgICA8ZwogICAgICAgICAgIGlkPSJnMzgiCiAgICAgICAgICAgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMTMuOTQ3OCw1LjUyODUpIj4KICAgICAgICAgIDxwYXRoCiAgICAgICAgICAgICBkPSJtIDAsMCBjIDAuMjM5LDAgMC40OCwwLjAxNyAwLjcxNywtMC4wMDMgMC4zNzMsLTAuMDMyIDAuNjEyLC0wLjI4MyAwLjYzLC0wLjYyOCAwLjAyLC0wLjM4MSAtMC4xOCwtMC42NjMgLTAuNTc3LC0wLjY4OSAtMC41NCwtMC4wMzQgLTEuMDg2LC0wLjAzIC0xLjYyNiwwLjAwOCAtMC4zNTcsMC4wMjUgLTAuNTQzLDAuMjk5IC0wLjUzOCwwLjY1OSAwLjAwNSwwLjMxMyAwLjIyOSwwLjYgMC41NDcsMC42MzUgMC4yNzksMC4wMyAwLjU2NCwwLjAwNiAwLjg0NywwLjAwNiAwLDAuMDA0IDAsMC4wMDggMCwwLjAxMiIKICAgICAgICAgICAgIHN0eWxlPSJmaWxsOiNmZmZmZmY7ZmlsbC1vcGFjaXR5OjE7ZmlsbC1ydWxlOm5vbnplcm87c3Ryb2tlOm5vbmUiCiAgICAgICAgICAgICBpZD0icGF0aDQwIiAvPgogICAgICAgIDwvZz4KICAgICAgICA8ZwogICAgICAgICAgIGlkPSJnNDIiCiAgICAgICAgICAgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoOS43Nyw1LjUyOSkiPgogICAgICAgICAgPHBhdGgKICAgICAgICAgICAgIGQ9Im0gMCwwIGMgMC4yMzksMCAwLjQ4LDAuMDE3IDAuNzE4LC0wLjAwMyAwLjM3MywtMC4wMzIgMC42MzgsLTAuMzE4IDAuNjM4LC0wLjY2IDEwZS00LC0wLjM2IC0wLjIwMywtMC42MzQgLTAuNTgzLC0wLjY1OCAtMC41NCwtMC4wMzMgLTEuMDg1LC0wLjAzIC0xLjYyNSwwLjAwOSAtMC4zNjMsMC4wMjUgLTAuNTM1LDAuMjgzIC0wLjUzMiwwLjY1OCAwLjAwMywwLjMyNiAwLjIxLDAuNTk4IDAuNTM3LDAuNjM1IDAuMjc5LDAuMDMyIDAuNTY0LDAuMDA3IDAuODQ3LDAuMDA3IHoiCiAgICAgICAgICAgICBzdHlsZT0iZmlsbDojZmZmZmZmO2ZpbGwtb3BhY2l0eToxO2ZpbGwtcnVsZTpub256ZXJvO3N0cm9rZTpub25lIgogICAgICAgICAgICAgaWQ9InBhdGg0NCIgLz4KICAgICAgICA8L2c+CiAgICAgICAgPGcKICAgICAgICAgICBpZD0iZzQ2IgogICAgICAgICAgIHRyYW5zZm9ybT0idHJhbnNsYXRlKDE4LjEwMDEsNC4xNTYpIj4KICAgICAgICAgIDxwYXRoCiAgICAgICAgICAgICBkPSJNIDAsMCBDIC0wLjI4MiwwLjAxOCAtMC41NjcsMC4wMTcgLTAuODQ1LDAuMDU5IC0xLjE4MSwwLjExIC0xLjM2NiwwLjM1NSAtMS4zNzUsMC42OTkgLTEuMzgzLDEgLTEuMTUxLDEuMzMyIC0wLjg0NSwxLjM0OSAtMC4yODIsMS4zOCAwLjI4NiwxLjM4MSAwLjg0OSwxLjM0OCAxLjE2OCwxLjMyOSAxLjM3LDEuMDI3IDEuMzY2LDAuNjk4IDEuMzYyLDAuMzM5IDEuMTkzLDAuMTEyIDAuODQ1LDAuMDU5IDAuNTY3LDAuMDE3IDAuMjgzLDAuMDE4IDAsMCIKICAgICAgICAgICAgIHN0eWxlPSJmaWxsOiNmZmZmZmY7ZmlsbC1vcGFjaXR5OjE7ZmlsbC1ydWxlOm5vbnplcm87c3Ryb2tlOm5vbmUiCiAgICAgICAgICAgICBpZD0icGF0aDQ4IiAvPgogICAgICAgIDwvZz4KICAgICAgPC9nPgogICAgPC9nPgogIDwvZz4KPC9zdmc+Cg=="
                         alt="Generar por IA">
                    <span wire:loading.remove wire:target="generarRespuestaIA">Generar por IA</span>
                    <span wire:loading wire:target="generarRespuestaIA">Generando...</span>
                    @if(!Auth::user()->hasAnyRole('root') && Auth::user()->status == 0)
                        <!-- Overlay transparente -->
                        <span class="absolute inset-0 bg-black/30 z-10 rounded-[8px]">
                            <a href="{{route('planes')}}"
                               target="_blank"
                               class="px-4 py-2 z-10 md:px-6 md:py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-bold rounded-full shadow-lg hover:scale-105 transition flex items-center justify-center text-[13px] md:tex-base opacity-0 hover:opacity-100">
                                🔒 Hazte PRO
                            </a>
                        </span>
                    @endif
                </button>
                <label for="respuesta" class="block text-gray-700">Respuesta <span class="text-red-500">*</span></label>
                <textarea
                    id="respuesta"
                    wire:model.live="respuesta"
                    class="mt-1 block w-full rounded border-gray-300 focus:border-[#195b81] focus:ring-[#195b81] "
                    rows="1"></textarea>
                @error('respuesta') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
            </div>
            <!-- Campos opcionales -->
            <div class="group-form">
                <div class="mb-4">
                    <label for="url" class="block text-gray-700">URL</label>
                    <input type="text" id="url" wire:model="url"
                           class="mt-1 block w-full rounded border-gray-300  focus:border-[#195b81] focus:ring-[#195b81] ">
                    @error('url') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label for="imagen" class="block text-gray-700">Imagen</label>
                    <input type="file" id="imagen" wire:model="imagen" accept="image/*"
                           class=" w-full text-sm text-grey-500 file:mr-5 file:py-2 file:px-6 file:rounded-full file:border-0 file:text-sm file:font-medium file:bg-blue-80 file:sky-950 hover:file:cursor-pointer">
                    @error('imagen') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="group-form">
                <div class="mb-4">
                    <label for="url_respuesta" class="block text-gray-700">URL Respuesta</label>
                    <input type="text" id="url_respuesta" wire:model="url_respuesta"
                           class="focus:border-[#195b81] focus:ring-[#195b81]  mt-1 block w-full rounded border-gray-300  ">
                    @error('url_respuesta') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
                <div class="mb-4">
                    <label for="imagen_respuesta" class="block text-gray-700">Imagen Respuesta</label>
                    <input type="file" id="imagen_respuesta" wire:model="imagen_respuesta" accept="image/*"
                           class=" w-full text-sm text-grey-500 file:mr-5 file:py-2 file:px-6 file:rounded-full file:border-0 file:text-sm file:font-medium file:bg-blue-80 file:sky-950 hover:file:cursor-pointer ">
                    @error('imagen_respuesta') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>
            </div>
            <!-- Sección opcional para asignar categorías a la flashcard -->
            <div class="mb-4">
                <label class="block text-gray-700">Categorías (opcional)</label>
                <div class="mt-2 space-x-4">
                    @foreach ($availableCategories as $category)
                        <label class="inline-flex items-center">
                            <input type="checkbox" wire:model="selectedCategories" value="{{ $category->id }}"
                                   class="focus:border-[#195b81] focus:ring-[#195b81]  rounded border-gray-300 text-indigo-600  checkbox-form ">
                            <span class="ml-2">{{ $category->nombre }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
            <button type="submit"
                    class="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700 boton-success-m">Crear
                Flashcard
            </button>
        </form>
    </div>

    <hr class="border-gray-300">

    <!--  seleccion de Tarjetas -->
    <div class="bg-white max-w-full border-none shadow-none p-[40px] rounded-[20px] mb-10">
        <!-- Botón seleccionar todas -->
        <div class="flex items-center justify-between mb-2">
            <span></span>
            @if(!Auth::user()->hasAnyRole('root') && Auth::user()->status == 0)
                <div class="relative inline-block">
                    <button type="button"
                            wire:click="toggleSelectAll"
                            class="flex md:w-auto w-full mb-5 md:mb-0 items-center justify-center gap-2 transition duration-300 rounded-[8px] text-[15px] font-medium px-[25px] py-[10px] text-white
            {{ count($selectedCards) === $cards->count() ? 'bg-[#4a6868]' : 'bg-[#5b8080] hover:bg-[#4a6868]' }}">

                        {{-- Ícono dinámico --}}
                        @if(count($selectedCards) === $cards->count())
                            {{-- Icono X (deseleccionar todas) --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        @else
                            {{-- Icono Check (seleccionar todas) --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 13l4 4L19 7"/>
                            </svg>
                        @endif

                        <span>
            {{ count($selectedCards) === $cards->count() ? 'Deseleccionar todas' : 'Seleccionar todas' }}
        </span>
                    </button>

                    <div
                        class="absolute inset-0 bg-black/30 backdrop-blur-[1px] z-10 flex items-center justify-center rounded-lg pointer-events-auto">
                        <a href="{{route('planes')}}"
                           target="_blank"
                           class="px-4 py-2 md:px-6 md:py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-bold rounded-full shadow-lg hover:scale-105 transition flex items-center justify-center text-[13px] md:tex-base opacity-0 hover:opacity-100">
                            🔒 Hazte PRO
                        </a>
                    </div>
                </div>
            @else
                <div class="">
                    <button type="button"
                            wire:click="toggleSelectAll"
                            class="flex md:w-auto w-full mb-5 md:mb-0 items-center justify-center gap-2 transition duration-300 rounded-[8px] text-[15px] font-medium px-[25px] py-[10px] text-white
            {{ count($selectedCards) === $cards->count() ? 'bg-[#4a6868]' : 'bg-[#5b8080] hover:bg-[#4a6868]' }}">

                        {{-- Ícono dinámico --}}
                        @if(count($selectedCards) === $cards->count())
                            {{-- Icono X (deseleccionar todas) --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        @else
                            {{-- Icono Check (seleccionar todas) --}}
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24"
                                 stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M5 13l4 4L19 7"/>
                            </svg>
                        @endif

                        <span>
            {{ count($selectedCards) === $cards->count() ? 'Deseleccionar todas' : 'Seleccionar todas' }}
        </span>
                    </button>
                </div>
            @endif
        </div>

        <!-- Tabs -->
        <div class="border-b box-cat-flash">
            <nav class="-mb-px flex space-x-4 overflow-x-auto no-scrollbar" style="max-width: 100%;" aria-label="Tabs">
                <button type="button"
                        wire:click="setActiveTab('sin-categoria')"
                        class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm
                    {{ $activeTab === 'sin-categoria' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    Sin Categoría
                </button>
                @foreach($availableCategories as $cat)
                    <button type="button"
                            wire:click="setActiveTab('cat-{{ $cat->id }}')"
                            class="whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm md:w-auto w-1/2
                        {{ $activeTab === 'cat-'.$cat->id ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        {{ $cat->nombre }}
                    </button>
                @endforeach
            </nav>
        </div>

        <!-- Contenido pestañas (solo se muestra la activa) -->
        <div class="mt-6">
            {{-- SIN CATEGORÍA --}}
            @if($activeTab === 'sin-categoria')
                @include('livewire.flashcard.slider', [
                    'tabId' => 'sin-categoria',
                    'cardsToShow' => $filteredTabs['sin-categoria'],
                    'searchTerm' => $searchTerms['sin-categoria'] ?? '',
                    'selectedCards' => $selectedCards,
                    'slidersScroll' => $slidersScroll
                ])
            @endif

            {{-- POR CATEGORÍA --}}
            @foreach($availableCategories as $cat)
                @php $catTab = 'cat-' . $cat->id; @endphp
                @if($activeTab === $catTab)
                    @include('livewire.flashcard.slider', [
                        'tabId' => $catTab,
                        'cardsToShow' => $filteredTabs[$catTab],
                        'searchTerm' => $searchTerms[$catTab] ?? '',
                        'selectedCards' => $selectedCards,
                        'slidersScroll' => $slidersScroll
                    ])
                @endif
            @endforeach
        </div>
        <div class="flex justify-end mt-6">
            <button
                wire:click="startGame"
                @disabled(count($selectedCards) === 0)
                class="tw-button rounded text-base font-semibold primary-button text-white  transition md:w-auto w-full
                {{ count($selectedCards) ? 'bg-[#195b81] hover:primary-button-hover' : 'bg-gray-400 cursor-not-allowed' }}">
                Iniciar Juego
            </button>
        </div>

    </div>
    @if($showEditModal)
        <div class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-40 z-50">
            <div class="bg-white rounded-lg shadow-lg p-8 w-full max-w-lg relative">
                <button wire:click="$set('showEditModal', false)"
                        class="absolute top-2 right-2 text-gray-400 hover:text-gray-700 text-2xl">&times;
                </button>
                <h2 class="text-xl font-bold mb-4">Editar Flashcard</h2>
                <form wire:submit.prevent="updateCard">
                    <div class="mb-4">
                        <label class="block mb-1 font-semibold">Pregunta</label>
                        <textarea wire:model.defer="pregunta" class="w-full border rounded px-3 py-2"
                                  required></textarea>
                        @error('pregunta') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block mb-1 font-semibold">Respuesta</label>
                        <textarea wire:model.defer="respuesta" class="w-full border rounded px-3 py-2"
                                  required></textarea>
                        @error('respuesta') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block mb-1">URL</label>
                        <input type="text" wire:model.defer="url" class="w-full border rounded px-3 py-2">
                        @error('url') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="mb-4">
                        <label class="block mb-1">URL Respuesta</label>
                        <input type="text" wire:model.defer="url_respuesta" class="w-full border rounded px-3 py-2">
                        @error('url_respuesta') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" wire:click="$set('showEditModal', false)"
                                class="px-4 py-2 bg-gray-300 rounded">Cancelar
                        </button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>

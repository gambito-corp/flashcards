<div class="fixed inset-0 top-[80px] left-0 right-0 bottom-0 z-10 bg-[#f3f8fd] flex">
    <div id="main-content"
         class="w-full  flex flex-col bg-[#f3f8fd] md:ml:48 ml-0 overflow-x-hidden transition-all duration-300">
        <!-- Overlay para sidebar en móvil -->
        <div id="sidebarOverlay" class="fixed inset-0 z-20 hidden bg-black overlay bg-opacity-30"
             onclick="closeSidebar()"></div>


        <!-- Sidebar con resizer -->
        <aside id="sidebar"
               class="fixed left-0 z-30 flex flex-col transition-all duration-300 bg-white shadow-lg sidebar-chat">
            <!-- Botón "+" para nuevo chat -->
            <div class="flex items-center justify-between px-3 pt-3 pb-2">
                <span class="font-bold text-[#195b81] text-lg">Chats</span>
                <button wire:click="createNewChat"
                        class="bg-[#195b81] text-white rounded-full w-8 h-8 flex items-center justify-center hover:bg-[#1a6ca6] transition"
                        title="Nuevo chat">
                    <i class="fa-solid fa-plus"></i>
                </button>
            </div>
            <hr class="mb-2">
            <!-- Lista de chats -->
            <nav class="flex-1 px-3 overflow-y-auto">
                @foreach($groupedChats as $group => $chats)
                    <div class="mt-4">
                        <button type="button"
                                wire:click="toggleChatGroup('{{ $group }}')"
                                class="flex items-center w-full gap-2 px-2 mb-2 text-xs font-medium tracking-wider text-gray-500 uppercase select-none focus:outline-none">
                            <i class="fa-solid transition-transform duration-200 {{ $chatGroupsOpen[$group] ? 'fa-chevron-down' : 'fa-chevron-right' }}"></i>
                            {{ $group }}
                        </button>
                        <div class="transition-all duration-200"
                             style="{{ $chatGroupsOpen[$group] ? '' : 'display:none;' }}">
                            @foreach($chats as $chat)
                                <div class="flex items-center group">
                                    <button wire:click="selectChat({{ $chat->id }})"
                                            class="flex-1 w-full text-left flex items-center gap-2 px-2 py-2 mb-1 rounded-lg
                                            transition hover:bg-[#f3f6fb]
                                            {{ $activeChatId == $chat->id ? 'bg-[#f3f6fb] text-[#195b81] font-bold' : 'text-gray-800' }}">
                                        <i class="fa-regular fa-message"></i>
                                        <span class="truncate">{{ $chat->title ?? "Chat #{$chat->id}" }}</span>
                                    </button>
                                    <!-- Botón lápiz -->
                                    <button wire:click="openEditModal({{ $chat->id }})"
                                            class="ml-2 text-[#195b81] opacity-70 hover:opacity-100 transition"
                                            title="Editar nombre">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                    <!-- Botón eliminar -->
                                    <button wire:click="openDeleteModal({{ $chat->id }})"
                                            class="ml-2 text-red-500 transition opacity-70 hover:opacity-100"
                                            title="Eliminar chat">
                                        <i class="fa-solid fa-trash" aria-hidden="true"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>
            <!-- Barra para redimensionar -->
            <div id="sidebarResizer" class="sidebar-resizer"></div>
        </aside>
        <!-- Botón hamburguesa para mostrar/ocultar sidebar -->
        <button id="sidebarToggle" class="sidebar-toggle">
            <i class="fa-solid fa-bars"></i>
        </button>

        <!-- Área principal del chat -->
        <div class="flex flex-col flex-1 h-full">
            <div wire:loading="sendMessage" class="flex justify-start ml-40">
                <div class="flex justify-start w-4/5">
                    <div class="bg-white p-4 rounded-2xl shadow border border-[#d9e6f7] text-left">
                        <div class="text-[#195b81] animate-pulse">
                            <i class="mr-2 fa-solid fa-spinner fa-spin"></i> cargando...
                        </div>
                    </div>
                </div>
            </div>
            @if($messages->isEmpty())
                <div class="flex  mt-8 flex-col items-center justify-center h-full w-full bg-[#f3f8fd] ">
                    <!-- Logo y título -->
                    <h1 class="text-4xl md:text-5xl font-extrabold text-[#195b81]  mb-2">DoctorMBS</h1>
                    <div class="text-lg md:text-xl text-[#195b81] font-semibold mb-1  mx-5 md:mx-0 text-center">
                        Respuestas <span class="text-[#3b82f6]">científicas</span> a preguntas médicas
                    </div>

                    <div class="w-full max-w-2xl mt-8 mb-8 ">
                        <div class="flex inset-x-0 mx-auto max-w-full items-center bg-white border-2 border-[#3b82f6] rounded-2xl px-4 md:px-6  shadow-lg
                            flex-wrap md:inset-auto md:mx-0 md:max-w-none h-[140px] mx-5 md:mx-0">
                            <input type="text"
                                   wire:model.defer="newMessage"
                                   wire:keydown.enter="sendMessage"
                                   class="relative flex-1 text-lg bg-transparent border-0 focus:ring-0 focus:outline-none text-[#195b81] placeholder-[#b0b8c1]"
                                   placeholder="Haz una pregunta de salud o biociencia...">
                            <button wire:click='sendMessage'
                                    class="relative ml-4 bg-[#66acff] hover:bg-[#195b81] text-white p-2 rounded-full flex items-center justify-center shadow transition h-[40px] w-[40px] hidden md:block">
                                <i class="text-lg fa-solid fa-magnifying-glass"></i>
                            </button>
                            <div class="flex w-full">
                                <button wire:click='openFilters'
                                        class="relative ml-3 text-[#195b81] hover:text-[#1a6ca6] flex items-center gap-1 font-semibold mr-3 md:mr-5">
                                    <i class="fa-solid fa-sliders"></i>
                                    <span class="ml-1 font-[14pz]">Filtros</span>
                                </button>
                                <label class="inline-flex items-center cursor-pointer">
                                    <input wire:model.live="deepResearch" type="checkbox" class="sr-only peer">
                                    <div class="relative w-14 h-7 bg-gray-200 rounded-full peer-focus:outline-none
                                    dark:peer-focus:ring-blue-800 dark:bg-gray-300
                                    peer-checked:bg-blue-600 dark:peer-checked:bg-blue-600
                                    peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full
                                    peer-checked:after:border-white
                                    after:content-[''] after:absolute after:top-[2px] after:start-[2px]
                                    after:bg-white after:border-gray-300 after:border
                                    after:rounded-full after:h-6 after:w-6 after:transition-all
                                    dark:border-gray-600">
                                    </div>
                                    <span class="ms-1 text-sm font-medium text-[#195b81] md:ms-3">Medisearch</span>
                                </label>
                                <button wire:click='sendMessage'
                                        class="relative ml-4 bg-[#66acff] hover:bg-[#195b81] text-white p-2 rounded-full flex items-center justify-center shadow transition md:h-[40px] h-[35px] md:w-[40px] w-[35px] md:hidden">
                                    <i class="text-lg fa-solid fa-magnifying-glass"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="w-full max-w-2xl space-y-8 px-8 md:px-0">
                        <div class="space-y-2">
                            <div class="text-center gap-2 text-sm font-semibold text-gray-600">
                                <i class="fa-regular fa-lightbulb"></i>
                                Comienza con preguntas comunes
                            </div>
                            <div class="relative w-full max-w-md mx-auto overflow-visible">
                                @foreach($questionsBasic as $index => $question)
                                    @if($current === $index)
                                        <div class="transition-all duration-300">
                                            <button
                                                wire:click="$set('question', '{{ $question }}')"
                                                class="w-full bg-white border border-[#b0b8c1] text-[#195b81] rounded-xl px-4 py-3 text-sm shadow hover:bg-[#f3f6fb] transition text-left">
                                                {{ $question }}
                                            </button>
                                        </div>
                                    @endif
                                @endforeach
                                <div class="absolute z-10 -translate-y-1/2 -left-4 top-1/2 md:-left-10">
                                    <button wire:click="previousQuestion"
                                            class="p-2 bg-white rounded-full shadow hover:bg-gray-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-700"
                                             fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15 19l-7-7 7-7"/>
                                        </svg>
                                    </button>
                                </div>

                                <div class="absolute z-10 -translate-y-1/2 -right-4 top-1/2 md:-right-10">
                                    <button wire:click="nextQuestion"
                                            class="p-2 bg-white rounded-full shadow hover:bg-gray-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-700"
                                             fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>


                        {{-- Carrusel 2: Preguntas complejas --}}
                        <div class="space-y-2">
                            <div class="text-center gap-2 text-sm font-semibold text-gray-600">
                                <i class="fa-solid fa-flask"></i>
                                Profundiza en preguntas complejas
                            </div>

                            <div class="relative w-full max-w-md mx-auto overflow-visible">
                                @foreach($questionsAdvanced as $index => $question)
                                    @if($currentAdvance === $index)
                                        <div class="transition-all duration-300">
                                            <button
                                                wire:click="$set('question', '{{ $question }}')"
                                                class="w-full bg-white border border-[#b0b8c1] text-[#195b81] rounded-xl px-4 py-3 text-sm shadow hover:bg-[#f3f6fb] transition text-left">
                                                {{ $question }}
                                            </button>
                                        </div>
                                    @endif
                                @endforeach

                                <div class="absolute z-10 -translate-y-1/2 -left-4 top-1/2 md:-left-10">
                                    <button wire:click="nextQuestionAdvance"
                                            class="p-2 bg-white rounded-full shadow hover:bg-gray-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-700"
                                             fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M15 19l-7-7 7-7"/>
                                        </svg>
                                    </button>
                                </div>

                                <div class="absolute z-10 -translate-y-1/2 -right-4 top-1/2 md:-right-10">
                                    <button wire:click="previousQuestionAdvance"
                                            class="p-2 bg-white rounded-full shadow hover:bg-gray-100">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-gray-700"
                                             fill="none"
                                             viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            @else

                <!-- Estado 2: Chat activo -->
                <div class="flex flex-col h-full bg-[#f3f8fd]">
                    <!-- Encabezado del chat -->
                    <div id="date-content"
                         class="flex items-center justify-between p-4 ml-0 md:ml-48 bg-white shadow flex-wrap">
                        <h2 class="text-sm md:text-xl  font-bold text-[#195b81]">
                            {{ $activeChatTitle }}
                        </h2>
                        <button
                            class="history-chat-button  bg-white z-40 px-5 py-2 rounded-full text-[14px] shadow-md md:hidden flex items-center gap-2"
                            onclick="openSidebar()">
                            <i class="fa-regular fa-message"></i> Historial
                        </button>
                        @if(!Auth::user()->hasAnyRole('root') && Auth::user()->status == 0)
                            <a
                                href="{{ route('planes') }}"
                                class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-bold rounded-full shadow-lg text-lg hover:scale-105 transition flex items-center justify-center"
                            >
                                Actualizar a Pro
                                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </a>

                        @endif
                    </div>

                    <!-- Área de mensajes -->
                    <div class="flex-1 p-4 overflow-y-auto space-y-3" id="chat-messages">
                        @foreach($messages as $message)

                            @if($message['from'] === 'user')
                                <div class="pt-3 flex">
                                    <div class=" text-[17px]   py-2  my-2 w-fit text-[#195b81] font-semibold "><i class="fa-regular fa-comment mr-1"></i>
                                        {{ $message['text'] }}
                                    </div>
                                </div>
                            @elseif($message['from'] === 'articles')
                                <div class="flex flex-wrap gap-4 relative article-section">
                                    @foreach($message['data'] as $index => $article)
                                    @php
                                        $url = $article['url'];
                                        $fecha = isset($article['fecha']) ? $article['fecha'] : $article['year'];
                                        $fuente = isset($article['fuente']) ? $article['fuente'] : $article['source'];
                                        $titulo = isset($article['titulo']) ? $article['titulo'] : $article['title'];
                                        $autores = isset($article['autores']) ? $article['autores'] : $article['authors'];
                                        $resumen = isset($article['resumen']) ? $article['resumen'] : $article['summary'];
                                        $tipoEstudio = isset($article['tipo_estudio']) ? $article['tipo_estudio'] : $article['journal'];
                                    @endphp

                                        <div class="md:w-[15rem] w-full md-1 md:mb-4 bg-white p-3 h-[85px] rounded-[15px] article-card {{ $index > 3 ? 'hidden' : '' }}">
                                            <a href="{{ $url }}" class="text-sm" target="_blank">
                                                <h3 class="font-bold text-[13px] leading-[18px] text-[#195b81] line-clamp-2">
                                                    {{$titulo}}
                                                </h3>
                                                <p class="text-xs text-gray-500 line-clamp-1">
                                                    {{ $tipoEstudio }} - {{ $fecha }}
                                                </p>
                                            </a>
                                        </div>
                                        @if ($index === 3)
                                            <button class="verMas absolute bottom-[12px] right-[-7px] w-[23px] h-[23px] bg-[#195b81] rounded-full">
                                                <i class="fa-solid fa-plus text-white"></i>
                                            </button>
                                        @endif
                                    @endforeach
                                </div>
                                <hr class="w-full ">
                            @elseif($message['from'] === 'bot')
                                <div class="flex justify-start ">
                                    <span  class="bot-text text-[15px] md:text-[16px] text-[#333333] leading-[32px] mb-5 font-medium">"{!! $message['text']  !!} "</span>
                                </div>
                            @endif
                        @endforeach
                        <!-- Mensaje de "Razonando..." mientras se procesa el envío -->
                        <div wire:loading.delay wire:target="sendMessage" class="flex justify-start">
                            <div class="flex items-center bg-[#dfe9ef] text-gray-800 px-4 py-2 rounded-lg my-2">
                                <i class="fa-regular fa-hourglass animate-spin-360 text-xl  mr-4"></i>
                                <span class="mr-2">Respondiendo<span class="dots"></span></span>
                            </div>
                        </div>
                    </div>
                    <!-- Input de seguimiento -->
                    <div class="p-4 bg-white border-t border-[#d9e6f7]">
                        <div id="chat-content"
                            class="flex flex-wrap items-center px-4 py-4 md:px-6 ml-0 md:ml-48 shadow py-4bg-white rounded-2xl">
                            <input type="text"
                                wire:model.live="newMessage"
                                class="flex-1 text-lg bg-transparent border-0 focus:ring-0 focus:outline-none text-[#195b81]"
                                placeholder="Haz una pregunta de seguimiento...">
                            <button wire:click='openFilters'
                                    class="ml-3 text-[#195b81] hover:text-[#1a6ca6] flex items-center gap-1 font-semibold md:order-none order-1">
                                <i class="fa-solid fa-sliders"></i>
                                <span class="ml-1">Filtros</span>
                            </button>
                            <label
                                class="inline-flex items-center cursor-pointer ml-4 select-none md:order-none order-1">
                                <!-- Hidden checkbox with Livewire binding -->
                                <input wire:model="deepResearch" type="checkbox" class="sr-only peer">

                                <!-- Toggle switch styling -->
                                <div class="relative w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300
                                    dark:peer-focus:ring-blue-800 rounded-full peer dark:bg-gray-700
                                    peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full
                                    peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px]
                                    after:start-[2px] after:bg-white after:border-gray-300 after:border
                                    after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600
                                    peer-checked:bg-[#195b81] dark:peer-checked:bg-[#195b81]">
                                </div>

                                <!-- Label text -->
                                <span class="ms-3 text-[13px] md:text-sm font-bold text-[#b0b8c1]">Investigacion Profunda</span>
                            </label>

                            <button wire:click="sendMessage"
                                    class="order-0 relative ml-4 bg-[#66acff] hover:bg-[#195b81] text-white p-2 rounded-full flex items-center justify-center shadow transition md:h-[40px] h-[35px] md:w-[40px] w-[35px] md:order-none order-0">
                                <i class="fa-solid fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>


    @if($showEditModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div class="relative w-full max-w-sm p-6 bg-white shadow-lg rounded-xl">
                <button class="absolute text-gray-400 top-3 right-3 hover:text-gray-600"
                        wire:click="closeEditModal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <h2 class="text-lg font-bold text-[#195b81] mb-4">Editar nombre del chat</h2>
                <form wire:submit.prevent="saveChatName">
                    <input type="text"
                           wire:model.defer="editChatName"
                           class="w-full border rounded-lg px-3 py-2 mb-4 focus:border-[#195b81] focus:ring-[#195b81]"
                           placeholder="Nuevo nombre para el chat"
                           maxlength="50"
                           required
                    >
                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="closeEditModal"
                                class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 rounded-md bg-[#195b81] text-white hover:bg-[#1a6ca6]">Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
    @if($showDeleteModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-40">
            <div class="relative w-full max-w-sm p-6 bg-white shadow-lg rounded-xl">
                <button class="absolute text-gray-400 top-3 right-3 hover:text-gray-600"
                        wire:click="closeDeleteModal">
                    <i class="fa-solid fa-xmark"></i>
                </button>
                <h2 class="text-lg font-bold text-[#195b81] mb-4">Editar nombre del chat</h2>
                <form wire:submit.prevent="deleteChat">
                    <h1>Eliminar Este Chat</h1>
                    <div class="flex justify-end gap-2">
                        <button type="button" wire:click="closeDeleteModal"
                                class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Cancelar
                        </button>
                        <button type="submit"
                                class="px-4 py-2 text-white bg-red-500 rounded-md hover:bg-red-800">Eliminar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
    @if($openModalFilter)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/30">
            <div
                class="bg-white w-[90vw] max-w-md rounded-2xl p-6 shadow-lg animate-in fade-in zoom-in-95 overflow-y-auto max-h-[90vh]">
                {{-- Header --}}
                <div class="flex items-center justify-between pb-3 mb-4 border-b">
                    <h2 class="text-xl font-bold">Filtrar artículos</h2>
                    <button wire:click="closeFilters" class="text-2xl text-gray-600 hover:text-gray-800">&times;
                    </button>
                </div>
                {{-- Filtro de fechas --}}
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Desde</label>
                    <input type="number" min="1890" max="{{now()->year}}" wire:model="from_date" class="w-full p-2 mt-1 border rounded-md">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Hasta</label>
                    <input type="number" min="1890" max="{{now()->year}}" wire:model="to_date" class="w-full p-2 mt-1 border rounded-md">
                </div>
                @if($fontOptions[0] !== "PubMed")
                    {{-- Seleccionar/Deseleccionar todos --}}
                    <div class="flex justify-between mb-2">
                        <button wire:click="selectAll" class="text-sm text-blue-600 hover:underline">Seleccionar todos
                        </button>
                        <button wire:click="deselectAll" class="text-sm text-red-600 hover:underline">Deseleccionar
                            todos
                        </button>
                    </div>

                    {{-- Opciones de checkboxes --}}
                    <div class="space-y-2">
                        <label class="block mb-1 font-medium">Fuentes</label>
                        @foreach($fontOptions as $option)
                            <label class="flex items-center gap-2">
                                <input type="checkbox" wire:model="selectedOptions" value="{{ $option }}">
                                <span>{{ $option }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif
                {{-- Seleccionar/Deseleccionar todos --}}
                <div class="flex justify-between mb-2">
                    <button wire:click="selectTypeAll" class="text-sm text-blue-600 hover:underline">Seleccionar todos
                    </button>
                    <button wire:click="deselectTypeAll" class="text-sm text-red-600 hover:underline">Deseleccionar
                        todos
                    </button>
                </div>

                {{-- Opciones de checkboxes --}}
                <div class="space-y-2">
                    <label class="block mb-1 font-medium">Tipo de artículos científicos</label>
                    @foreach($typeOptions as $option)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="selectedTypeOptions" value="{{ $option }}">
                            <span>{{ $option }}</span>
                        </label>
                    @endforeach
                </div>

                {{-- Botón aplicar (opcional) --}}
                <div class="flex justify-end mt-6">
                    <button wire:click="closeFilters"
                            class="px-4 py-2 text-white bg-blue-600 rounded-md hover:bg-blue-700">
                        Aplicar filtros
                    </button>
                </div>
            </div>
        </div>
        @if(!Auth::user()->hasAnyRole('root') && Auth::user()->status == 0)
            <div class="absolute inset-0 bg-white/80 z-50 flex flex-col items-center justify-center rounded-2xl">
                <a
                    href="{{ route('planes') }}"
                    class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-bold rounded-full shadow-lg text-lg hover:scale-105 transition flex items-center justify-center"
                >
                    Actualizar a Pro
                    <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                    </svg>
                </a>
            </div>
        @endif
    @endif
    <script>
        // ===================Lettering===================
        // document.addEventListener("DOMContentLoaded", function () {
        //     let observer = new MutationObserver(() => {
        //         const botMessages = document.querySelectorAll(".bot-text");
        //         if (botMessages.length === 0) return;
        //         const lastMessage = botMessages[botMessages.length - 1];
        //         if (!lastMessage.classList.contains("animated")) {
        //             // Seleccionar la última sección antes de la animación y agregar "card-ati"
        //             const articleSections = document.querySelectorAll(".article-section");
        //             const lastSection = articleSections[articleSections.length - 1];
        //             if (lastSection) {
        //                 lastSection.classList.add("card-ati");
        //             }
        //             // Animar el texto y luego cambiar las clases
        //             animateText(lastMessage, () => {
        //                 // Remover "active-card" de todas las secciones
        //                 articleSections.forEach(section => section.classList.remove("active-card"));
        //                 if (lastSection) {
        //                     // Eliminar "card-ati" y agregar "active-card" después de la animación
        //                     lastSection.classList.remove("card-ati");
        //                     lastSection.classList.add("active-card");
        //                 }
        //             });
        //             lastMessage.classList.add("animated");
        //         }
        //     });
        //
        //     function typeEffect(element, speed) {
        //         const text = element.innerHTML;
        //         element.innerHTML = "";
        //         let i = 0;
        //
        //         function typing() {
        //             if (i < text.length) {
        //                 element.innerHTML += text.charAt(i);
        //                 i++;
        //                 setTimeout(typing, speed);
        //             }
        //         }
        //
        //         typing();
        //     }
        //
        //     const observera = new MutationObserver(function (mutationsList) {
        //         for (const mutation of mutationsList) {
        //             console.log(mutation);
        //             // Detectar cambios en texto o nodos
        //             if (mutation.type === 'childList' || mutation.type === 'characterData') {
        //                 const answerDiv = document.getElementById("answer-div");
        //                 if (answerDiv && !answerDiv.dataset.animated) {
        //                     console.log("Nuevo contenido detectado, aplicando efecto");
        //                     answerDiv.dataset.animated = "true";
        //                     typeEffect(answerDiv, 30);
        //                 }
        //             }
        //         }
        //     });
        //
        //     // Observar el contenedor general
        //     const container = document.getElementById("answer-content");
        //     if (container) {
        //         observera.observe(container, {
        //             childList: true,
        //             subtree: true,
        //             characterData: true // Escuchar también cambios de texto
        //         });
        //     }
        //
        // });
        // ======================== Fin del Lettering ?==============================


        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const resizer = document.getElementById('sidebarResizer');
            const toggle = document.getElementById('sidebarToggle');
            const overlay = document.getElementById('sidebarOverlay');
            const mainContent = document.getElementById('main-content');
            const dateContent = document.getElementById('date-content');
            const chatContent = document.getElementById('chat-content');
            let isResizing = false;

            // Función para redimensionar
            resizer.addEventListener('mousedown', function (e) {
                isResizing = true;
                document.body.style.cursor = 'ew-resize';
            });

            document.addEventListener('mousemove', function (e) {
                if (!isResizing) return;
                let newWidth = e.clientX;
                if (newWidth < 180) newWidth = 180;
                if (newWidth > 350) newWidth = 350;
                sidebar.style.width = newWidth + 'px';
                // Actualiza la posición del botón solo si el sidebar está visible
                if (!sidebar.classList.contains('collapsed')) {
                    toggle.style.left = (newWidth + 15) + 'px';
                }
            });

            document.addEventListener('mouseup', function () {
                isResizing = false;
                document.body.style.cursor = '';
            });

            // Colapsar/expandir sidebar
            toggle.addEventListener('click', function () {
                sidebar.classList.toggle('collapsed');
                toggle.classList.toggle('collapsed');
                updateTogglePosition();
            });

            // Funciones para móvil
            window.openSidebar = function () {
                if (sidebar.classList.contains('active')) {
                    closeSidebar();
                } else {
                    sidebar.classList.add('active');
                    overlay.classList.remove('hidden');
                }
            }

            window.closeSidebar = function () {
                sidebar.classList.remove('active');
                overlay.classList.add('hidden');
            }

            function updateContentMargins(isMobile, sidebar, elements) {
                // elements = [mainContent, chatContent, dateContent, answerContent]

                // Define los posibles márgenes según el contexto
                let removeClasses, addClass;

                if (isMobile) {
                    removeClasses = ['ml-20', 'ml-48'];
                    addClass = 'ml-0';
                } else if (sidebar.classList.contains('collapsed')) {
                    removeClasses = ['ml-48', 'ml-0'];
                    addClass = 'ml-20';
                } else {
                    removeClasses = ['ml-20', 'ml-0'];
                    addClass = 'ml-48';
                }

                // Aplica los cambios a todos los elementos de la lista
                elements.forEach(el => {
                    if (el) {
                        el.classList.remove(...removeClasses);
                        el.classList.add(addClass);
                    }
                });
            }

            function adjustContentMargin() {
                const dateContent = document.getElementById('date-content');
                const answerContent = document.getElementById('answer-content');

                const isMobile = window.innerWidth <= 768;

                updateContentMargins(
                    isMobile,
                    sidebar,
                    [mainContent, chatContent, dateContent, answerContent]
                );
            }

            if (toggle) {
                toggle.addEventListener('click', function () {
                    setTimeout(adjustContentMargin, 300); // Le damos tiempo a la animación del sidebar
                });
            }

            const observer = new MutationObserver((mutationsList) => {
                for (const mutation of mutationsList) {
                    if (mutation.type === 'childList') {
                        adjustContentMargin();
                    }
                }
            });

            observer.observe(document.body, {childList: true, subtree: true});

            // Evento para detectar si redimensionan la ventana (desktop <-> mobile)
            window.addEventListener('resize', adjustContentMargin);

            adjustContentMargin();

            const answerDivs = document.querySelectorAll('#answer-div');

            answerDivs.forEach(div => {
                const text = div.innerHTML;
                div.innerHTML = '';

                let i = 0;
                const speed = 20; // Velocidad en milisegundos

                function typeWriter() {
                    if (i < text.length) {
                        div.innerHTML += text.charAt(i);
                        i++;
                        setTimeout(typeWriter, speed);
                    }
                }

                typeWriter();
            });
        });

        function updateTogglePosition() {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');

            if (sidebar.classList.contains('collapsed')) {
                toggle.style.left = '10px';
            } else {
                toggle.style.left = (sidebar.offsetWidth + 15) + 'px';
            }
        }


        document.addEventListener('DOMContentLoaded', function () {
            const footer = document.querySelector('footer'); // O el selector de tu footer
            const sidebar = document.getElementById('sidebar');

            window.addEventListener('scroll', function () {
                const footerRect = footer.getBoundingClientRect();
                const sidebarRect = sidebar.getBoundingClientRect();

                // Si el footer es visible en la ventana
                if (footerRect.top < window.innerHeight) {
                    const overlap = window.innerHeight - footerRect.top;
                    sidebar.style.maxHeight = `calc(100vh - 75px - ${overlap}px)`;
                } else {
                    sidebar.style.maxHeight = 'calc(100vh - 75px)';
                }
            });
        });
        document.addEventListener('livewire:load', function () {
            Livewire.on('new-message', function () {
                const chatContainer = document.querySelector('.overflow-y-auto');
                if (chatContainer) {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            });
        });

        function typingEffectWithHtml() {
            return {
                fullHtml: '',
                displayedHtml: '',
                currentIndex: 0,

                startTyping(html) {
                    this.fullHtml = html;
                    this.typeNextCharacter();
                },

                typeNextCharacter() {
                    if (this.currentIndex < this.fullHtml.length) {
                        this.displayedHtml += this.fullHtml[this.currentIndex];
                        this.currentIndex++;
                        setTimeout(() => this.typeNextCharacter(), 20);
                    }
                }
            };
        }

        function decodeHTMLEntities(str) {
            let textarea = document.createElement('textarea');
            textarea.innerHTML = str;
            return textarea.value;
        }
    </script>
</div>

@push('styles')
    <style>
        .sidebar-chat {
            top: 75px; /* ajusta a la altura de tu navbar */
            left: 0;
            height: calc(100vh - 75px);
            width: 350px; /* ancho inicial */
            min-width: 180px;
            max-width: 350px;
            overflow-y: auto;
            background-color: #fff;
            transition: width 0.2s, transform 0.3s ease;
            border-right: 1px solid #e0e7ef;
            z-index: 999;
        }

        .sidebar-resizer {
            width: 5px;
            cursor: ew-resize;
            position: absolute;
            top: 0;
            right: 0;
            height: 100%;
            background-color: transparent;
            transition: background-color 0.2s ease;
        }

        .sidebar-resizer:hover {
            background-color: #e0e7ef;
        }

        .sidebar-toggle {
            position: fixed;
            top: 85px;
            left: 365px; /* ancho inicial + margen */
            z-index: 31;
            background: #195b81;
            color: #fff;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            cursor: pointer;
            transition: left 0.2s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }


        .sidebar-chat.collapsed {
            transform: translateX(-100%);
        }

        .sidebar-toggle.collapsed {
            left: 10px;
        }

        .overlay {
            transition: opacity 0.3s;
        }

        .bg-\[\#f3f8fd\] {
            z-index: auto;
        }

        @media (max-width: 768px) {
            .sidebar-chat {
                transform: translateX(-100%);
            }

            .sidebar-chat.active {
                transform: translateX(0);
            }

            .sidebar-toggle {
                display: none;
            }
        }

        /* estilos del contenido de las respuestas*/
        h1 {
            color: #2a5d84;
        }

        h2 {
            color: #3a7ca5;
        }

        p {
            font-size: 1.1em;
        }

        ul {
            margin-left: 20px;
        }

        .referencias {
            margin-top: 30px;
            background: #eaf1f8;
            padding: 15px;
            border-radius: 8px;
        }

        .referencias h3 {
            color: #2a5d84;
        }

        .referencias li {
            font-size: 0.98em;
        }

        /*lettering */
        .typewriter {
            overflow: hidden; /* Oculta el texto que aún no aparece */
            border-right: .15em solid #195b81; /* Efecto cursor */
            white-space: pre-wrap; /* Mantiene los saltos de línea y espacios */
            margin: 0 auto;
            letter-spacing: .15em;
            animation: typing 3.5s steps(40, end), blink-caret .75s step-end infinite;
        }

        /* Animación de tipeo */
        @keyframes typing {
            from {
                width: 0
            }
            to {
                width: 100%
            }
        }

        /* Efecto de cursor parpadeando */
        @keyframes blink-caret {
            from, to {
                border-color: transparent
            }
            50% {
                border-color: #195b81;
            }
        }
    </style>
@endpush

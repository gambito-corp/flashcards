<div class="fixed inset-0 top-[65px] md:top-[75px] left-0 right-0 bottom-0 z-1 bg-[#f3f8fd] flex">
    <div id="main-content"
         class="w-full  flex flex-col bg-[#f3f8fd] md:ml:48 ml-0 overflow-x-hidden transition-all duration-300">
        <!-- Overlay para sidebar en móvil -->
<!-- Overlay -->
@if($openSidebar)
    <div 
        class="fixed inset-0 bg-black bg-opacity-50 z-20 sm:hidden top-[65px] "
       >
    </div>
@endif

<div class="flex justify-end">
    
        <!-- Sidebar con resizer -->
        <aside id="sidebar"
               class=" fixed left-0 z-30 flex flex-col transition-transform duration-300 transform 
@if(!$openSidebar) -translate-x-full @endif 
bg-white  w-[85%] sm:w-1/5 h-[calc(100vh_-_75px)]">
            <!-- Botón "+" para nuevo chat -->
            <div class="flex items-center justify-between px-3 pt-3 pb-2">
                <span class="font-bold text-[#195b81] text-base">Chats</span>
                <button wire:click="createNewChat"
                        class="bg-[#195b81] px-[20px] py-[8px] text-[12px] rounded-[50px] gap-2 flex justify-center items-center text-white hover:bg-[#1a6ca6] transition"
                        title="Nuevo chat">
                       <span> Nuevo Chat</span>                   <i class="fa-solid fa-plus"></i>
                </button>
                <!-- Botón hamburguesa -->
                <button  wire:click="toggleSidebar" 
    onclick="event.stopPropagation()" class="absolute right-[-35px] h-[35px] w-[35px] bg-white rounded-r-full rounded-l-none shadow-lg" id="toggle-sidebar" >
                    <svg class="w-6 h-6" fill="white" stroke="#195b81" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

            </div>
            <hr class="mb-2 bg-white">
            <!-- Lista de chats -->
            <nav class="flex-1 px-3  overflow-y-auto
  [&::-webkit-scrollbar]:w-2
  [&::-webkit-scrollbar-track]:rounded-full
  [&::-webkit-scrollbar-track]:bg-[#d1d5dc]
  [&::-webkit-scrollbar-thumb]:rounded-full
  [&::-webkit-scrollbar-thumb]:bg-[#d1d5dc]
  dark:[&::-webkit-scrollbar-track]:bg-[#f3f4f6]
  dark:[&::-webkit-scrollbar-thumb]:[#d1d5dc]">
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
                                <div class="flex items-center group " >
                                    <button wire:click="selectChat({{ $chat->id }})"
                                            class="flex-1 w-full text-left flex items-center gap-2 px-2 py-2 mb-1 rounded-lg
                                            transition hover:bg-[#f3f6fb]
                                            {{ $activeChatId == $chat->id ? 'bg-[#f3f6fb] text-[#195b81] font-bold' : 'text-gray-800' }}"  x-data 
    @click.window="if (window.innerWidth < 768) { $wire.set('openSidebar', false) }">
                                        <i class="fa-regular fa-message"  ></i>
                                        <span class="truncate">{{ $chat->title ?? "Chat #{$chat->id}" }}</span>
                                    </button>
                                    <!-- Botón lápiz -->
                                <button wire:click="openEditModal({{ $chat->id }})"
                                            class="ml-2 text-[#195b81] opacity-70 hover:opacity-100 transition"
                                            title="Editar nombre">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>
                                       <!--<button wire:click="openEditModal({{ $chat->id }})"
                                            class="ml-2  transition opacity-70 hover:opacity-100 w-[30px] h-[30px] bg-[#dcfbe8] flex justify-center items-center rounded-full"
                                            title="Editar nombre">
                                     <img class="w-[17px]" src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyNC4yLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgdmlld0JveD0iMCAwIDE0IDE0LjMiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDE0IDE0LjM7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiM1OUQxODk7fQ0KPC9zdHlsZT4NCjxnPg0KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0wLDMuMWMwLjEtMC4yLDAuMS0wLjUsMC4yLTAuNmMwLjMtMC42LDAuOC0wLjksMS41LTAuOWMxLDAsMiwwLDMsMGMwLjIsMCwwLjQsMC4xLDAuNCwwLjMNCgkJYzAsMC4yLTAuMiwwLjMtMC40LDAuM2MtMSwwLTIsMC0zLDBjLTAuNSwwLTAuOSwwLjMtMS4xLDAuOGMwLDAuMSwwLDAuMiwwLDAuM2MwLDMsMCw1LjksMCw4LjljMCwwLjYsMC4zLDAuOSwwLjgsMS4xDQoJCWMwLjEsMCwwLjIsMCwwLjMsMGMzLjIsMCw2LjQsMCw5LjYsMGMwLjcsMCwxLjEtMC40LDEuMS0xLjFjMC0xLDAtMiwwLTNjMC0wLjIsMC4xLTAuMywwLjMtMC40YzAuMiwwLDAuNCwwLjEsMC40LDAuMw0KCQljMCwwLjMsMCwwLjYsMCwwLjhjMCwwLjgsMCwxLjYsMCwyLjRjMCwwLjktMC44LDEuNi0xLjYsMS42Yy0wLjEsMC0wLjEsMC0wLjIsMGMtMy4yLDAtNi40LDAtOS42LDBjLTAuNywwLTEuMi0wLjMtMS42LTAuOQ0KCQlDMC4yLDEzLDAuMSwxMi44LDAsMTIuNkMwLDkuNCwwLDYuMywwLDMuMXoiLz4NCgk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMTIsMC4yYzAuNCwwLDAuNywwLjEsMSwwLjRjMC4yLDAuMSwwLjMsMC4zLDAuNSwwLjVjMC42LDAuNiwwLjYsMS41LDAsMi4yYzAsMC0wLjEsMC4xLTAuMSwwLjFjLTIsMi00LDQtNiw2DQoJCUM3LjMsOS41LDcuMiw5LjYsNyw5LjZjLTAuOSwwLjItMS44LDAuNS0yLjcsMC44Yy0wLjEsMC0wLjIsMC4xLTAuNCwwYy0wLjEtMC4xLTAuMi0wLjItMC4yLTAuNEM0LDkuMiw0LjMsOC40LDQuNCw3LjYNCgkJYzAuMS0wLjUsMC4zLTAuOCwwLjctMS4yQzcsNC42LDguOSwyLjcsMTAuOCwwLjhDMTEuMiwwLjQsMTEuNSwwLjIsMTIsMC4yeiBNMTAuNSwyLjFjLTEuNywxLjctMy40LDMuNC01LDVDNiw3LjYsNi42LDguMiw3LjEsOC43DQoJCWMxLjctMS43LDMuNC0zLjQsNS01QzExLjYsMy4xLDExLjEsMi42LDEwLjUsMi4xeiBNMTEsMS41YzAuNiwwLjYsMS4xLDEuMSwxLjcsMS43QzEyLjgsMy4xLDEyLjksMywxMywyLjljMCwwLDAuMS0wLjEsMC4xLTAuMQ0KCQljMC4yLTAuMywwLjItMC43LDAtMWMtMC4yLTAuMi0wLjQtMC40LTAuNi0wLjZjLTAuMy0wLjItMC42LTAuMy0wLjktMC4xQzExLjQsMS4yLDExLjIsMS40LDExLDEuNXogTTUuMSw3LjhDNSw4LjQsNC44LDksNC42LDkuNg0KCQljMC42LTAuMiwxLjItMC4zLDEuOC0wLjVDNiw4LjYsNS42LDguMiw1LjEsNy44eiIvPg0KPC9nPg0KPC9zdmc+DQo="> 
                                    </button>-->
                                    <!-- Botón eliminar -->
                                    <button wire:click="openDeleteModal({{ $chat->id }})"
                                            class="ml-2 text-red-500 transition opacity-70 hover:opacity-100"
                                            title="Eliminar chat">
                                        <i class="fa-solid fa-trash" aria-hidden="true"></i>   </button>
                                           <!--<button wire:click="openDeleteModal({{ $chat->id }})"
                                            class="ml-2  transition opacity-70 hover:opacity-100 w-[30px] h-[30px] bg-[#ffe3e3] flex justify-center items-center rounded-full"
                                            title="Eliminar chat">
                                      <img class="w-[17px]" src="data:image/svg+xml;base64,PD94bWwgdmVyc2lvbj0iMS4wIiBlbmNvZGluZz0idXRmLTgiPz4NCjwhLS0gR2VuZXJhdG9yOiBBZG9iZSBJbGx1c3RyYXRvciAyNC4yLjAsIFNWRyBFeHBvcnQgUGx1Zy1JbiAuIFNWRyBWZXJzaW9uOiA2LjAwIEJ1aWxkIDApICAtLT4NCjxzdmcgdmVyc2lvbj0iMS4xIiBpZD0iQ2FwYV8xIiB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHhtbG5zOnhsaW5rPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5L3hsaW5rIiB4PSIwcHgiIHk9IjBweCINCgkgdmlld0JveD0iMCAwIDE0IDE0LjMiIHN0eWxlPSJlbmFibGUtYmFja2dyb3VuZDpuZXcgMCAwIDE0IDE0LjM7IiB4bWw6c3BhY2U9InByZXNlcnZlIj4NCjxzdHlsZSB0eXBlPSJ0ZXh0L2NzcyI+DQoJLnN0MHtmaWxsOiNFRjQ0NDQ7fQ0KPC9zdHlsZT4NCjxnPg0KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik0xMS4xLDE0LjNIM2MtMC4yLDAtMC4zLDAtMC41LTAuMWMtMC4yLTAuMS0wLjMtMC4yLTAuNC0wLjNjLTAuMS0wLjEtMC4yLTAuMy0wLjMtMC40DQoJCWMtMC4xLTAuMi0wLjEtMC4zLTAuMS0wLjVWNC4xaDAuOVYxM2MwLDAuMSwwLDAuMSwwLDAuMmMwLDAuMSwwLDAuMSwwLjEsMC4xYzAsMCwwLjEsMC4xLDAuMSwwLjFjMCwwLDAuMSwwLDAuMiwwaDguMg0KCQljMC4xLDAsMC4xLDAsMC4yLDBjMCwwLDAuMS0wLjEsMC4xLTAuMWMwLDAsMC4xLTAuMSwwLjEtMC4xYzAtMC4xLDAtMC4xLDAtMC4yVjQuMWgwLjlWMTNjMCwwLjIsMCwwLjMtMC4xLDAuNQ0KCQljLTAuMSwwLjItMC4yLDAuMy0wLjMsMC40Yy0wLjEsMC4xLTAuMywwLjItMC40LDAuM0MxMS41LDE0LjMsMTEuMywxNC4zLDExLjEsMTQuM3oiLz4NCgk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNMTIuOCwzLjFIMS4yQzEuMSwzLjEsMSwzLjEsMC45LDNDMC44LDIuOSwwLjgsMi44LDAuOCwyLjdjMC0wLjEsMC0wLjIsMC4xLTAuM3MwLjItMC4xLDAuMy0wLjFoMTEuNQ0KCQljMC4xLDAsMC4yLDAsMC4zLDAuMWMwLjEsMC4xLDAuMSwwLjIsMC4xLDAuM2MwLDAuMSwwLDAuMi0wLjEsMC4zQzEzLDMuMSwxMi45LDMuMSwxMi44LDMuMXoiLz4NCgk8cGF0aCBjbGFzcz0ic3QwIiBkPSJNOC40LDQuOWgwLjl2Ni43SDguNFY0Ljl6Ii8+DQoJPHBhdGggY2xhc3M9InN0MCIgZD0iTTQuOCw0LjloMC45djYuN0g0LjhWNC45eiIvPg0KCTxwYXRoIGNsYXNzPSJzdDAiIGQ9Ik05LjMsMS43SDguNFYwLjlINS43djAuOEg0LjhWMC45YzAtMC4yLDAuMS0wLjUsMC4yLTAuNkM1LjIsMC4xLDUuNCwwLDUuNywwaDIuOEM4LjcsMCw4LjksMC4xLDksMC4zDQoJCWMwLjIsMC4yLDAuMiwwLjQsMC4yLDAuNlYxLjd6Ii8+DQo8L2c+DQo8L3N2Zz4NCg=="> 
                                    </button>-->
                                 
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </nav>
            <!-- Barra para redimensionar -->
            <div id="sidebarResizer" class="sidebar-resizer"></div>
        </aside>

        <!-- Área principal del chat -->
       <div class="flex flex-col flex-1 h-full container-bot transition-all duration-300 w-full ">
          <!--  <div wire:loading="sendMessage" class="flex justify-start ml-40">
                <div class="flex justify-start w-4/5">
                    <div class="bg-white p-4 rounded-2xl shadow border border-[#d9e6f7] text-left">
                        <div class="text-[#195b81] animate-pulse">
                            <i class="mr-2 fa-solid fa-spinner fa-spin"></i> cargando...
                        </div>
                    </div>
                </div>
            </div>-->


            @if($messages->isEmpty())
                <div class="transition-all duration-300 ml-auto mt-8 flex flex-col items-center justify-center h-full bg-[#f3f8fd] w-full {{ $openSidebar ? 'sm:w-4/5' : 'sm:w-full' }}">
                    <!-- Logo y título -->
                    <h1 class="text-4xl md:text-5xl font-extrabold text-[#195b81]  mb-2 pt-10">DoctorMBS</h1>
                    <div class="text-lg md:text-xl text-[#195b81] font-semibold mb-1  mx-5 md:mx-0 text-center">
                        Respuestas <span class="text-[#3b82f6]">científicas</span> a preguntas médicas
                    </div>

                    <div class="w-full max-w-2xl mt-8 mb-8 ">

                    <div wire:loading wire:target="sendMessage" class="flex flex-col items-center justify-center mt-4 space-y-2 w-full" >
    <div class="animate-spin h-8 w-8 border-4 border-blue-400 border-t-transparent rounded-full text-center m-auto"></div>
    <span class="text-blue-600 font-semibold text-lg text-center flex justify-center w-full">Cargando...</span>
</div>
                        <div class="flex inset-x-0  max-w-full items-center bg-white border-2 border-[#3b82f6] rounded-2xl px-4 md:px-6  shadow-lg
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
                                        class="button-search relative ml-4 bg-[#66acff] hover:bg-[#195b81] text-white p-2 rounded-full flex items-center justify-center shadow transition md:h-[40px] h-[35px] md:w-[40px] w-[35px] md:hidden">
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
                <div class="flex flex-col  bg-[#f3f8fd] transition-all duration-300 h-[calc(100vh_-_120px)] md:h-[calc(100vh_-_75px)]
    @if($openSidebar) w-4/5 ml-[20%] @else w-full ml-0 @endif">
                    <!-- Encabezado del chat -->
                    <div id="date-content"
                         class="flex items-center justify-between p-4  bg-white shadow flex-wrap">
                        <h2 class="text-sm md:text-xl  font-bold text-[#195b81] pl-8">
                            {{ $activeChatTitle }}
                        </h2>
                    
           
                            <a
                                href="{{ route('planes') }}"
                                class="px-4 py-2 md:px-6 md:py-3 bg-gradient-to-r from-blue-500 to-purple-500 text-white font-bold rounded-full shadow-lg  hover:scale-105 transition flex items-center justify-center text-[13px] md:tex-base"
                            >
                                Actualizar a Pro
                                <svg class="ml-2 w-5 h-5" fill="none" stroke="currentColor" stroke-width="2"
                                     viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"></path>
                                </svg>
                            </a>

                    </div>

                    <!-- Área de mensajes -->
                    <div id="chat-messages" x-data
     x-ref="chatMessages"  class="h-screen flex-1 overflow-y-auto p-4 space-y-4 bg-[#f3f8fd]">
                        @foreach($messages as $message)
                            @if($message['from'] === 'user')
                                <div class="flex justify-end md:mr-10 mr-0">
                                    <div class="flex justify-end w-4/5">
                                        <div class="bg-[#195b81] text-white p-4 rounded-2xl shadow text-right">
                                            {{ $message['text'] }}
                                        </div>
                                    </div>
                                </div>
                            @elseif($message['from'] === 'bot')
                                <div id="answer-content" class="md:flex block justify-start ml-0 md:ml-20  md:mr-10 mr-0 overflow-hidden">
                                    <div class="justify-start md:flex block">
                                        <div class="bg-white p-4 rounded-2xl  border border-[#d9e6f7] text-left">
                                            @php
                                                $content = preg_replace('/<style\b[^>]*>(.*?)<\/style>/is', '', $message['text']);
                                            @endphp
                           
                                     @if(isset($message["is_new"]) && $message["is_new"] !== 'false')
<div id="answer-div"
     class="text-[#444] answer-div text-[15px]"
     x-data="typingEffectWithHtml()" 
     x-bind:data-content='@json($content)'
     x-init="startTyping(decodeHTMLEntities($el.dataset.content))">
    <span x-html="displayedHtml"></span>
</div>
@else
    <div id="answer-div"
         class="text-[#444] answer-div text-[15px]"
    >
        {!! $content !!}
    </div>
@endif

                                            @if(!empty($message['references']))
                                                <div class="flex flex-wrap gap-2 mt-2">
                                                    @foreach($message['references'] as $ref)
                                                        <span
                                                            class="bg-[#d9e6f7] text-[#195b81] px-3 py-1 rounded-full text-xs">
                                                            {{ $ref }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @elseif($message['from'] === 'articles' && !empty($message['data']))                           
                                <div class="ml-0 md:ml-20">
                                    <div class="">
                                        <div class="text-left">
                                            <div class="font-semibold text-[#195b81] mb-2">
                                                Artículos relacionados:
                                            </div>
                                            <div class="flex gap-3 flex-wrap">
                                                @foreach($message['data'] as $article)
                                                @php
                                        $url = $article['url'];
                                        $fecha = isset($article['fecha']) ? $article['fecha'] : $article['year'];
                                        $fuente = isset($article['fuente']) ? $article['fuente'] : $article['source'];
                                        $titulo = isset($article['titulo']) ? $article['titulo'] : $article['title'];
                                        $autores = isset($article['autores']) ? $article['autores'] : $article['authors'];
                                        $resumen = isset($article['resumen']) ? $article['resumen'] : $article['summary'];
                                        $tipoEstudio = isset($article['tipo_estudio']) ? $article['tipo_estudio'] : $article['journal'];
                                    @endphp
                                                    <div
                                                        class="bg-white p-3 rounded-lg border border-[#d9e6f7] hover:shadow-md transition md:w-1/5 w-full">
                                                        <a href="{{ $article['url'] ?? '#' }}" target="_blank"
                                                           class="block">
                                                            <h4 class="font-bold text-[#195b81] text-[13px] line-clamp-2 overflow-hidden">{{$titulo}}</h4>
                                                            <div class="mt-1 text-xs text-gray-500 line-clamp-2 overflow-hidden">
                                                                {{$autores}}
                                                                @if(!empty($article['journal'] ?? ''))
                                                                    ·     {{$fuente}}
                                                                @endif
                                                                @if(!empty($article['fecha'] ?? ''))
                                                                    ·     {{$fecha}}
                                                                @endif
                                                            </div>
                                                            @if(!empty($article['tipo_estudio']))
                                                                <span
                                                                    class="inline-block bg-[#d9e6f7] text-[#195b81] px-2 py-0.5 rounded-full text-xs mt-2">
                                                                       {{$tipoEstudio}}
                                                                </span>
                                                            @endif
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            @endif
                        @endforeach
                    </div>
                    <!-- Input de seguimiento -->
                    <div class="p-4 bg-white border-t border-[#d9e6f7]">
                        <div id="chat-content"
                            class="flex flex-wrap items-center px-4 py-4 md:px-6 shadow py-4bg-white rounded-2xl">
                            <input type="text"
                                wire:model.live="newMessage"
                                class="flex-1 text-[15px] md:text-lg bg-transparent border-0 focus:ring-0 focus:outline-none text-[#195b81]"
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
<!-- Asegúrate de que Alpine.js esté cargado en tu plantilla -->
<div x-data="{ overlay: false }">
    <!-- Botón -->
    <button wire:click="sendMessage"
            class="order-0 relative ml-4 bg-[#66acff] hover:bg-[#195b81] text-white p-2 rounded-full flex items-center justify-center shadow transition md:h-[40px] h-[35px] md:w-[40px] w-[35px] md:order-none order-0"
            @click="overlay = true; setTimeout(() => overlay = false, 6000)">
        <i class="fa-solid fa-arrow-right"></i>
    </button>

    <!-- Overlay con texto "Cargando..." -->
    <div
      x-show="overlay"
      x-transition.opacity
      class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-50 z-50"
    >
      <div class="bg-white rounded-lg px-6 py-4 shadow-lg">
        <p class="text-lg font-semibold">Cargando...</p>
      </div>
    </div>
</div>

                        </div>
                        
                    </div>
                </div>
            @endif
        </div>
    </div>
<div>

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
                @if(count($fontOptions) === 0)

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
       
            <div class="absolute inset-0 bg-white/80 z-50 flex flex-col items-center justify-center rounded-2xl">
                  <button wire:click="closeFilters" class="text-2xl  text-white rounded-[50px] right-5 top-5 absolute bg-[#195b81] w-10 h-10">&times;
                    </button>
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
   <script>
    function typingEffectWithHtml() {
        return {
            displayedHtml: '',
            content: '',
            startTyping(content) {
                this.content = content;
                let index = 0;
                const interval = setInterval(() => {
                    if (index < this.content.length) {
                        this.displayedHtml += this.content[index];
                        index++;
                    } else {
                        clearInterval(interval);
                    }
                }, 10);
            }
        };
    }

    function decodeHTMLEntities(text) {
        var textarea = document.createElement('textarea');
        textarea.innerHTML = text;
        return textarea.value;
    }


    document.addEventListener('livewire:update', () => {
        const chatMessages = document.querySelector('#chat-messages');
        if (chatMessages) {
            chatMessages.scrollTop = chatMessages.scrollHeight;
        }
    });
function typingEffectWithHtml() {
    return {
        fullContent: '',
        displayedHtml: '',
        index: 0,
        interval: null,
        startTyping(content) {
            this.fullContent = content;
            this.displayedHtml = '';
            this.index = 0;

            this.interval = setInterval(() => {
                if (this.index < this.fullContent.length) {
                    this.displayedHtml += this.fullContent[this.index];
                    this.index++;

                    // Desplazar hacia abajo en cada actualización
                    this.$nextTick(() => {
                        const chatMessages = document.querySelector('#chat-messages');
                        if (chatMessages) {
                            chatMessages.scrollTop = chatMessages.scrollHeight;
                        }
                    });
                } else {
                    clearInterval(this.interval);
                }
            }, 10); // Velocidad de escritura (ajusta si es necesario)
        }
    };
}

function decodeHTMLEntities(text) {
    let txt = document.createElement("textarea");
    txt.innerHTML = text;
    return txt.value;
}
</script>
</div>

@push('styles')
    <style>
        #answer-div table{
    
    padding:20px 0px;
    display: block;
}
#answer-div table{
    
   overflow: auto;

}
#answer-div table tr{
    
    text-align: left;

}
#answer-div table  td{
    
border:1px solid #d7d7d7;
padding:8px;

}
 #answer-div  a{
    text-decoration: underline;
    color: #195b81;
}
 #answer-div  ul{
    list-style:inside;
    padding:8px 0px;
 } 
  #answer-div  h1 {
    font-size: 18px;
    font-weight: 900;
    padding-bottom: 15px;
}
 #answer-div  h2{
    font-weight: 700;
    padding-top: 15px;
    padding-bottom: 10px;
}
@media (max-width:620px){
   #answer-div  table{
    font-size:14px;
   }
}
    </style>
@endpush

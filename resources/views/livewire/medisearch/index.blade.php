<div class="fixed inset-0 top-[80px] left-0 right-0 bottom-0 z-10 bg-[#f3f8fd] flex">
    <div class="w-10/12 h-[calc(100vh-75px)] flex ml-40 bg-[#f3f8fd]">
        <!-- Overlay para sidebar en móvil -->
        <div id="sidebarOverlay" class="fixed inset-0 z-20 hidden bg-black overlay bg-opacity-30" onclick="closeSidebar()"></div>


        <!-- Sidebar con resizer -->
        <div class="flex-shrink-0 basis-1/8 min-w-[180px] max-w-[320px] h-full bg-white border-r border-[#e0e7ef] flex flex-col">
            <aside id="sidebar" class="fixed left-0 z-30 flex flex-col transition-all duration-300 bg-white shadow-lg sidebar-chat">
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
                            <div class="transition-all duration-200" style="{{ $chatGroupsOpen[$group] ? '' : 'display:none;' }}">
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
        </div>

        <!-- Botón para abrir sidebar en móvil -->
        <button class="history-chat-button fixed left-4 bottom-4 bg-white z-40 px-5 py-2 rounded-full text-[14px] shadow-md md:hidden flex items-center gap-2"
                onclick="openSidebar()">
            <i class="fa-regular fa-message"></i> Historial
        </button>

        <!-- Área principal del chat -->
        <div class="flex flex-col flex-1 h-full">
            <div class="bg-black">
                <p class="text-white" wire:loading='sendMessage'>HOLA MUNDO</p>
            </div>
            @if($messages->isEmpty())
                <!-- Estado 1: Pantalla de bienvenida -->
                <div class="flex flex-col items-center justify-center h-full w-full bg-[#f3f8fd]">
                    <!-- Logo y título -->
                    <h1 class="text-4xl md:text-5xl font-extrabold text-[#195b81] mb-2">DoctorMBS</h1>
                    <div class="text-lg md:text-xl text-[#195b81] font-semibold mb-1">Respuestas <span class="text-[#3b82f6]">científicas</span> a preguntas médicas</div>

                    <div class="w-full max-w-2xl mt-8 mb-4">
                        <div class="flex items-center bg-white border-2 border-[#3b82f6] rounded-2xl px-6 py-4 shadow-lg">
                            <input type="text"
                                   wire:model.defer="newMessage"
                                   class="flex-1 text-lg bg-transparent border-0 focus:ring-0 focus:outline-none text-[#195b81] placeholder-[#b0b8c1]"
                                   placeholder="Haz una pregunta de salud o biociencia...">
                            <button wire:click='openFilters' class="ml-3 text-[#195b81] hover:text-[#1a6ca6] flex items-center gap-1 font-semibold">
                                <i class="fa-solid fa-sliders"></i>
                                <span class="ml-1">Filtros</span>
                            </button>
                            <label class="flex items-center ml-4 cursor-pointer select-none">
                                <span class="text-sm text-[#b0b8c1] font-bold mr-1">Investigacion Profunda </span>
                                <input wire:model.live='deepResearch' type="checkbox" class="accent-[#195b81] scale-125">
                            </label>
                            <button wire:click='sendMessage' class="ml-4 bg-[#3b82f6] hover:bg-[#195b81] text-white p-2 rounded-full flex items-center justify-center shadow transition">
                                <i class="text-lg fa-solid fa-magnifying-glass"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Sugerencias -->
                    <div class="w-full max-w-2xl mt-6">
                        <div class="flex flex-col gap-3">
                            <div class="flex items-center gap-2 mb-1 text-sm font-semibold text-gray-600">
                                <i class="fa-regular fa-lightbulb"></i>
                                Comienza con preguntas comunes
                            </div>
                            <div class="flex flex-wrap gap-2 mb-2">
                                <button wire:click="setQuestion('¿El deporte aumenta la esperanza de vida?')"
                                        class="bg-white border border-[#b0b8c1] text-[#195b81] rounded-full px-4 py-2 text-sm shadow hover:bg-[#f3f6fb] transition">
                                    ¿El deporte aumenta la esperanza de vida?
                                </button>
                                <button wire:click="setQuestion('¿Cuáles son las probabilidades de contraer cáncer?')"
                                        class="bg-white border border-[#b0b8c1] text-[#195b81] rounded-full px-4 py-2 text-sm shadow hover:bg-[#f3f6fb] transition">
                                    ¿Cuáles son las probabilidades de contraer cáncer?
                                </button>
                            </div>
                            <div class="flex items-center gap-2 mb-1 text-sm font-semibold text-gray-600">
                                <i class="fa-solid fa-flask"></i>
                                Profundiza en preguntas complejas
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <button wire:click="setQuestion('¿La vacuna contra el COVID empeora la artritis?')"
                                        class="bg-white border border-[#b0b8c1] text-[#195b81] rounded-full px-4 py-2 text-sm shadow hover:bg-[#f3f6fb] transition">
                                    ¿La vacuna contra el COVID empeora la artritis?
                                </button>
                                <button wire:click="setQuestion('¿El control de la natalidad hormonal puede afectar la demografía?')"
                                        class="bg-white border border-[#b0b8c1] text-[#195b81] rounded-full px-4 py-2 text-sm shadow hover:bg-[#f3f6fb] transition">
                                    ¿El control de la natalidad hormonal puede afectar la demografía?
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Enlace informativo -->
                    <div class="mt-10">
                        <a href="#" class="text-[#3b82f6] text-sm underline hover:text-[#195b81] transition">¿Qué es MediSearch?</a>
                    </div>
                </div>

            @else
                <!-- Estado 2: Chat activo -->
                <div class="flex flex-col h-full bg-[#f3f8fd]">
                    <!-- Encabezado del chat -->
                    <div class="flex items-center justify-between p-4 bg-white shadow">
                        <h2 class="text-xl ml-12 font-bold text-[#195b81]">
                            {{ $activeChatTitle }}
                        </h2>
                        <button class="bg-gradient-to-r from-[#6a5af9] to-[#38bdf8] text-white px-6 py-2 rounded-full font-bold shadow hover:scale-105 transition">
                            <i class="mr-1 fa-solid fa-star"></i> Actualizar a Pro
                        </button>
                    </div>

                    <!-- Área de mensajes -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-4 bg-[#f3f8fd]">
                        @foreach($messages as $message)
                            @if($message['from'] === 'user')
                                <div class="flex justify-end mr-10">
                                    <div class="flex justify-end w-4/5">
                                        <div class="bg-[#195b81] text-white p-4 rounded-2xl shadow text-right">
                                            {{ $message['text'] }}
                                        </div>
                                    </div>
                                </div>
                            @elseif($message['from'] === 'bot')
                                <div class="flex justify-start ml-10">
                                    <div class="flex justify-start w-4/5">
                                        <div class="bg-white p-4 rounded-2xl shadow border border-[#d9e6f7] text-left">
                                            <div class="text-[#195b81]">
                                                {!! $message['text'] !!}
                                            </div>
                                            @if(!empty($message['references']))
                                                <div class="flex flex-wrap gap-2 mt-2">
                                                    @foreach($message['references'] as $ref)
                                                        <span class="bg-[#d9e6f7] text-[#195b81] px-3 py-1 rounded-full text-xs">
                                                            {{ $ref }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @elseif($message['from'] === 'articles' && !empty($message['data']))
                                <div class="flex justify-center w-full">
                                    <div class="w-4/5 max-w-2xl">
                                        <div class="bg-[#eaf4fb] p-4 rounded-2xl shadow border border-[#d9e6f7] text-left">
                                            <div class="font-semibold text-[#195b81] mb-2">
                                                Artículos relacionados:
                                            </div>
                                            <div class="grid gap-3">
                                                @foreach($message['data'] as $article)
                                                    <div class="bg-white p-3 rounded-lg border border-[#d9e6f7] hover:shadow-md transition">
                                                        <a href="{{ $article['url'] ?? '#' }}" target="_blank" class="block">
                                                            <h4 class="font-bold text-[#195b81] text-sm">Titulo de Prueba</h4>
                                                            <div class="mt-1 text-xs text-gray-500">
                                                                Autor de Prueba
                                                                @if(!empty($article['journal'] ?? ''))
                                                                    · journal de Prueba
                                                                @endif
                                                                @if(!empty($article['fecha'] ?? ''))
                                                                    · fecha de Prueba
                                                                @endif
                                                            </div>
                                                            @if(!empty($article['tipo_estudio']))
                                                                <span class="inline-block bg-[#d9e6f7] text-[#195b81] px-2 py-0.5 rounded-full text-xs mt-2">
                                                                    estudio de Prueba
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
                        <div wire:loading="sendMessage" class="flex justify-start ml-10">
                            <div class="flex justify-start w-4/5">
                                <div class="bg-white p-4 rounded-2xl shadow border border-[#d9e6f7] text-left">
                                    <div class="text-[#195b81] animate-pulse">
                                        <i class="mr-2 fa-solid fa-spinner fa-spin"></i> cargando...
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Input de seguimiento -->
                    <div class="p-4 bg-white border-t border-[#d9e6f7]">
                        <div class="flex items-center px-6 py-4 bg-white shadow rounded-2xl">
                            <input type="text"
                                   wire:model.live="newMessage"
                                   class="flex-1 text-lg bg-transparent border-0 focus:ring-0 focus:outline-none text-[#195b81]"
                                   placeholder="Haz una pregunta de seguimiento...">
                                <button wire:click='openFilters' class="ml-3 text-[#195b81] hover:text-[#1a6ca6] flex items-center gap-1 font-semibold">
                                    <i class="fa-solid fa-sliders"></i>
                                    <span class="ml-1">Filtros</span>
                                </button>
                                <label class="flex items-center ml-4 cursor-pointer select-none">
                                    <span class="text-sm text-[#b0b8c1] font-bold mr-1">Investigacion Profunda </span>
                                    <input wire:model='deepResearch' type="checkbox" class="accent-[#195b81] scale-125">
                                </label>
                            <button wire:click="sendMessage"
                                    class="ml-3 bg-[#195b81] text-white px-4 py-2 rounded-full font-bold shadow hover:bg-[#1a6ca6] transition">
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
                                class="px-4 py-2 text-gray-700 bg-gray-200 rounded-md hover:bg-gray-300">Cancelar</button>
                        <button type="submit"
                                class="px-4 py-2 rounded-md bg-[#195b81] text-white hover:bg-[#1a6ca6]">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <script>
// ===================Lettering===================
document.addEventListener("DOMContentLoaded", function () {
        let observer = new MutationObserver(() => {
            const botMessages = document.querySelectorAll(".bot-text");
            if (botMessages.length === 0) return;
            const lastMessage = botMessages[botMessages.length - 1];
            if (!lastMessage.classList.contains("animated")) {
                // Seleccionar la última sección antes de la animación y agregar "card-ati"
                const articleSections = document.querySelectorAll(".article-section");
                const lastSection = articleSections[articleSections.length - 1];
                if (lastSection) {
                    lastSection.classList.add("card-ati");
                }
                // Animar el texto y luego cambiar las clases
                animateText(lastMessage, () => {
                    // Remover "active-card" de todas las secciones
                    articleSections.forEach(section => section.classList.remove("active-card"));
                    if (lastSection) {
                        // Eliminar "card-ati" y agregar "active-card" después de la animación
                        lastSection.classList.remove("card-ati");
                        lastSection.classList.add("active-card");
                    }
                });
                lastMessage.classList.add("animated");
            }
        });
        observer.observe(document.getElementById("chat-messages"), { childList: true, subtree: true });
        function animateText(element, callback) {
            const message = element.textContent;
            element.textContent = "";
            let index = 0;
            function typeWriter() {
                if (index < message.length) {
                    element.textContent += message.charAt(index);
                    index++;
                    setTimeout(typeWriter, 10);
                } else if (callback) {
                    callback(); // Ejecutar el callback después de la animación
                }
            }
            typeWriter();
        }
    });
    // ======================== Fin del Lettering ?==============================






        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.getElementById('sidebar');
            const resizer = document.getElementById('sidebarResizer');
            const toggle = document.getElementById('sidebarToggle');
            const overlay = document.getElementById('sidebarOverlay');
            let isResizing = false;

            // Función para redimensionar
            resizer.addEventListener('mousedown', function(e) {
                isResizing = true;
                document.body.style.cursor = 'ew-resize';
            });

            document.addEventListener('mousemove', function(e) {
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

            document.addEventListener('mouseup', function() {
                isResizing = false;
                document.body.style.cursor = '';
            });

            // Colapsar/expandir sidebar
            toggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                toggle.classList.toggle('collapsed');
            });

            // Funciones para móvil
            window.openSidebar = function() {
                sidebar.classList.add('active');
                overlay.classList.remove('hidden');
            }

            window.closeSidebar = function() {
                sidebar.classList.remove('active');
                overlay.classList.add('hidden');
            }
        });

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');

            sidebar.classList.toggle('collapsed');

            if (sidebar.classList.contains('collapsed')) {
                toggle.style.left = '10px'; // Posición cuando está colapsado
            } else {
                // Recupera la posición normal basada en el ancho del sidebar
                toggle.style.left = (sidebar.offsetWidth + 15) + 'px';
            }
        }


        document.addEventListener('DOMContentLoaded', function() {
            const footer = document.querySelector('footer'); // O el selector de tu footer
            const sidebar = document.getElementById('sidebar');

            window.addEventListener('scroll', function() {
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
        document.addEventListener('livewire:load', function() {
            Livewire.on('new-message', function() {
                const chatContainer = document.querySelector('.overflow-y-auto');
                if (chatContainer) {
                    chatContainer.scrollTop = chatContainer.scrollHeight;
                }
            });
        });
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
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
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
    </style>
@endpush

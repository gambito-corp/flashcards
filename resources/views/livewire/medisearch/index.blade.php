<div class="max-w-[67rem] mx-auto p-4 pt-[40px] md:pt-[70px] h-full main-chat-bot-mbs">
<div class="overlay"></div>
    <!-- Sección de Historial de Chats -->
    <div class="sidebar-chat bg-white z-0   p-9 m-0 fixed    rounded-[0px] md:rounded-[20px] z-10 left-5 transform md:-translate-x-[0%] -translate-x-[109%]">

        <h3 class="text-lg font-bold mb-4 text-[#195b81]">Historial</h3>
        <hr>
        <div class="  flex flex-col overflow-hidden gap-[12px] text-center mt-2 w-full">
            @foreach($chatHistory as $chat)
                <button wire:click="selectChat({{ $chat->id }})"
                        class="border-0 w-full text-gray-800 flex mb-1 items-center  text-sm font-medium  py-1  font-semibold chat-history md:hover:bg-[#f3f6fb] md:rounded-[20px] md:p-[10px_15px]
                        {{ $activeChatId == $chat->id ?  : '' }} "><i class="fa-regular fa-message mr-3"></i>
                    Chat #{{ $chat->id }} <br>
                    <span class="text-xs ml-3">{{ $chat->created_at->format('d/m/Y') }}</span>
                </button>
            @endforeach
        </div>
    </div>
    <!-- Boton historial movil -->
    <div class="history-chat-button fixed left-[20px] bottom-[15px] bg-white z-[999] px-[25px] py-[10px] rounded-full text-[14px] shadow-md md:hidden block"><i class="fa-regular fa-message mr-1"></i>Historial</div>
    <!-- Área del Chat -->
    <div class=" flex flex-col  mt-4  rounded-2xl content-chat__mbs">
        <!-- Chat Header -->
        <div class="p-4 md-4  md:mt-[0px]">
        <h2 class="text-[24px] md:text-[30px] font-extrabold text-[#195b81]" >¿En qué puedo ayudarte?</h2>
        <span class="md:text-[17px] text-[15px] text-[#333333] font-medium">Escribe para poder ayudarte</span>
        </div>
        <hr>

        <!-- Área de Mensajes -->
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
                        <div class="md:w-[15rem] w-full md-1 md:mb-4 bg-white p-3 h-[85px] rounded-[15px] article-card {{ $index > 3 ? 'hidden' : '' }}">
                            <a href="{{ $article['url'] }}" class="text-sm" target="_blank">
                                <h3 class="font-bold text-[13px] leading-[18px] text-[#195b81] line-clamp-2">
                                    {{ $article['title'] }}
                                </h3>
                                <p class="text-xs text-gray-500 line-clamp-1">
                                    {{ $article['journal'] }} - {{ $article['year'] }}
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
                        <span  class="bot-text text-[15px] md:text-[16px] text-[#333333] leading-[32px] mb-5 font-medium">"{{ $message['text'] }}"</span>
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
        <!-- Formulario de Envío -->
        <div class="p-4">
            <form wire:submit.prevent="sendMessage" class="flex flex-col gap-2 relative">
                @if(($queryCount <= 99))
                    <div class="flex gap-3 mb-3 items-center">
                        <div>
                            <label class="block text-xs text-[#195b81] font-semibold mb-1">Modelo IA</label>
                            <select wire:model.live="modeloIA" class="border rounded-md px-2 py-1 text-sm">
                                @foreach($modelosIA as $key => $modelo)
                                    <option value="{{ $key }}" @if($modeloIA == $key) selected @endif>{{ $modelo }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($this->modeloIA != 'medisearch')
                            <div class="flex items-center gap-2">
                                <input type="checkbox" id="investigacionProfunda" wire:model.live="investigacionProfunda" class="accent-[#195b81]" @if ($this->modeloIA == 'medisearch') disabled @endif>
                                <label for="investigacionProfunda" class="text-xs text-[#195b81] font-semibold cursor-pointer">
                                    Investigación profunda
                                </label>
                            </div>
                            <span>
                                Modelo de IA Experimental los Resultados o la Funcionabilidad podria no ser 100% precisa Todavia en Entrenamiento.
                            </span>

                        @endif
                    </div>

                    <div class="flex relative">
                        <input
                            type="text"
                            wire:model.defer="newMessage"
                            placeholder="Escribe tu mensaje..."
                            class="flex-1 h-[70px] md:h-[90px] border-1 border-[#195b81] rounded-[20px] indent-5 focus:border-[#195b81] focus:ring-[#195b81] "
                        />
                        <button wire:loading.attr="disabled" type="submit" class="bg-[#195b81] w-[30px] md:w-[40px] flex-shrink-0 flex absolute h-[30px] md:h-[40px] justify-center items-center rounded-full right-[15px] bottom-[15px]">
                            <i class="fa-solid fa-arrow-right text-white"></i>
                        </button>
                    </div>
               @endif
            </form>
        @if(!Auth::user()->hasRole('root'))
                <div class="p-2 text-sm text-gray-600 border-b border-gray-200">
                    Has realizado {{ $queryCount }} preguntas este mes. Te quedan {{ 100 - $queryCount }}.
                    @if($queryCount >= 100)
                        Puedes volver a preguntar en
                        {{ \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::now()->endOfMonth()) }} días.
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

@push('styles')
    <style>
        @media (min-width: 768px) {
            .main-chat-bot {
                width: 100%;
                max-width: 100%;
                display: flex;
                justify-content: end;
                padding-right: 20vw;
            }
            .main-chat-bot-mbs {
                max-width: 100%;
            }
            .box-chat {
                width: calc(100% - 410px);
            }
        }
        @media (max-width: 1640px) and (min-width: 769px) {
            .main-chat-bot {
                padding-right: 9vw;
            }
            .main-chat-bot-mbs {
                max-width: 100%;
            }
            .box-chat {
                width: calc(100% - 300px);
            }
        }
        .header-mbs{
            position: fixed;
            width: 100%;
            top: 0;
        }
        .footer-mbs{
            display: none;
        }
        .activo-sidebar {
            transform: translateX(0%);
            transition: .3s all ease-in-out;
            z-index: 999999;
            height: 100%;
            top: 0;
            left: 0;
        }
        .overlay{
            position: fixed;
            background-color: rgba(0, 0, 0, 0.7);
            opacity: 0;
            transition: opacity 0.5s cubic-bezier(0.19, 1, 0.22, 1), z-index 0s cubic-bezier(0.19, 1, 0.22, 1) 0.5s, top 0s cubic-bezier(0.19, 1, 0.22, 1) 0.5s;
            width: 100%;
            height: 100%;
            top: 0;
            visibility: hidden;
            left: 0;
            z-index: 1;
        }
        .active-overlay.overlay{
            visibility: visible;
            opacity: 1;
        }
        hr{
            margin-bottom: 0px !important;
        }
        .article-card {
            opacity: 1;
            transition: opacity 0.5s ease-in-out;
        }
        .article-card.hidden {
            opacity: 0;
            display: none;
        }
        @keyframes spin360 {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
        .animate-spin-360 {
            animation: spin360 2s linear infinite;
        }
        /* Animación para los puntos suspensivos */
        .dots::after {
            content: '.';
            animation: dotsAnimation 1.5s infinite steps(1) 1s;
        }
        .dots::before {
            content: '';
            animation: dotsAnimation 1.5s infinite steps(1) 0.5s;
        }
        @keyframes dotsAnimation {
            0% {
                content: '.';
            }
            33% {
                content: '..';
            }
            66% {
                content: '...';
            }
            100% {
                content: '.';
            }
        }
        /*Sidebar historial */
        @media (min-width:620px){
            .chat-history{
                width: 200px;
            }
        }
        .sidebar-chat {
            overflow-y: auto;
        }
        @media (min-width:992px){
            .sidebar-chat {
                min-width: fit-content;
                max-height: 620px;
            }
            /* Barra de desplazamiento */
            .sidebar-chat::-webkit-scrollbar{
                width: 8px;
                background-color: #dedede;
                border-radius: 20px;
            }
            .sidebar-chat::-webkit-scrollbar:window-inactive {
                display: none;
            }
            .sidebar-chat::-webkit-scrollbar-thumb  {
                background-color: #5b8080;
                border-radius: 6px;
            }
        }
        .card-ati{
            opacity: 0;
            visibility: hidden;
            transition: .3s all ease-in;
        }
        .active-card{
            opacity: 1;
            visibility: visible;
            transition: .3s all ease-in;
        }
    </style>
@endpush

<script>
    // Animar texto bot
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
    //Mostrar el boton ver mas articulos
    document.addEventListener("DOMContentLoaded", function () {
        function actualizarBotones() {
            document.querySelectorAll(".article-section").forEach(section => {
                const articles = section.querySelectorAll(".article-card.hidden");
                const verMasButton = section.querySelector(".verMas");
                if (!verMasButton) return;
                if (articles.length === 0) {
                    verMasButton.classList.add("hidden");
                } else {
                    verMasButton.classList.remove("hidden");
                }
                verMasButton.onclick = function () {
                    articles.forEach(article => article.classList.remove("hidden"));
                    this.style.display = "none";
                };
            });
        }
        // Llamamos a la función al cargar la página
        actualizarBotones();
        // Observamos cambios en el DOM
        const observer = new MutationObserver(actualizarBotones);
        observer.observe(document.body, { childList: true, subtree: true });
    });
    // Agregar fondo a toda la pagina
    document.querySelectorAll('.body-content').forEach(element => {
        element.style.background = '#f3f6fb';
    });
    //Activar boton historial
    document.querySelectorAll(".history-chat-button").forEach(button => {
        button.addEventListener("click", () => {
            document.querySelector(".sidebar-chat").classList.add("activo-sidebar");
            document.querySelector(".overlay").classList.add("active-overlay");
        });
    });
    document.querySelectorAll(".overlay, .chat-history").forEach(element => {
        element.addEventListener("click", () => {
            const sidebar = document.querySelector(".sidebar-chat");
            const overlay = document.querySelector(".overlay");
            // Agregar transición antes de quitar las clases
            sidebar.style.transition = "transform 0.5s ease-in-out, opacity 0.5s ease-in-out";
            overlay.style.transition = "opacity 0.5s ease-in-out";
            // Aplicar animación antes de quitar las clases
            sidebar.style.transform = "translateX(-100%)";
            sidebar.style.opacity = "0";
            overlay.style.opacity = "0";
            // Esperar la animación antes de quitar las clases
            setTimeout(() => {
                sidebar.classList.remove("activo-sidebar");
                overlay.classList.remove("active-overlay");
                // Resetear estilos inline para que pueda seguir funcionando con Tailwind u otros estilos
                sidebar.style.transition = "";
                sidebar.style.transform = "";
                sidebar.style.opacity = "";
                overlay.style.transition = "";
                overlay.style.opacity = "";
            }, 500); // Debe coincidir con la duración de la transición
        });
    });
</script>

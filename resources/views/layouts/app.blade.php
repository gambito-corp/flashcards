<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="body-content">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg==" crossorigin="anonymous" referrerpolicy="no-referrer" />


    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/ts/app.ts'])
    <link rel="stylesheet" href="{{ asset('estilos.css') }}">

    <!-- Styles -->
    <livewire:styles />
    @stack('styles')
    <style>
        .environment-indicator {
            position: fixed;
            top: 50px;
            left: 50%;
            transform: translateX(-50%);
            z-index: 9999;
            background: rgba(255, 0, 0, 0.2);
            color: #ff0000;
            font-weight: 800;
            padding: 8px 20px;
            border-radius: 5px;
            border: 2px solid #ff0000;
            text-transform: uppercase;
            font-size: 14px;
            letter-spacing: 1px;
            backdrop-filter: blur(2px);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
    </style>
    @if(config('app.env') == 'prod')
        <script>
            // Función para deshabilitar atajos de teclado específicos
            function disableKeyShortcuts() {
                document.addEventListener('keydown', (e) => {
                    // Lista de keycodes que se deshabilitan: F12 (123), Windows/Meta izquierda (91) y derecha (92)
                    // Además, se deshabilitan Ctrl+Shift+I (73) y Ctrl+Shift+J (74), y Ctrl+U (85)
                    if (
                        [123, 91, 92].includes(e.keyCode) ||
                        (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74)) ||
                        (e.ctrlKey && e.keyCode === 85)
                    ) {
                        e.preventDefault();
                    }
                });
            }

            // Función para deshabilitar el menú contextual (click derecho)
            function disableContextMenu() {
                document.addEventListener('contextmenu', (e) => e.preventDefault());
            }

            // Función para copiar un mensaje al portapapeles
            function copyMessageToClipboard(message) {
                const inputOculto = document.createElement('input');
                inputOculto.value = message;
                document.body.appendChild(inputOculto);
                inputOculto.select();
                document.execCommand("copy");
                document.body.removeChild(inputOculto);
            }

            // Función para mostrar un overlay en pantalla con un mensaje
            function showOverlay(message, duration = 3000) {
                const overlay = document.createElement('div');
                overlay.id = "screenshotOverlay";
                Object.assign(overlay.style, {
                    position: 'fixed',
                    top: '0',
                    left: '0',
                    width: '100%',
                    height: '100%',
                    backgroundColor: 'black',
                    opacity: '1',
                    zIndex: '9999',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center'
                });
                overlay.innerHTML = `<p style="color: white; font-size: 24px;">${message}</p>`;
                document.body.appendChild(overlay);

                // Permitir quitar el overlay haciendo clic en él
                overlay.addEventListener('click', () => overlay.remove());

                // Remover el overlay automáticamente después del tiempo especificado
                setTimeout(() => {
                    const el = document.getElementById('screenshotOverlay');
                    if (el) el.remove();
                }, duration);
            }

            // Función para gestionar eventos de tecla al soltar (keyup)
            function handleKeyUpEvent(e) {
                // Detecta Print Screen (44) o Ctrl (17)
                // También se verifica si se presionan combinaciones con Ctrl y teclas específicas
                if (
                    e.keyCode === 44 ||
                    e.keyCode === 17 ||
                    (e.ctrlKey && (e.keyCode === 123 || e.keyCode === 91 || e.keyCode === 92))
                ) {
                    const mensajePortapapeles = "Você não pode mais dar printscreen. Isto faz parte da nova medida de segurança do sistema.";
                    const mensajeOverlay = "Print screen y teclas Windows desabilitadas. Haz click en la pantalla para desactivar este aviso.";

                    // Copiar mensaje al portapapeles
                    copyMessageToClipboard(mensajePortapapeles);
                    // Mostrar overlay en pantalla
                    showOverlay(mensajeOverlay, 3000);
                }
            }

            // Función para agregar el listener de keyup
            function addKeyUpListener() {
                window.addEventListener("keyup", handleKeyUpEvent);
            }

            // Función para manejar el enfoque y desenfoque de la ventana
            function addFocusBlurListeners() {
                window.addEventListener("focus", () => document.body.style.display = "block");
                window.addEventListener("blur", () => document.body.style.display = "none");
            }

            // Inicialización del script una vez que el DOM esté cargado
            document.addEventListener("DOMContentLoaded", () => {
                disableKeyShortcuts();
                disableContextMenu();
                addKeyUpListener();
                addFocusBlurListeners();
            });
        </script>
    @endif
</head>
<body class="font-sans antialiased">
<!-- Texto flotante para entorno PRE -->
@if(app()->environment('pre'))
    <div class="environment-indicator">
        ENTORNO PRE - AMBIENTE DE PRUEBAS
    </div>
@endif
<x-banner />
<div class="min-h-screen bg-[#f7f7f7] body-content">
    <livewire:nav-link/>

    <!-- Page Heading -->
    @if ($title)
        <header class="bg-white ml-48 mt-4 shadow inline-block">
            <div class="p-4 sm:px-6 lg:px-8 inline-flex items-center">
                @if ($icon)
                    <i class="fa-solid fa-{{ $icon }} mr-2"></i>
                @else
                    <i class="fa-regular fa-circle-question mr-2"></i>
                @endif
                <span class="inline-block">{{ $title }}</span>
            </div>
        </header>
    @endif

    <!-- Page Content -->
    <!-- Modal -->
    <div id="lightboxModal" class="lightbox-modal" onclick="closeModal()">
        <span class="close-btn">&times;</span>
        <img class="modal-content" id="modalImage" alt="as">
    </div>

    <main class="">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-footer text-white  z-10 footer-mbs flex flex-col  md:flex-row justify-between px-[20px] md:px-[50px] py-[35px] ">
        <div class=" text-sm text-center mb-3 md:mb-0">
            © {{ date('Y') }} {{ config('app.name', 'Laravel') }}. Todos los derechos reservados.
        </div>
        <div class="text-sm flex flex-col  md:flex-row gap-4 underline text-center"><a href="https://medbystudents.com/libro-de-reclamaciones/" target="_blank">Libro de reclamaciones</a><a href="https://medbystudents.com/politicas-de-privacidad/" target="_blank">Políticas de privacidad</a></div>
    </footer>
</div>
@stack('modals')
<livewire:scripts />
@stack('scripts')
</body>
</html>

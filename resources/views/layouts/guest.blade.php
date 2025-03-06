<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <link rel="stylesheet" href="{{ asset('estilos.css') }}">
        <!-- Styles -->
        @livewireStyles
        @stack('styles')
        @if(config('app.env') == 'prod')
            <script>
                // Deshabilitar ciertas teclas
                document.addEventListener('keydown', e => {
                    if (
                        [123].includes(e.keyCode) || // F12
                        (e.ctrlKey && e.shiftKey && (e.keyCode === 73 || e.keyCode === 74)) || // Ctrl+Shift+I/J
                        (e.ctrlKey && e.keyCode === 85) // Ctrl+U
                    ) {
                        e.preventDefault();
                    }
                });

                // Deshabilitar el menú contextual (click derecho)
                document.addEventListener('contextmenu', e => e.preventDefault());

                // Función para copiar mensaje al portapapeles y mostrar un overlay
                const copyToClipboard = () => {
                    // Crear input oculto y copiar mensaje
                    const aux = document.createElement('input');
                    aux.value = "Você não pode mais dar printscreen. Isto faz parte da nova medida de segurança do sistema.";
                    document.body.appendChild(aux);
                    aux.select();
                    document.execCommand("copy");
                    document.body.removeChild(aux);

                    // Crear overlay que cubre toda la pantalla
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
                    overlay.innerHTML = "<p style='color: white; font-size: 24px;'>Print screen y Tecla ctrl desabilitadas haz click en la pantalla para desactivar este aviso.</p>";
                    document.body.appendChild(overlay);

                    // Eliminar el overlay al hacer clic o después de 3 segundos
                    overlay.addEventListener('click', () => overlay.remove());
                    setTimeout(() => {
                        const el = document.getElementById('screenshotOverlay');
                        if (el) el.remove();
                    }, 3000);
                };

                document.addEventListener('DOMContentLoaded', () => {
                    window.addEventListener('keyup', e => {
                        // Detecta Print Screen (44) o Ctrl (17)
                        if (e.keyCode === 44 || e.keyCode === 17) {
                            copyToClipboard();
                        }
                    });

                    window.addEventListener('focus', () => document.body.style.display = 'block');
                    window.addEventListener('blur', () => document.body.style.display = 'none');
                });
            </script>
        @endif



    </head>
    <body>
    <!-- <header class="bg-white shadow">
        <div class="max-w-7xl mx-auto px-4 py-6 flex justify-between items-center">
            @if (Route::has('login'))
                <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Log in</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="ml-4 font-semibold text-gray-600 hover:text-gray-900 focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

        </div>
    </header> -->

    <div class="login-register__container">
        <div class="font-sans text-gray-900 antialiased md-50">
            {{ $slot }}
        </div>
        <div class="img-login__register md-50">

        </div>
    </div>

        <!-- Footer -->
          <!-- <footer class="bg-footer text-white border-t border-gray-200">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 text-center text-sm">
                © {{ date('Y') }} {{ config('app.name', 'MBS') }}. Todos los derechos reservados.
            </div>
        </footer> -->
        </div>
        @stack('modals')
        @livewireScripts
        @stack('scripts')
    </body>
</html>

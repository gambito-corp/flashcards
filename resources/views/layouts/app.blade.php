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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
          integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
          crossorigin="anonymous" referrerpolicy="no-referrer"/>


    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="{{ asset('estilos.css') }}">

    <!-- Styles -->
    <livewire:styles/>
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
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body class="font-sans antialiased">
<!-- Texto flotante para entorno PRE -->
@if(app()->environment('pre'))
    <div class="environment-indicator">
        ENTORNO PRE - AMBIENTE DE PRUEBAS
    </div>
@endif
<x-banner/>
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
    <footer
        class="bg-footer text-white  z-10 footer-mbs flex flex-col  md:flex-row justify-between px-[20px] md:px-[50px] py-[35px] ">
        <div class=" text-sm text-center mb-3 md:mb-0">
            © {{ date('Y') }} {{ config('app.name', 'Laravel') }}. Todos los derechos reservados.
        </div>
        <div class="text-sm flex flex-col  md:flex-row gap-4 underline text-center"><a
                href="https://medbystudents.com/libro-de-reclamaciones/" target="_blank">Libro de reclamaciones</a><a
                href="https://medbystudents.com/politicas-de-privacidad/" target="_blank">Políticas de privacidad</a>
        </div>
    </footer>
</div>
@stack('modals')
<livewire:scripts/>
@stack('scripts')
</body>
</html>

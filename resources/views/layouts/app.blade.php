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

    <!-- Styles -->
    @livewireStyles
    @stack('styles')
</head>
<body class="font-sans antialiased">
<x-banner />

<div class="min-h-screen bg-gray-100">
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
    <main class="h-screen overflow-y-auto">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-footer text-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 text-center text-sm">
            © {{ date('Y') }} {{ config('app.name', 'Laravel') }}. Todos los derechos reservados.
        </div>
    </footer>
</div>
@stack('modals')
@livewireScripts
@stack('scripts')
</body>
</html>

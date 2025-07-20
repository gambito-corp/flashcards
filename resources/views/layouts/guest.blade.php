<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Panel de Administración')</title>

    {{-- Fuente gratuita ‑ Figtree --}}
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet"/>

    @stack('styles')
</head>
<body class="d-flex flex-column min-vh-100">
<main class="flex-grow-1">
    @yield('content')
</main>
</body>
</html>

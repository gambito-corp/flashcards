@extends('layouts.errors')

@section('content')
    <div class="flex flex-col items-center justify-center min-h-[70vh] px-4">
        <div class="text-[120px] leading-none font-bold text-yellow-500 drop-shadow-lg">419</div>
        <i class="fa-solid fa-clock-rotate-left text-[64px] text-yellow-300 mt-2 mb-4 animate-pulse"></i>
        <h2 class="text-2xl mt-2 font-semibold text-gray-700">¡Tu sesión ha expirado!</h2>
        <p class="text-gray-500 mt-2 mb-6 text-lg">
            Por seguridad, tu sesión ha caducado.<br>
            Por favor, recarga la página o inicia sesión de nuevo.
        </p>
        <a href="{{ url()->previous() }}"
           class="px-6 py-2 bg-yellow-600 text-white rounded-full shadow hover:bg-yellow-700 transition">
            <i class="fa-solid fa-arrow-rotate-left mr-2"></i> Volver atrás
        </a>
    </div>
@endsection

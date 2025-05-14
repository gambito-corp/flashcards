@extends('layouts.errors')

@section('content')
    <div class="flex flex-col items-center justify-center min-h-[70vh] px-4">
        <div class="text-[120px] leading-none font-bold text-blue-400 drop-shadow-lg">503</div>
        <i class="fa-solid fa-tools text-[64px] text-blue-300 mt-2 mb-4 animate-wrench"></i>
        <h2 class="text-2xl mt-2 font-semibold text-gray-700">¡Estamos en mantenimiento!</h2>
        <p class="text-gray-500 mt-2 mb-6 text-lg">
            Nuestro equipo está trabajando en mejoras.<br>
            Vuelve en unos minutos.
        </p>
        <a href="{{ url('/') }}"
           class="px-6 py-2 bg-blue-500 text-white rounded-full shadow hover:bg-blue-600 transition">
            <i class="fa-solid fa-clock mr-2"></i> Intentar de nuevo
        </a>
    </div>
@endsection

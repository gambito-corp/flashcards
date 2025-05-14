@extends('layouts.errors')

@section('content')
    <div class="flex flex-col items-center justify-center min-h-[70vh] px-4">
        <div class="text-[120px] leading-none font-bold text-red-500 drop-shadow-lg">403</div>
        <i class="fa-solid fa-ban text-[64px] text-red-300 mt-2 mb-4 animate-shake"></i>
        <h2 class="text-2xl mt-2 font-semibold text-gray-700">Acceso denegado</h2>
        <p class="text-gray-500 mt-2 mb-6 text-lg">
            No tienes permisos para ver esta sección.<br>
            Si crees que esto es un error, contacta con el administrador.
        </p>
        <a href="{{ url('/') }}"
           class="px-6 py-2 bg-red-600 text-white rounded-full shadow hover:bg-red-700 transition">
            <i class="fa-solid fa-door-closed mr-2"></i> Volver al inicio
        </a>
    </div>
@endsection

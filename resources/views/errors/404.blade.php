@extends('layouts.errors')

@section('content')
    <div class="flex flex-col items-center justify-center min-h-[70vh] px-4">
        <div class="text-[120px] leading-none font-bold text-indigo-500 drop-shadow-lg">404</div>
        <i class="fa-solid fa-ghost text-[64px] text-indigo-300 mt-2 mb-4 animate-bounce"></i>
        <h2 class="text-2xl mt-2 font-semibold text-gray-700">¡Ups! No encontramos esa página</h2>
        <p class="text-gray-500 mt-2 mb-6 text-lg">
            Puede que hayas escrito mal la dirección o la página fue movida/eliminada.
        </p>
        <a href="{{ url('/') }}"
           class="px-6 py-2 bg-indigo-600 text-white rounded-full shadow hover:bg-indigo-700 transition">
            <i class="fa-solid fa-house mr-2"></i> Volver al inicio
        </a>
    </div>
@endsection

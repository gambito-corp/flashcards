@extends('layouts.errors')

@section('content')
    <div class="flex flex-col items-center justify-center min-h-[70vh] px-4">
        <div class="text-[120px] leading-none font-bold text-gray-400 drop-shadow-lg">500</div>
        <i class="fa-solid fa-bug text-[64px] text-gray-300 mt-2 mb-4 animate-spin-slow"></i>
        <h2 class="text-2xl mt-2 font-semibold text-gray-700">¡Ocurrió un error inesperado!</h2>
        <p class="text-gray-500 mt-2 mb-6 text-lg">
            Estamos trabajando para solucionarlo.<br>
            Si el problema persiste, contáctanos y dinos qué intentabas hacer.
        </p>
        @if(Auth::check() && Auth::id() === 1)
            <div
                class="bg-red-100 text-red-800 border border-red-200 rounded px-4 py-3 mb-4 text-left max-w-2xl mx-auto break-words">
                <strong>Mensaje de error:</strong>
                <pre class="whitespace-pre-line break-all">{{ $exception->getMessage() }}</pre>
                @if($exception->getFile())
                    <div class="mt-2 text-xs text-gray-700">
                        Archivo: <span class="font-mono">{{ $exception->getFile() }}</span><br>
                        Línea: <span class="font-mono">{{ $exception->getLine() }}</span>
                    </div>
                @endif
                @if($exception->getTraceAsString())
                    <details class="mt-2">
                        <summary class="cursor-pointer text-blue-600 underline">Ver traza completa</summary>
                        <pre class="whitespace-pre-line text-xs">{{ $exception->getTraceAsString() }}</pre>
                    </details>
                @endif
            </div>
        @endif

        <a href="{{ url('/') }}"
           class="px-6 py-2 bg-gray-600 text-white rounded-full shadow hover:bg-gray-700 transition">
            <i class="fa-solid fa-rotate-right mr-2"></i> Volver a la página principal
        </a>
    </div>
@endsection

{{--@extends('layouts.app')--}}

{{--@section('content')--}}
{{--    @if(!Auth::user()->hasAnyRole(['root', 'admin', 'colab']))--}}
{{--        <div class="container mx-auto p-4">--}}
{{--            <p class="text-red-500 text-lg font-bold">No tienes permisos para acceder a esta sección.</p>--}}
{{--        </div>--}}
{{--    @else--}}
{{--        <div class="container mx-auto p-4">--}}
{{--            <h1 class="text-2xl font-bold mb-4">Crear Preguntas</h1>--}}
{{--            <form action="{{ route('preguntas.store') }}" method="POST">--}}
{{--                @csrf--}}
{{--                <!-- Campo para el enunciado -->--}}
{{--                <div class="mb-4">--}}
{{--                    <label for="content" class="block text-sm font-medium text-gray-700">Enunciado de la pregunta:</label>--}}
{{--                    <textarea name="content" id="content" rows="4" class="mt-1 block w-full border-gray-300 rounded" placeholder="Escribe aquí el enunciado"></textarea>--}}
{{--                </div>--}}
{{--                <!-- Tipo de pregunta -->--}}
{{--                <div class="mb-4">--}}
{{--                    <label for="question_type" class="block text-sm font-medium text-gray-700">Tipo de pregunta:</label>--}}
{{--                    <select name="question_type" id="question_type" class="mt-1 block w-full border-gray-300 rounded">--}}
{{--                        <option value="multiple_choice">Multiple Choice</option>--}}
{{--                        <option value="boolean">Verdadero/Falso</option>--}}
{{--                        <option value="range">Rango</option>--}}
{{--                    </select>--}}
{{--                </div>--}}
{{--                <!-- Explicación -->--}}
{{--                <div class="mb-4">--}}
{{--                    <label for="explanation" class="block text-sm font-medium text-gray-700">Explicación (opcional):</label>--}}
{{--                    <textarea name="explanation" id="explanation" rows="3" class="mt-1 block w-full border-gray-300 rounded" placeholder="Explicación de la respuesta correcta"></textarea>--}}
{{--                </div>--}}
{{--                <!-- Otros campos opcionales (ej. medios, categoría, etc.) -->--}}
{{--                <div class="mb-4">--}}
{{--                    <label for="category_id" class="block text-sm font-medium text-gray-700">Categoría:</label>--}}
{{--                    <select name="category_id" id="category_id" class="mt-1 block w-full border-gray-300 rounded">--}}
{{--                        <!-- Aquí puedes recorrer tus categorías disponibles -->--}}
{{--                        @foreach($categories as $category)--}}
{{--                            <option value="{{ $category->id }}">{{ $category->name }}</option>--}}
{{--                        @endforeach--}}
{{--                    </select>--}}
{{--                </div>--}}

{{--                <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded">--}}
{{--                    Guardar Pregunta--}}
{{--                </button>--}}
{{--            </form>--}}
{{--        </div>--}}
{{--    @endif--}}
{{--@endsection--}}

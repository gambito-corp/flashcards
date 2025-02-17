<x-main-layout title="hola">
    <div class="p-6 lg:p-8 bg-white border-b border-gray-200">
        <div class="flex items-center">
            <div class="ml-4">
                <h1 class="text-2xl font-semibold text-gray-800 leading-tight">{{ __('Welcome back, :name', ['name' => Auth::user()->name]) }}</h1>
            </div>
        </div>
    </div>
</x-main-layout>

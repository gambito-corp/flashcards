<div>
    <x-app-layout :title="$title" :icon="$icon">
        <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $title ?? __('Dashboard') }}
            </h2>
        </x-slot>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </x-app-layout>
</div>

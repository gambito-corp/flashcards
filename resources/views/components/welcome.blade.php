<div class="p-6 lg:p-8 bg-white border-b border-gray-200">
    <div class="flex items-center">
        <div class="flex-shrink-0">
            <img class="h-12 w-12 rounded-full" src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" />
        </div>

        <div class="ml-4">
            <h1 class="text-2xl font-semibold text-gray-800 leading-tight">{{ __('Welcome back, :name', ['name' => Auth::user()->name]) }}</h1>
            <p class="text-sm text-gray-600">{{ __('You are logged in!') }}</p>
            <livewire:csv.carga-csv />
        </div>
    </div>
</div>

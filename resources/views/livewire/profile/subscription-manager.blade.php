<x-form-section submit="toggleSubscription">
    <x-slot name="title">
        {{ __('Gestión de Suscripción') }}
    </x-slot>

    <x-slot name="description">
        {{ __('Administra el estado de tu suscripción y realiza acciones relacionadas.') }}
    </x-slot>

    <x-slot name="form">
        <!-- Estado de la suscripción -->
        <div class="col-span-6 sm:col-span-4 space-y-4">
            @if ($matchingResult)
                <div class="p-4 rounded-lg
                    @if($matchingResult['status'] === 'active') bg-green-50 @endif
                    @if($matchingResult['status'] === 'inactive') bg-yellow-50 @endif
                    @if($matchingResult['status'] === 'cancelled') bg-red-50 @endif">

                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium
                                @if($matchingResult['status'] === 'active') text-green-600 @endif
                                @if($matchingResult['status'] === 'inactive') text-yellow-600 @endif
                                @if($matchingResult['status'] === 'cancelled') text-red-600 @endif">
                                Estado actual:
                                @if($matchingResult['status'] === 'active') Activa @endif
                                @if($matchingResult['status'] === 'inactive') Pausada @endif
                                @if($matchingResult['status'] === 'cancelled') Cancelada @endif
                            </p>
                            <p class="text-sm text-gray-500 mt-1">
                                Próximo cobro: {{ $matchingResult['next_payment_date'] ?? 'N/A' }}
                            </p>
                        </div>

                        <div class="flex space-x-2">
                            @if($matchingResult['status'] === 'active')
                                <x-button type="button" wire:click="pauseSubscription"
                                          wire:loading.attr="disabled"
                                          class="bg-yellow-600 hover:bg-yellow-500">
                                    {{ __('Pausar Suscripción') }}
                                </x-button>
                            @endif

                            @if($matchingResult['status'] === 'inactive')
                                <x-button type="button" wire:click="resumeSubscription"
                                          wire:loading.attr="disabled"
                                          class="bg-green-600 hover:bg-green-500">
                                    {{ __('Reactivar Suscripción') }}
                                </x-button>
                            @endif

                            @if(in_array($matchingResult['status'], ['active', 'inactive']))
                                <x-button type="button" wire:click="cancelSubscription"
                                          wire:loading.attr="disabled"
                                          class="bg-red-600 hover:bg-red-500">
                                    {{ __('Cancelar Permanentemente') }}
                                </x-button>
                            @endif
                        </div>
                    </div>
                </div>
            @else
                <div class="p-4 bg-gray-50 rounded-lg">
                    <p class="text-gray-600">
                        {{ __('No tienes una suscripción activa actualmente.') }}
                    </p>
                </div>
            @endif
        </div>
    </x-slot>

    <x-slot name="actions">
        <x-action-message class="mr-3" on="subscriptionUpdated">
            {{ __('Estado actualizado correctamente') }}
        </x-action-message>

        @if(session()->has('message'))
            <div class="mr-3 text-green-600">
                {{ session('message') }}
            </div>
        @endif

        @if(session()->has('error'))
            <div class="mr-3 text-red-600">
                {{ session('error') }}
            </div>
        @endif
    </x-slot>
</x-form-section>

<div class="max-w-7xl mx-auto p-4">
    <!-- Sección de Historial de Chats -->
    <div class="mb-4">
        <h3 class="text-lg font-bold mb-2">Historial de Conversaciones</h3>
        <div class="flex space-x-4 overflow-x-auto">
            @foreach($chatHistory as $chat)
                <button wire:click="selectChat({{ $chat->id }})"
                        class="px-4 py-2 rounded border
                        {{ $activeChatId == $chat->id ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-800' }}">
                    Chat #{{ $chat->id }} <br>
                    <span class="text-xs">{{ $chat->created_at->format('d/m/Y') }}</span>
                </button>
            @endforeach
        </div>
    </div>

    <!-- Área del Chat -->
    <div class="bg-white shadow rounded-lg flex flex-col h-[60vh] mt-4">
        <!-- Chat Header -->
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-2xl font-bold text-gray-800">Chat MediSearch</h2>
        </div>

        <!-- Área de Mensajes -->
        <div class="flex-1 p-4 overflow-y-auto space-y-3" id="chat-messages">
            @foreach($messages as $message)
                @if($message['from'] === 'user')
                    <div class="flex justify-end">
                        <div class="bg-blue-100 text-gray-800 px-4 py-2 rounded-lg my-2">
                            {{ $message['text'] }}
                        </div>
                    </div>
                @elseif($message['from'] === 'articles')
                    <div class="flex flex-wrap gap-4 p-2">
                        @foreach($message['data'] as $article)
                            <div class="bg-white shadow rounded p-4 w-64 border">
                                <h3 class="font-bold text-lg">{{ $article['title'] }}</h3>
                                <p class="text-sm text-gray-600">Por: {{ implode(', ', $article['authors']) }}</p>
                                <p class="text-xs text-gray-500">{{ $article['journal'] }} - {{ $article['year'] }}</p>
                                <a href="{{ $article['url'] }}" class="text-blue-500 text-sm" target="_blank">Ver más</a>
                            </div>
                        @endforeach
                    </div>
                @elseif($message['from'] === 'bot')
                    <div class="flex justify-start">
                        <div class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg my-2">
                            <!-- Se usa data-fulltext para efecto typing -->
                            <span class="bot-text">{{ $message['text'] }}</span>
                        </div>
                    </div>
                @endif
            @endforeach

            <!-- Mensaje de "Razonando..." mientras se procesa el envío -->
            <div wire:loading.delay wire:target="sendMessage" class="flex justify-start">
                <div class="bg-gray-200 text-gray-800 px-4 py-2 rounded-lg my-2">
                    Razonando...
                </div>
            </div>
        </div>

        <!-- Formulario de Envío -->
        <div class="p-4 border-t border-gray-200">
            <form wire:submit.prevent="sendMessage" class="flex">
                @if(($queryCount <= 99))
                    <input
                            type="text"
                            wire:model.defer="newMessage"
                            placeholder="Escribe tu mensaje..."
                            class="flex-1 border border-gray-300 rounded-l-lg px-4 py-2 focus:outline-none focus:ring focus:border-blue-300"
                    />

                    <button wire:loading.attr="disabled" type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-r-lg">
                        Enviar
                    </button>
                @endif
            </form>
            @if(!Auth::user()->hasRole('root'))
                <div class="p-2 text-sm text-gray-600 border-b border-gray-200">
                    Has realizado {{ $queryCount }} preguntas este mes. Te quedan {{ 100 - $queryCount }}.
                    @if($queryCount >= 100)
                        Puedes volver a preguntar en
                        {{ \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::now()->endOfMonth()) }} días.
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

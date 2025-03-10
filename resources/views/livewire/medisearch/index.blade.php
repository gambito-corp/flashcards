<div class="max-w-4xl mx-auto bg-white shadow rounded-lg flex flex-col h-[80vh]">
    <!-- Chat Header -->
    <div class="p-4 border-b border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800">Chat MediSearch</h2>
    </div>

    <!-- Área de mensajes -->
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
                        <!-- Se usa data-fulltext para aplicar efecto typing en JS -->
                        <span class="bot-text">{{ $message['text'] }}></span>
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



    <!-- Formulario de envío -->
    <div class="p-4 border-t border-gray-200">
        <form wire:submit.prevent="sendMessage" class="flex">
            <input
                type="text"
                wire:model.defer="newMessage"
                placeholder="Escribe tu mensaje..."
                class="flex-1 border border-gray-300 rounded-l-lg px-4 py-2 focus:outline-none focus:ring focus:border-blue-300"
            />
            <button  wire:loading.attr="disabled" type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-r-lg">
                Enviar
            </button>
        </form>
    </div>
</div>

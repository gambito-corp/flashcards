<!-- resources/views/livewire/chat-stream.blade.php -->

<div>
    <div class="chat-container">
        @foreach($messages as $message)
            <div class="{{ $message['role'] === 'user' ? 'user-message' : 'bot-message' }}">
                {!! $message['content'] !!}
            </div>
        @endforeach

        <!-- Aquí se mostrará el stream en tiempo real -->
            <div wire:stream="streaming_response" class="response-container">
                {!! $streamedResponse !!}
            </div>
    </div>

    <form wire:submit.prevent="ask">
        <input
            type="text"
            wire:model.live="prompt"
            placeholder="Escribe tu pregunta..."
        >
        <button type="submit">Enviar</button>
    </form>

    <style>
        .chat-container {
            max-width: 600px;
            margin: 20px auto;
        }

        .user-message {
            background: #e3f2fd;
            padding: 10px;
            margin: 5px;
            border-radius: 10px;
        }

        .bot-message {
            background: #f5f5f5;
            padding: 10px;
            margin: 5px;
            border-radius: 10px;
        }

    </style>
</div>

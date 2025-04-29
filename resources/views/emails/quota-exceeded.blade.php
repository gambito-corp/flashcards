@component('mail::message')
    # ¡Alerta de cuota excedida!

    Se ha superado la cuota de OpenAI y se ha activado el fallback a Medisearch.

    **Detalles de la consulta:**
    - Query: {{ $payload['query'] ?? 'N/A' }}
    - Fecha: {{ now()->format('d/m/Y H:i') }}

    @component('mail::button', ['url' => config('app.url')])
        Ver sistema
    @endcomponent

    Gracias,<br>
    {{ config('app.name') }}
@endcomponent

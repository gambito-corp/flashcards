<form action="{{ route('payment.create') }}" method="POST">
    @csrf
    <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded">
        Pagar con Mercado Pago
    </button>
</form>

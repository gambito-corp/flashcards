<x-app-layout title="palnes">
    <div class="max-w-5xl mx-auto p-4">
        <!-- Contenedor de cards: 1 columna en móviles y 2 en pantallas medianas y superiores -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-10">

            <!-- Card para el Plan Básico -->
            <div class="bg-white rounded-2xl border border-gray-200 hover:shadow-xl transition-shadow duration-300 p-5 flex-center flex">
                <div class="p-6">
                    <h2 class="text-2xl font-bold mb-3">Plan Básico</h2>
                    <p class="text-gray-600 mb-4">
                        Ideal para quienes comienzan y desean conocer nuestras funcionalidades.
                    </p>
                    <div class="text-3xl font-extrabold mb-4">
                        $9.99<span class="text-base font-normal">/mes</span>
                    </div>
                    <a href="{{ route('mercadopago.createSubscription', ['productId' => 1]) }}" class="w-full inline-block text-center bg-blue-500 hover:bg-blue-600 text-white py-2 rounded-lg transition-colors duration-300">
                        Seleccionar Plan
                    </a>
                </div>
            </div>

            <!-- Card para el Plan Premium -->
            <div class="bg-white rounded-2xl  border border-gray-200 hover:shadow-xl transition-shadow duration-300">
                <div class="p-6">
                    <h2 class="text-2xl font-bold mb-3">Plan Premium</h2>
                    <p class="text-gray-600 mb-4">
                        Para usuarios avanzados que buscan todas las funcionalidades y soporte prioritario.
                    </p>
                    <div class="text-3xl font-extrabold mb-4">
                        $19.99<span class="text-base font-normal">/mes</span>
                    </div>
                    <a href="{{ route('mercadopago.createSubscription', ['productId' => 2]) }}" class="w-full inline-block text-center bg-green-500 hover:bg-green-600 text-white py-2 rounded-lg transition-colors duration-300">
                        Seleccionar Plan
                    </a>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>

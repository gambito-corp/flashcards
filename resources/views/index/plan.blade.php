<x-app-layout title="Planes">
    <script src="https://sdk.mercadopago.com/js/v2"></script>

    <div class="max-w-5xl mx-auto p-4">
        <!-- Contenedor de cards: 1 columna en móviles y 2 en pantallas medianas y superiores -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-10">
            <div class="bg-white rounded-2xl border border-gray-200 hover:shadow-xl transition-shadow duration-300 py-5">
                <div class="p-6 text-center flex content-center flex-col">
                    <img class="text-center img-planes w-[100px] m-auto pb-5 "  src="/imgc.webp" alt="Planes">
                    <h2 class="text-2xl font-bold mb-3">{{$product->name}}</h2>
                    <div class="text-2xl font-extrabold  primary-color ">
                        S/ {{$product->price}}<span class="text-base font-normal font-black "></span>
                    </div>
                    <ul class="py-3 pb-6 text-[#727275] text-[17px] leading-[30px]  font-medium ">
                        <li>Examenes ilimitados</li>
                        <li>Uso de flashcards</li>
                        <li>Control y estadisticas de nota</li>
                        <li>Inteligencia artificial e Medisearch</li>
                    </ul>

                </div>
                <a href="{{$preference->init_point}}" class=""> PAGAR AHORA</a>
                <div id="wallet_container"></div>
            </div>
        </div>
    </div>
    <script>
        const mp = new MercadoPago("{{config('services.mercadopago.public_key')}}");
        const bricksBuilder = mp.bricks();
        mp.bricks().create("wallet", "wallet_container", {
            inicialization:{
                preferenceId: "{{$preferenceId}}",
            },
            customization:{
                theme: 'default',
            },
        })
    </script>
</x-app-layout>

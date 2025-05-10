<x-app-layout title="Planes">
    <div class="max-w-5xl p-4 mx-auto">
        <div class="grid grid-cols-1 gap-6 my-10 md:grid-cols-2">
                @forelse ($planes as $plan)
                    <div class="py-5 transition-shadow duration-300 bg-white border border-gray-200 rounded-2xl hover:shadow-xl">
                        <div class="flex flex-col content-center p-6 text-center">
                            <img class="text-center img-planes w-[100px] m-auto pb-5 "  src="/imgc.webp" alt="Planes">
                            <h2 class="mb-3 text-2xl font-bold">{{$plan->name}}</h2>
                            <div class="text-2xl font-extrabold primary-color ">
                                S/ {{$plan->price}}<span class="text-base font-normal font-black "></span>
                            </div>
                            <ul class="py-3 pb-6 text-[#727275] text-[17px] leading-[30px]  font-medium ">
                                <li>Examenes ilimitados</li>
                                <li>Uso de flashcards</li>
                                <li>Control y estadisticas de nota</li>
                                <li>Inteligencia artificial e Medisearch</li>
                            </ul>
                            <a  href="{{route('gettigPay', $plan->id)}}" class="inline-block w-full py-2 text-center text-white transition-colors duration-300 bg-blue-500 rounded-lg hover:bg-blue-600">
                                Seleccionar Plan
                            </a>
                    </div>
                    </div>
            @empty
                <h1>No se cargaron planes todavía...</h1>
            @endforelse
        </div>
    </div>
</x-app-layout>

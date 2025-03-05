<x-main-layout title="hola">
    <div class="p-6 lg:p-8 bg-white border-b border-gray-200  container-ask">
        <div class="flex items-center">


            <div class="mb-4 bg-[radial-gradient(circle, rgb(2,85,91), rgb(1,25,27))]">
                <div class="grid grid-cols-12 p-4">
                    <div class="col-span-9">
                        <h3 class="mb-3 text-yellow-500">Obtén acceso total</h3>
                        <div>Lleva tu preparación al siguiente nivel con todas las funcionalidades de ENAM.pe.</div>
                        <a href="{{route('planes')}}" class="mt-3 inline-block bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded">
                            Más información
                        </a>
                    </div>
                    <div class="col-span-3 flex items-center justify-center">
                        <i class="fal fa-graduation-cap fa-3x opacity-30"></i>
                    </div>
                </div>
            </div>


        </div>
    </div>
</x-main-layout>

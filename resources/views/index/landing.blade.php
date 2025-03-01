<x-guest-layout>
    <main class="max-w-7xl mx-auto p-4">
        <section class="text-center">
            <h2 class="text-2xl font-semibold mb-4">Descubre Nuestro Producto</h2>
            <p class="mb-8">Esta es una landing page de ejemplo para mostrar cómo diseñar la página de inicio de tu sitio web.</p>
            <a href="{{ route('dashboard') }}" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded">
                Comenzar
            </a>
        </section>
    </main>
    <footer class="bg-white mt-8 p-4 text-center">
        <p class="text-gray-600">© {{ date('Y') }} Mi Sitio. Todos los derechos reservados.</p>
    </footer>
</x-guest-layout>

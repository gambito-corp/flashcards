<x-main-layout title="Bienvenido {{auth()->user()->name}}">
    <div class=" bg-white border-b border-gray-200  container-ask box-dashboard relative overflow-hidden max-sm:m-5 shadow-md">
        <div class="mb-4 bg-[radial-gradient(circle, rgb(2,85,91), rgb(1,25,27))]">
            <div class="grid grid-cols-2 relative">
                <div class="span-2">
                    <h2 class="mb-3 text-yellow-500 primary-color title-ask-container color-white">Obtén acceso total</h2>
                    <hr>
                    <p class="pb-3"> Lleva tu preparación al siguiente nivel con todas las funcionalidades de medbystudents.</p>
                    <a href="{{route('planes')}}" class="mt-3 inline-block bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 px-4 rounded boton-success-m">
                        Más información<img src="https://medbystudents.com/app-banqueo/wp-content/uploads/2025/03/arrow-mbs.svg"/>
                    </a>
                    <img class="absolute right-0 bottom-0 mix-blend-luminosity hidden lg:block" src="{{asset('dashboard-img.png')}}" />
                </div>
                <div class="col-span-3 flex items-center justify-center">
                    <i class="fal fa-graduation-cap fa-3x opacity-30"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="">

        <!-- Card 2: Tabla de últimos exámenes realizados -->
        <div class="mb-6 bg-white rounded-lg shadow-md border border-gray-200">
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-4">Últimos Exámenes Realizados</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Examen</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Calificación</th>
                        </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <!-- Ejemplo de filas -->
                            @forelse($exams as $item)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{$item->title}}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{ $item->created_at->format('d/m/Y') }} ({{ $item->created_at->diffForHumans() }})
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        {{$item->examResults->avg('total_score')}}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">Matemáticas</td>
                                    <td class="px-6 py-4 whitespace-nowrap">2025-02-25</td>
                                    <td class="px-6 py-4 whitespace-nowrap">85%</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Card 3: Gráfico de evolución -->
        <div class="mb-6 bg-white rounded-lg shadow-md border border-gray-200">
            <div class="p-6">
                <h2 class="text-2xl font-bold mb-4">Evolución del Usuario</h2>
                <!-- Selector para elegir el rango de tiempo -->
                <select id="timeframe" class="mb-4 p-2 border rounded">
                    <option value="day">Día a Día</option>
                    <option value="week">Semana a Semana</option>
                    <option value="month">Mes a Mes</option>
                </select>
                <!-- Contenedor para el gráfico -->
                <canvas id="evolutionChart" width="400" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- Scripts para el gráfico (utilizando Chart.js) -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Datos que provienen del controlador (asegúrate de que las variables existan)
        const dailyLabels = {!! json_encode($dailyLabels) !!};
        const dailyData   = {!! json_encode($dailyData) !!};

        const weeklyLabels = {!! json_encode($weeklyLabels) !!};
        const weeklyData   = {!! json_encode($weeklyData) !!};

        const monthlyLabels = {!! json_encode($monthlyLabels) !!};
        const monthlyData   = {!! json_encode($monthlyData) !!};

        const ctx = document.getElementById('evolutionChart').getContext('2d');

        // Inicializa el gráfico con los datos diarios por defecto
        let chart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: dailyLabels,
                datasets: [{
                    label: 'Progreso de calificaciones',
                    data: dailyData,
                    fill: false,
                    borderColor: 'rgb(75, 192, 192)',
                    tension: 0.1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });

        // Cambio dinámico del dataset según el valor seleccionado
        document.getElementById('timeframe').addEventListener('change', function() {
            const value = this.value;
            let newLabels, newData;

            if (value === 'day') {
                newLabels = dailyLabels;
                newData = dailyData;
            } else if (value === 'week') {
                newLabels = weeklyLabels;
                newData = weeklyData;
            } else if (value === 'month') {
                newLabels = monthlyLabels;
                newData = monthlyData;
            }

            chart.data.labels = newLabels;
            chart.data.datasets[0].data = newData;
            chart.update();
        });
    </script>
</x-main-layout>

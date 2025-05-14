<div class="container mx-auto py-10">
    <h1 class="text-3xl font-bold mb-8 text-[#195b81]">Dashboard de Análisis</h1>

    {{-- Card de Totales Globales --}}
    <div class="flex flex-wrap gap-6 mb-10">
        <div class="bg-white shadow rounded-lg p-6 min-w-[320px]">
            <h2 class="text-xl font-semibold mb-2 text-[#195b81]">Totales Globales</h2>
            <div class="grid grid-cols-2 gap-4 text-lg">
                <div>Respondidas:</div>
                <div class="font-bold">{{ $global['total_respondidas'] }}</div>
                <div>Correctas:</div>
                <div class="font-bold text-green-600">{{ $global['total_correctas'] }}</div>
                <div>Incorrectas:</div>
                <div class="font-bold text-red-600">{{ $global['total_incorrectas'] }}</div>
                <div>Porcentaje:</div>
                <div class="font-bold text-blue-600">{{ $global['porcentaje'] }}%</div>
            </div>
        </div>
    </div>

    {{-- Gráfico de barras: Favoritismo por Área --}}
    <div class="bg-white shadow rounded-lg p-6 mb-10">
        <h2 class="text-xl font-semibold mb-4 text-[#195b81]">Favoritismo Ponderado por Área (%)</h2>
        <canvas id="barFavoritismo"></canvas>
    </div>

    {{-- Gráfico de Radar: Análisis por Área --}}
    <div class="bg-white shadow rounded-lg p-6 mb-10">
        <h2 class="text-xl font-semibold mb-4 text-[#195b81]">Radar de Áreas: Respondidas, Correctas, Incorrectas</h2>
        <canvas id="radarAreas"></canvas>
    </div>

    {{-- Pasa los datos SOLO como JSON seguro --}}
    <script>
        const areas = @json($estadisticas);
        console.log('AREAS (estadisticas):', areas);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        let barChart = null;
        let radarChart = null;

        function renderCharts() {
            // --- Gráfico de barras: Favoritismo ---
            const barLabels = areas.map(a => a.area_name);
            const barData = areas.map(a => a.favoritismo || 0);

            // Límite superior dinámico (mín. 100)
            const maxBar = Math.max(100, ...barData) || 100;

            const ctxBar = document.getElementById('barFavoritismo');
            if (ctxBar) {
                if (barChart) barChart.destroy();
                barChart = new Chart(ctxBar.getContext('2d'), {
                    type: 'bar',
                    data: {
                        labels: barLabels,
                        datasets: [{
                            label: 'Favoritismo (%)',
                            data: barData,
                            backgroundColor: 'rgba(30, 144, 255, 0.7)',
                            borderColor: 'rgba(30, 144, 255, 1)',
                            borderWidth: 2
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                max: maxBar
                            }
                        }
                    }
                });
            }

            // --- Gráfico de Radar: Respondidas, Correctas, Incorrectas ---
            const radarLabels = areas.map(a => a.area_name);
            const dataRespondidas = areas.map(a => a.total_respondidas || 0);
            const dataCorrectas = areas.map(a => a.total_correctas || 0);
            const dataIncorrectas = areas.map(a => a.total_incorrectas || 0);

            const maxRadar = Math.max(1, ...dataRespondidas, ...dataCorrectas, ...dataIncorrectas) + 2;

            const ctxRadar = document.getElementById('radarAreas');
            if (ctxRadar) {
                if (radarChart) radarChart.destroy();
                radarChart = new Chart(ctxRadar.getContext('2d'), {
                    type: 'radar',
                    data: {
                        labels: radarLabels,
                        datasets: [
                            {
                                label: 'Total Respondidas',
                                data: dataRespondidas,
                                backgroundColor: 'rgba(128,128,128,0.2)',
                                borderColor: 'rgba(128,128,128,0.7)',
                                pointBackgroundColor: 'rgba(128,128,128,1)',
                                borderWidth: 2
                            },
                            {
                                label: 'Total Correctas',
                                data: dataCorrectas,
                                backgroundColor: 'rgba(34,197,94,0.2)',
                                borderColor: 'rgba(34,197,94,1)',
                                pointBackgroundColor: 'rgba(34,197,94,1)',
                                borderWidth: 2
                            },
                            {
                                label: 'Total Incorrectas',
                                data: dataIncorrectas,
                                backgroundColor: 'rgba(239,68,68,0.2)',
                                borderColor: 'rgba(239,68,68,1)',
                                pointBackgroundColor: 'rgba(239,68,68,1)',
                                borderWidth: 2
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        elements: {
                            line: {borderWidth: 2}
                        },
                        scales: {
                            r: {
                                angleLines: {display: true},
                                suggestedMin: 0,
                                suggestedMax: maxRadar
                            }
                        }
                    }
                });
            }
        }

        // Livewire: Re-render cuando Livewire actualiza el DOM
        document.addEventListener('livewire:load', () => {
            renderCharts();
            if (window.Livewire) {
                window.Livewire.hook('message.processed', () => renderCharts());
            }
        });
    </script>
</div>

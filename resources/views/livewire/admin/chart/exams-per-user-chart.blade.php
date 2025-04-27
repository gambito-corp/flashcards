<div class="relative">
    <div class="flex justify-between items-center mb-4">
        <div class="flex items-center gap-2">
            <button id="openExamModalBtn" class="p-1 rounded hover:bg-gray-100 focus:outline-none">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" fill="none"/>
                    <rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" fill="none"/>
                    <path d="M3 10V3h7M21 14v7h-7" stroke="currentColor"/>
                </svg>
            </button>
            <h3 class="text-lg font-semibold">Exámenes Realizados</h3>
        </div>
        <select wire:model.live="timeRange" class="border rounded px-2 py-1">
            <option value="day">Diario</option>
            <option value="week">Semanal</option>
            <option value="month">Mensual</option>
        </select>
    </div>
    <div>
        <canvas id="examsChart" wire:ignore style="width:100%;height:300px;"></canvas>
    </div>

    <!-- Modal -->
    <div id="examChartModal" class="modal-custom">
        <div class="modal-backdrop"></div>
        <div class="modal-content-custom">
            <div class="flex justify-between items-center mb-2">
                <span class="font-semibold">Exámenes Realizados</span>
                <button id="closeExamModalBtn" class="text-2xl font-bold hover:text-gray-500">&times;</button>
            </div>
            <canvas id="examsChartModal" style="width:100%;height:90%;"></canvas>
        </div>
    </div>

    <script>
        let examsChart = null;
        let examsChartModal = null;
        const examOptions = {
            responsive: true,
            maintainAspectRatio: false,
            animation: false, // Desactivar animaciones para actualizaciones más limpias
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        stepSize: 1,
                        autoSkip: true,
                        maxTicksLimit: 10
                    },
                    title: {
                        text: 'Cantidad de Exámenes',
                        display: true
                    }
                },
                x: {
                    ticks: {
                        autoSkip: true,
                        maxTicksLimit: 15
                    },
                    title: {
                        text: 'Período',
                        display: true
                    }
                }
            }
        };

        const handleChartUpdate = (chartInstance, data) => {
            if (!chartInstance) return;

            chartInstance.data.labels = data.labels;
            chartInstance.data.datasets[0].data = data.data;

            // Método especial para actualización completa
            chartInstance.config._config.data = chartInstance.data;
            chartInstance.update('none'); // Actualizar sin animación
        };

        document.addEventListener('livewire:initialized', () => {
            const ctx = document.getElementById('examsChart').getContext('2d');

            // Inicialización inicial
            examsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @js($chartData['labels']),
                    datasets: [{
                        label: 'Exámenes',
                        data: @js($chartData['data']),
                        borderColor: 'rgb(255, 159, 64)',
                        backgroundColor: 'rgba(255, 159, 64, 0.2)',
                        borderWidth: 2,
                        tension: 0.1,
                        fill: true
                    }]
                },
                options: examOptions
            });

            Livewire.on('updateExamChart', (chartData) => {
                const data = Array.isArray(chartData) ? chartData[0] : chartData;
                console.log("Datos actualizados:", data);

                if (data?.labels && data?.data) {
                    // Actualizar gráfico principal
                    handleChartUpdate(examsChart, data);

                    // Actualizar gráfico modal si existe
                    if (examsChartModal) {
                        handleChartUpdate(examsChartModal, data);
                    }
                }
            });
        });

        // Manejo del modal
        document.addEventListener('DOMContentLoaded', () => {
            const modal = document.getElementById('examChartModal');
            const openBtn = document.getElementById('openExamModalBtn');
            const closeBtn = document.getElementById('closeExamModalBtn');

            openBtn.addEventListener('click', () => {
                if (!examsChart) return;

                modal.classList.add('visible');
                const ctxModal = document.getElementById('examsChartModal').getContext('2d');

                if (examsChartModal) {
                    examsChartModal.destroy();
                }

                // Clonar datos actuales del gráfico principal
                const chartData = {
                    labels: [...examsChart.data.labels],
                    datasets: examsChart.data.datasets.map(dataset => ({
                        ...dataset,
                        data: [...dataset.data]
                    }))
                };

                examsChartModal = new Chart(ctxModal, {
                    type: 'line',
                    data: chartData,
                    options: examOptions
                });
            });

            [closeBtn, modal.querySelector('.modal-backdrop')].forEach(el => {
                el.addEventListener('click', () => {
                    modal.classList.remove('visible');
                    if (examsChartModal) {
                        examsChartModal.destroy();
                        examsChartModal = null;
                    }
                });
            });
        });
    </script>


</div>

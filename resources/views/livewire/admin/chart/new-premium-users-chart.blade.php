<div class="relative">
    <div class="flex justify-between items-center mb-4">
        <div class="flex items-center gap-2">
            <!-- Botón Maximizar SVG -->
            <button id="openPremiumModalBtn" class="p-1 rounded hover:bg-gray-100 focus:outline-none" style="margin-right: 8px;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" fill="none"/>
                    <rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" fill="none"/>
                    <path d="M3 10V3h7M21 14v7h-7" stroke="currentColor"/>
                </svg>
            </button>
            <h3 class="text-lg font-semibold">Nuevos Usuarios Premium</h3>
        </div>
        <select wire:model.live="timeRange" class="border rounded px-2 py-1">
            <option value="day">Diario</option>
            <option value="week">Semanal</option>
            <option value="month">Mensual</option>
        </select>
    </div>
    <div>
        <canvas id="premiumUsersChart" wire:ignore style="width:100%;height:300px;"></canvas>
    </div>

    <!-- Modal -->
    <div id="premiumChartModal" class="modal-custom">
        <div class="modal-backdrop"></div>
        <div class="modal-content-custom">
            <div class="flex justify-between items-center mb-4">
                <div class="flex items-center gap-2">
                    <span class="font-semibold">Nuevos Usuarios Premium</span>
                    <select id="modalPremiumTimeRange" class="border rounded px-2 py-1 ml-4">
                        <option value="day">Diario</option>
                        <option value="week">Semanal</option>
                        <option value="month">Mensual</option>
                    </select>
                </div>
                <button id="closePremiumModalBtn" class="text-2xl font-bold hover:text-gray-500" style="line-height: 1;">&times;</button>
            </div>
            <canvas id="premiumUsersChartModal" style="width:100%;height:90%;"></canvas>
        </div>
    </div>

    <script>
        // Gráficos principales
        let premiumUsersChart, premiumUsersChartModal;

        // Opciones compartidas para ambos gráficos
        const premiumChartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        stepSize: 1
                    },
                    title: {
                        display: true,
                        text: 'Cantidad de Usuarios Premium'
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Período'
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                }
            }
        };

        // Función para actualizar ambos gráficos
        function updatePremiumCharts(data) {
            console.log('Actualizando gráficos premium con datos:', data);

            // El dato viene como array, extraer primer elemento
            const chartData = Array.isArray(data) ? data[0] : data;

            if (!chartData || !chartData.labels || !chartData.data) {
                console.error('Datos premium inválidos:', data);
                return;
            }

            // Actualizar gráfico principal
            if (premiumUsersChart) {
                premiumUsersChart.data.labels = chartData.labels;
                premiumUsersChart.data.datasets[0].data = chartData.data;
                premiumUsersChart.update('none');
            }

            // Actualizar gráfico del modal si existe
            if (premiumUsersChartModal) {
                premiumUsersChartModal.data.labels = chartData.labels;
                premiumUsersChartModal.data.datasets[0].data = chartData.data;
                premiumUsersChartModal.update('none');
            }
        }

        // Inicializar gráfico principal
        document.addEventListener('livewire:initialized', function() {
            const ctx = document.getElementById('premiumUsersChart').getContext('2d');

            premiumUsersChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: @js($chartData['labels']),
                    datasets: [{
                        label: 'Nuevos Usuarios Premium',
                        data: @js($chartData['data']),
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.1
                    }]
                },
                options: premiumChartOptions
            });

            // Escuchar actualizaciones de Livewire
            Livewire.on('updatePremiumUserChart', function(chartData) {
                updatePremiumCharts(chartData);
            });
        });

        // Modal JS
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('premiumChartModal');
            const openBtn = document.getElementById('openPremiumModalBtn');
            const closeBtn = document.getElementById('closePremiumModalBtn');
            const modalSelect = document.getElementById('modalPremiumTimeRange');

            openBtn.addEventListener('click', function() {
                modal.classList.add('visible');

                // Sincronizar select
                const mainSelect = document.querySelector('[wire\\:model\\.live="timeRange"]');
                modalSelect.value = mainSelect.value;

                setTimeout(() => {
                    const ctxModal = document.getElementById('premiumUsersChartModal').getContext('2d');
                    if (!premiumUsersChartModal) {
                        premiumUsersChartModal = new Chart(ctxModal, {
                            type: 'line',
                            data: {
                                labels: premiumUsersChart.data.labels,
                                datasets: [{
                                    label: 'Nuevos Usuarios Premium',
                                    data: premiumUsersChart.data.datasets[0].data,
                                    borderColor: 'rgba(255, 99, 132, 1)',
                                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.1
                                }]
                            },
                            options: premiumChartOptions
                        });
                    }
                }, 100);
            });

            // Manejar cambios en el selector del modal
            modalSelect.addEventListener('change', function() {
                const mainSelect = document.querySelector('[wire\\:model\\.live="timeRange"]');
                mainSelect.value = modalSelect.value;
                mainSelect.dispatchEvent(new Event('input'));
            });

            function closeModal() {
                modal.classList.remove('visible');
            }

            closeBtn.addEventListener('click', closeModal);
            modal.querySelector('.modal-backdrop').addEventListener('click', closeModal);
            document.addEventListener('keyup', function(e) {
                if (e.key === "Escape" && modal.classList.contains('visible')) closeModal();
            });
        });
    </script>
</div>

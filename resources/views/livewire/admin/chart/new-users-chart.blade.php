<div class="relative">
    <div class="flex justify-between items-center mb-4">
        <div class="flex items-center gap-2">
            <!-- Botón Maximizar SVG -->
            <button id="openModalBtn" class="p-1 rounded hover:bg-gray-100 focus:outline-none" style="margin-right: 8px;">
                <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="3" width="7" height="7" rx="1" stroke="currentColor" fill="none"/>
                    <rect x="14" y="14" width="7" height="7" rx="1" stroke="currentColor" fill="none"/>
                    <path d="M3 10V3h7M21 14v7h-7" stroke="currentColor"/>
                </svg>
            </button>
            <h3 class="text-lg font-semibold">Nuevos Usuarios</h3>
        </div>
        <select wire:model.live="timeRange" class="border rounded px-2 py-1">
            <option value="day">Diario</option>
            <option value="week">Semanal</option>
            <option value="month">Mensual</option>
        </select>
    </div>
    <div>
        <canvas id="newUsersChart" wire:ignore style="width:100%;height:300px;"></canvas>
    </div>

    <!-- Modal -->
    <div id="chartModal" class="modal-custom">
        <div class="modal-backdrop"></div>
        <div class="modal-content-custom">
            <div class="flex justify-between items-center mb-4">
                <button id="closeModalBtn" class="text-2xl font-bold hover:text-gray-500" style="line-height: 1;">&times;</button>
            </div>
            <canvas id="newUsersChartModal" style="width:100%;height:90%;"></canvas>
            <br>
        </div>
    </div>


    <style>
        .modal-custom {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0; top: 0;
            width: 100vw;
            height: 100vh;
            justify-content: center;
            align-items: center;
        }
        .modal-custom.visible {
            display: flex;
        }
        .modal-backdrop {
            position: absolute;
            left: 0;
            top: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(0,0,0,0.7);
            z-index: 1;
        }
        .modal-content-custom {
            position: relative;
            z-index: 2;
            background: #fff;
            border-radius: 8px;
            padding: 24px 24px 40px 24px; /* <-- aquí el 40px es el padding-bottom */
            max-width: 90vw;
            width: 90vw;
            max-height: 70vh;
            height: 70vh;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            display: flex;
            flex-direction: column;
            align-items: stretch;
        }
    </style>

    <script>
        // Gráficos principales
        let usersChart, usersChartModal;

        // Opciones compartidas para ambos gráficos
        const chartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        stepSize: 1 // Forzar escala por 1 para resolver problema de visualización
                    },
                    title: {
                        display: true,
                        text: 'Cantidad de Usuarios'
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

        // Función para actualizar ambos gráficos con datos nuevos
        function updateCharts(data) {
            console.log('Actualizando gráficos con datos:', data);

            // El dato viene como un array que contiene un objeto
            // Necesitamos acceder al primer elemento
            const chartData = Array.isArray(data) ? data[0] : data;

            // Verificar que los datos ahora sean válidos
            if (!chartData || !chartData.labels || !chartData.data) {
                console.error('Datos inválidos recibidos:', data);
                return;
            }

            // Actualizar gráfico principal
            if (usersChart) {
                usersChart.data.labels = chartData.labels;
                usersChart.data.datasets[0].data = chartData.data;
                usersChart.update();
            }

            // Actualizar gráfico del modal si existe
            if (usersChartModal) {
                usersChartModal.data.labels = chartData.labels;
                usersChartModal.data.datasets[0].data = chartData.data;
                usersChartModal.update();
            }
        }


        // Inicializar gráfico principal cuando Livewire esté listo
        document.addEventListener('livewire:initialized', function() {
            const ctx = document.getElementById('newUsersChart').getContext('2d');

            // Obtener datos iniciales del componente PHP
            const initialData = @js($chartData);
            console.log('Datos iniciales:', initialData); // Log para depuración

            // Crear el gráfico con los datos iniciales
            usersChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: initialData.labels,
                    datasets: [{
                        label: 'Nuevos Usuarios',
                        data: initialData.data,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.1
                    }]
                },
                options: chartOptions
            });

            // Escuchar actualizaciones de Livewire
            Livewire.on('updateUserChart', function(chartData) {
                console.log('Evento recibido con datos:', chartData);
                updateCharts(chartData);
            });
        });

        // Modal JS
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('chartModal');
            const openBtn = document.getElementById('openModalBtn');
            const closeBtn = document.getElementById('closeModalBtn');

            openBtn.addEventListener('click', function() {
                modal.classList.add('visible');
                setTimeout(() => {
                    const ctxModal = document.getElementById('newUsersChartModal').getContext('2d');

                    // Si el gráfico modal ya existe, actualizar sus datos
                    if (usersChartModal) {
                        usersChartModal.data = usersChart.data;
                        usersChartModal.update();
                    } else {
                        // Crear gráfico modal con los mismos datos del gráfico principal
                        usersChartModal = new Chart(ctxModal, {
                            type: 'line',
                            data: usersChart.data,
                            options: chartOptions
                        });
                    }
                }, 100); // Pequeño retraso para asegurar que el modal está visible
            });

            function closeModal() {
                modal.classList.remove('visible');
                // No destruimos el gráfico para mantener los datos actualizados
            }

            closeBtn.addEventListener('click', closeModal);
            modal.querySelector('.modal-backdrop').addEventListener('click', closeModal);
            document.addEventListener('keyup', function(e) {
                if (e.key === "Escape" && modal.classList.contains('visible')) closeModal();
            });
        });
    </script>


</div>

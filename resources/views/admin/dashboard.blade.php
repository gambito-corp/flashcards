<x-main-layout title="Administración">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <div class="container mx-auto p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

            <!-- Usuarios nuevos -->
            <div class="bg-white rounded-lg shadow p-4">
                <livewire:admin.chart.new-users-chart />
            </div>

            <!-- Usuarios Premium nuevos -->
            <div class="bg-white rounded-lg shadow p-4">
                <livewire:admin.chart.new-premium-users-chart />
            </div>

            <!-- Conversión Premium -->
            <div class="bg-white rounded-lg shadow p-4">
{{--                <livewire:admin.premium-conversion-rate />--}}
            </div>

            <!-- Exámenes por usuario -->
            <div class="bg-white rounded-lg shadow p-4">
{{--                <livewire:admin.exams-per-user-chart />--}}
            </div>

            <!-- Flashcards por media -->
            <div class="bg-white rounded-lg shadow p-4">
{{--                <livewire:admin.flashcards-media-chart />--}}
            </div>

            <!-- Preguntas por modelo -->
            <div class="bg-white rounded-lg shadow p-4">
{{--                <livewire:admin.questions-per-model-chart />--}}
            </div>

            <!-- Logins -->
            <div class="bg-white rounded-lg shadow p-4">
{{--                <livewire:admin.user-logins-chart />--}}
            </div>
        </div>
    </div>
</x-main-layout>

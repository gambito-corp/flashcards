<div x-data="{ activeTab: 'areas' }" class="container mx-auto p-4 bg-white shadow rounded-lg">
    <!-- Selector de Áreas -->
    <livewire:exams.question-selector
        :options="$areas"
        label="Área"
        event="area-selected"
        tab-id="areas"
    />

    <!-- Selector de Categoría -->
    <div x-show="activeTab === 'categories'" x-cloak>
        <livewire:exams.question-selector
            :options="[]"
            label="Categoría"
            event="category-selected"
            tab-id="categories"
        />
    </div>

    <!-- Selector de Tipo -->
    <div x-show="activeTab === 'tipos'" x-cloak>
        <livewire:exams.question-selector
            :options="[]"
            label="Tipo de Pregunta"
            event="tipo-selected"
            tab-id="tipos"
        />
    </div>

    <!-- Resto del formulario... -->
</div>

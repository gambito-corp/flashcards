<?php

namespace App\Http\Requests\Api\MedChat;

use Illuminate\Foundation\Http\FormRequest;

class MedChatAskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // ✅ CAMPOS PRINCIPALES
            'question' => 'required|string|min:5|max:2000',
            'conversation_id' => 'nullable|integer|exists:medisearch_chats,id',
            'chat_history' => 'nullable|array',
            'search_type' => 'nullable|string|in:standard,deep_research,simple',

            // ✅ FILTROS GENERALES
            'filters' => 'nullable|array',
            'filters.year_from' => 'nullable|integer|min:1800|max:2025',
            'filters.year_to' => 'nullable|integer|min:1800|max:2025',
            'filters.language' => 'nullable|string|in:english,spanish,french,german,portuguese',

            // ✅ NUEVO PARADIGMA: document_type (string o array)
            'filters.document_type' => 'nullable|string|in:journal_articles,books,guidelines,news_commentary,educational,conferences,technical_legal',

            // ✅ SUBFILTROS DINÁMICOS
            'filters.subfiltros' => 'nullable|array',
            'filters.subfiltros.*' => 'string',

            // ✅ FILTROS DE CONTENIDO
            'filters.free_full_text' => 'nullable|boolean',
            'filters.has_abstract' => 'nullable|boolean',
            'filters.pmc_articles' => 'nullable|boolean',

            // ✅ FILTROS DE POBLACIÓN
            'filters.species' => 'nullable|string|in:humans,animals,mice,rats',
            'filters.sex' => 'nullable|string|in:male,female',
            'filters.age_groups' => 'nullable|array',
            'filters.age_groups.*' => 'string|in:infant,child,adolescent,adult,middle_aged,aged',

            // ✅ FILTROS DE FECHA ALTERNATIVOS
            'filters.date_range' => 'nullable|string|in:last_year,last_5_years,last_10_years',

            // ✅ COMPATIBILIDAD LEGACY
            'filters.article_types' => 'nullable|array',
            'filters.article_types.*' => 'string|in:meta_analyses,systematic_reviews,reviews,clinical_trials,randomized_controlled_trials,observational_studies,case_reports,practice_guidelines,guidelines'
        ];
    }

    /**
     * ✅ VALIDACIÓN ADICIONAL PERSONALIZADA
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateDateRange($validator);
            $this->validateSubfiltros($validator);
            $this->validateDocumentTypeArray($validator);
        });
    }

    /**
     * ✅ PREPARAR DATOS ANTES DE VALIDACIÓN
     */
    protected function prepareForValidation(): void
    {
        // Convertir document_type a array si es string
        if ($this->has('filters.document_type') && is_string($this->input('filters.document_type'))) {
            $this->merge([
                'filters' => array_merge($this->input('filters', []), [
                    'document_type' => [$this->input('filters.document_type')]
                ])
            ]);
        }

        // Limpiar subfiltros vacíos
        if ($this->has('filters.subfiltros')) {
            $subfiltros = array_filter($this->input('filters.subfiltros', []), function ($item) {
                return !empty(trim($item));
            });

            $this->merge([
                'filters' => array_merge($this->input('filters', []), [
                    'subfiltros' => array_values($subfiltros)
                ])
            ]);
        }
    }

    /**
     * ✅ VALIDAR RANGO DE FECHAS
     */
    private function validateDateRange($validator)
    {
        $yearFrom = $this->input('filters.year_from');
        $yearTo = $this->input('filters.year_to');

        if ($yearFrom && $yearTo && $yearFrom > $yearTo) {
            $validator->errors()->add(
                'filters.year_from',
                'El año inicial no puede ser mayor que el año final.'
            );
        }

        if ($yearTo && $yearTo > date('Y')) {
            $validator->errors()->add(
                'filters.year_to',
                'El año final no puede ser mayor que el año actual.'
            );
        }
    }

    /**
     * ✅ VALIDAR SUBFILTROS SEGÚN DOCUMENT_TYPE
     */
    private function validateSubfiltros($validator)
    {
        $documentTypes = $this->input('filters.document_type', []);
        $subfiltros = $this->input('filters.subfiltros', []);

        if (empty($subfiltros)) {
            return; // No hay subfiltros que validar
        }

        // Convertir a array si es string
        if (is_string($documentTypes)) {
            $documentTypes = [$documentTypes];
        }

        // Mapeo de subfiltros válidos por tipo de documento
        $validSubfiltros = [
            'journal_articles' => [
                'meta_analyses', 'systematic_reviews', 'reviews', 'clinical_trials',
                'randomized_controlled_trials', 'controlled_clinical_trials',
                'observational_studies', 'cohort_studies', 'case_control_studies',
                'cross_sectional_studies', 'case_reports', 'comparative_studies',
                'multicenter_studies'
            ],
            'books' => [
                'handbooks', 'textbooks', 'atlases', 'dictionaries',
                'encyclopedias', 'formularies', 'pharmacopoeia', 'monographs'
            ],
            'guidelines' => [
                'practice_guidelines', 'consensus_conferences', 'nih_consensus',
                'government_publications'
            ],
            'news_commentary' => [
                'editorials', 'letters', 'comments', 'news', 'newspaper_articles'
            ],
            'educational' => [
                'patient_education', 'lectures', 'interactive_tutorials',
                'instructional_videos', 'study_guides'
            ],
            'conferences' => [
                'meeting_abstracts', 'congresses', 'addresses'
            ],
            'technical_legal' => [
                'technical_reports', 'legal_cases', 'legislation', 'patents'
            ]
        ];

        // Obtener todos los subfiltros válidos para los tipos seleccionados
        $allValidSubfiltros = [];
        foreach ($documentTypes as $docType) {
            if (isset($validSubfiltros[$docType])) {
                $allValidSubfiltros = array_merge($allValidSubfiltros, $validSubfiltros[$docType]);
            }
        }

        // Validar cada subfiltro
        foreach ($subfiltros as $index => $subfiltro) {
            if (!in_array($subfiltro, $allValidSubfiltros)) {
                $validator->errors()->add(
                    "filters.subfiltros.{$index}",
                    "El subfiltro '{$subfiltro}' no es válido para los tipos de documento seleccionados."
                );
            }
        }
    }

    /**
     * ✅ PERMITIR document_type como array
     */
    private function validateDocumentTypeArray($validator)
    {
        $documentType = $this->input('filters.document_type');

        if (is_array($documentType)) {
            $validTypes = ['journal_articles', 'books', 'guidelines', 'news_commentary', 'educational', 'conferences', 'technical_legal'];

            foreach ($documentType as $index => $type) {
                if (!in_array($type, $validTypes)) {
                    $validator->errors()->add(
                        "filters.document_type.{$index}",
                        "Tipo de documento no válido: {$type}"
                    );
                }
            }
        }
    }

    /**
     * ✅ MENSAJES DE ERROR PERSONALIZADOS
     */
    public function messages(): array
    {
        return [
            'question.required' => 'La pregunta es obligatoria.',
            'question.min' => 'La pregunta debe tener al menos 5 caracteres.',
            'question.max' => 'La pregunta no puede exceder 2000 caracteres.',
            'conversation_id.exists' => 'La conversación especificada no existe.',
            'search_type.in' => 'El tipo de búsqueda debe ser: standard, deep_research o simple.',

            'filters.document_type.in' => 'Tipo de documento no válido. Opciones: journal_articles, books, guidelines, news_commentary, educational, conferences, technical_legal.',
            'filters.language.in' => 'Idioma no válido. Opciones: english, spanish, french, german, portuguese.',
            'filters.species.in' => 'Especie no válida. Opciones: humans, animals, mice, rats.',
            'filters.sex.in' => 'Sexo no válido. Opciones: male, female.',
            'filters.age_groups.*.in' => 'Grupo de edad no válido. Opciones: infant, child, adolescent, adult, middle_aged, aged.',

            'filters.year_from.min' => 'El año inicial no puede ser menor a 1800.',
            'filters.year_from.max' => 'El año inicial no puede ser mayor a 2025.',
            'filters.year_to.min' => 'El año final no puede ser menor a 1800.',
            'filters.year_to.max' => 'El año final no puede ser mayor a 2025.',

            'filters.free_full_text.boolean' => 'El filtro de texto completo debe ser verdadero o falso.',
            'filters.has_abstract.boolean' => 'El filtro de abstract debe ser verdadero o falso.',
            'filters.pmc_articles.boolean' => 'El filtro de artículos PMC debe ser verdadero o falso.',
        ];
    }

    /**
     * ✅ ATRIBUTOS PERSONALIZADOS
     */
    public function attributes(): array
    {
        return [
            'question' => 'pregunta',
            'conversation_id' => 'ID de conversación',
            'search_type' => 'tipo de búsqueda',
            'filters.document_type' => 'tipo de documento',
            'filters.subfiltros' => 'subfiltros',
            'filters.year_from' => 'año inicial',
            'filters.year_to' => 'año final',
            'filters.language' => 'idioma',
            'filters.species' => 'especie',
            'filters.sex' => 'sexo',
            'filters.age_groups' => 'grupos de edad',
            'filters.free_full_text' => 'texto completo gratuito',
            'filters.has_abstract' => 'con resumen',
        ];
    }
}

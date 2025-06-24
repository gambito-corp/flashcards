<?php
// app/Http/Requests/Api/MedChat/MedChatAskRequest.php

namespace App\Http\Requests\Api\MedChat;

use Illuminate\Foundation\Http\FormRequest;

class MedChatAskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        // ✅ REGLAS BASE (SIEMPRE DISPONIBLES)
        $rules = [
            'question' => 'required|string|min:5|max:2000',
            'conversation_id' => 'nullable|integer|exists:medisearch_chats,id',
            'chat_history' => 'nullable|array',
            'search_type' => 'nullable|string|in:standard,deep_research,simple',

            // ✅ FILTROS GENERALES
            'filters' => 'nullable|array',
            'filters.year_from' => 'nullable|integer|min:1800|max:2025',
            'filters.year_to' => 'nullable|integer|min:1800|max:2025',
            'filters.sources' => 'nullable|array',
            'filters.sources.*' => 'string|in:scientific_articles,books,health_guidelines,drug_guides,validated_webs',
            'filters.language' => 'nullable|string|max:50',
        ];

        // ✅ OBTENER FUENTES SELECCIONADAS
        $sources = $this->input('filters.sources', []);

        // ✅ REGLAS CONDICIONALES SEGÚN FUENTES
        if (in_array('scientific_articles', $sources)) {
            $rules = array_merge($rules, $this->getScientificArticlesRules());
        }

        if (in_array('books', $sources)) {
            $rules = array_merge($rules, $this->getBooksRules());
        }

        if (in_array('health_guidelines', $sources)) {
            $rules = array_merge($rules, $this->getHealthGuidelinesRules());
        }

        if (in_array('drug_guides', $sources)) {
            $rules = array_merge($rules, $this->getDrugGuidesRules());
        }

        if (in_array('validated_webs', $sources)) {
            $rules = array_merge($rules, $this->getValidatedWebsRules());
        }

        return $rules;
    }

    /**
     * ✅ REGLAS PARA ARTÍCULOS CIENTÍFICOS
     */
    private function getScientificArticlesRules(): array
    {
        return [
            'filters.article_types' => 'nullable|array',
            'filters.article_types.*' => 'string|in:meta_analyses,systematic_reviews,reviews,clinical_trials,randomized_controlled_trials,observational_studies,case_reports,practice_guidelines,guidelines,editorials,letters,comments,news,others',
            'filters.free_full_text' => 'nullable|boolean',
            'filters.has_abstract' => 'nullable|boolean',
            'filters.has_structured_abstract' => 'nullable|boolean',
            'filters.has_associated_data' => 'nullable|boolean',
            'filters.species' => 'nullable|string|in:humans,animals,mice,rats',
            'filters.sex' => 'nullable|string|in:male,female',
            'filters.age_groups' => 'nullable|array',
            'filters.age_groups.*' => 'string|in:infant,child,adolescent,adult,middle_aged,aged',
            'filters.journal_subset' => 'nullable|string|in:core_clinical_journals,dental_journals,nursing_journals',
        ];
    }

    /**
     * ✅ REGLAS PARA LIBROS
     */
    private function getBooksRules(): array
    {
        return [
            'filters.book_types' => 'nullable|array',
            'filters.book_types.*' => 'string|in:textbooks,reference_books,clinical_handbooks,patient_guides',
            'filters.publisher' => 'nullable|string|max:100',
            'filters.edition' => 'nullable|string|max:50',
            'filters.isbn' => 'nullable|string|max:20',
        ];
    }

    /**
     * ✅ REGLAS PARA GUÍAS DE SALUD
     */
    private function getHealthGuidelinesRules(): array
    {
        return [
            'filters.guideline_organizations' => 'nullable|array',
            'filters.guideline_organizations.*' => 'string|in:who,cdc,nice,aemps,ema,fda,aha,esc,acp,asco,nccn',
            'filters.guideline_types' => 'nullable|array',
            'filters.guideline_types.*' => 'string|in:clinical_practice,treatment,prevention,diagnostic,screening',
            'filters.evidence_level' => 'nullable|string|in:high,moderate,low,very_low',
        ];
    }

    /**
     * ✅ REGLAS PARA GUÍAS DE MEDICAMENTOS
     */
    private function getDrugGuidesRules(): array
    {
        return [
            'filters.drug_types' => 'nullable|array',
            'filters.drug_types.*' => 'string|in:prescription,otc,biologics,vaccines,medical_devices',
            'filters.regulatory_status' => 'nullable|string|in:approved,investigational,withdrawn,discontinued',
            'filters.therapeutic_area' => 'nullable|string|max:100',
            'filters.route_of_administration' => 'nullable|array',
            'filters.route_of_administration.*' => 'string|in:oral,injectable,topical,inhalation,transdermal',
        ];
    }

    /**
     * ✅ REGLAS PARA WEBS VALIDADAS
     */
    private function getValidatedWebsRules(): array
    {
        return [
            'filters.web_sources' => 'nullable|array',
            'filters.web_sources.*' => 'string|in:medlineplus,mayoclinic,healthline,webmd,uptodate,cochrane,bmj_best_practice',
            'filters.content_types' => 'nullable|array',
            'filters.content_types.*' => 'string|in:patient_info,professional_info,drug_info,disease_info,symptom_checker',
            'filters.target_audience' => 'nullable|string|in:patients,healthcare_professionals,general_public',
        ];
    }

    /**
     * ✅ VALIDACIÓN ADICIONAL DESPUÉS DE LAS REGLAS BÁSICAS
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $this->validateFilterCompatibility($validator);
            $this->validateDateRange($validator);
            $this->validateSourcesNotEmpty($validator);
        });
    }

    /**
     * ✅ VALIDAR COMPATIBILIDAD DE FILTROS
     */
    private function validateFilterCompatibility($validator)
    {
        $filters = $this->input('filters', []);
        $sources = $filters['sources'] ?? [];

        // ✅ VERIFICAR QUE NO HAYA FILTROS DE ARTÍCULOS SIN SCIENTIFIC_ARTICLES
        if (!in_array('scientific_articles', $sources)) {
            $scientificFilters = [
                'article_types', 'free_full_text', 'has_abstract',
                'has_structured_abstract', 'has_associated_data',
                'species', 'sex', 'age_groups', 'journal_subset'
            ];

            foreach ($scientificFilters as $filter) {
                if (isset($filters[$filter])) {
                    $validator->errors()->add(
                        "filters.{$filter}",
                        "Este filtro solo está disponible cuando 'scientific_articles' está seleccionado en sources."
                    );
                }
            }
        }

        // ✅ VERIFICAR FILTROS DE LIBROS
        if (!in_array('books', $sources)) {
            $bookFilters = ['book_types', 'publisher', 'edition', 'isbn'];
            foreach ($bookFilters as $filter) {
                if (isset($filters[$filter])) {
                    $validator->errors()->add(
                        "filters.{$filter}",
                        "Este filtro solo está disponible cuando 'books' está seleccionado en sources."
                    );
                }
            }
        }

        // ✅ VERIFICAR FILTROS DE GUÍAS DE SALUD
        if (!in_array('health_guidelines', $sources)) {
            $guidelineFilters = ['guideline_organizations', 'guideline_types', 'evidence_level'];
            foreach ($guidelineFilters as $filter) {
                if (isset($filters[$filter])) {
                    $validator->errors()->add(
                        "filters.{$filter}",
                        "Este filtro solo está disponible cuando 'health_guidelines' está seleccionado en sources."
                    );
                }
            }
        }

        // ✅ VERIFICAR FILTROS DE MEDICAMENTOS
        if (!in_array('drug_guides', $sources)) {
            $drugFilters = ['drug_types', 'regulatory_status', 'therapeutic_area', 'route_of_administration'];
            foreach ($drugFilters as $filter) {
                if (isset($filters[$filter])) {
                    $validator->errors()->add(
                        "filters.{$filter}",
                        "Este filtro solo está disponible cuando 'drug_guides' está seleccionado en sources."
                    );
                }
            }
        }

        // ✅ VERIFICAR FILTROS DE WEBS VALIDADAS
        if (!in_array('validated_webs', $sources)) {
            $webFilters = ['web_sources', 'content_types', 'target_audience'];
            foreach ($webFilters as $filter) {
                if (isset($filters[$filter])) {
                    $validator->errors()->add(
                        "filters.{$filter}",
                        "Este filtro solo está disponible cuando 'validated_webs' está seleccionado en sources."
                    );
                }
            }
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
     * ✅ VALIDAR QUE AL MENOS UNA FUENTE ESTÉ SELECCIONADA
     */
    private function validateSourcesNotEmpty($validator)
    {
        $sources = $this->input('filters.sources', []);

        if (empty($sources)) {
            $validator->errors()->add(
                'filters.sources',
                'Debe seleccionar al menos una fuente de información.'
            );
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
            'filters.sources.*.in' => 'Fuente no válida. Las fuentes disponibles son: scientific_articles, books, health_guidelines, drug_guides, validated_webs.',
            'filters.year_from.min' => 'El año inicial no puede ser menor a 1800.',
            'filters.year_to.max' => 'El año final no puede ser mayor a 2025.',
            'filters.language.max' => 'El idioma no puede exceder 50 caracteres.',
        ];
    }

    /**
     * ✅ ATRIBUTOS PERSONALIZADOS PARA ERRORES
     */
    public function attributes(): array
    {
        return [
            'question' => 'pregunta',
            'conversation_id' => 'ID de conversación',
            'search_type' => 'tipo de búsqueda',
            'filters.year_from' => 'año inicial',
            'filters.year_to' => 'año final',
            'filters.sources' => 'fuentes',
            'filters.language' => 'idioma',
            'filters.article_types' => 'tipos de artículos',
            'filters.free_full_text' => 'texto completo gratuito',
            'filters.has_abstract' => 'con resumen',
        ];
    }
}

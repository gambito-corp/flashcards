<?php

declare(strict_types=1);

namespace App\Enums;

enum DocumentTypeEnum: string
{
    case JOURNAL_ARTICLES = 'journal_articles';
    case BOOKS = 'books';
    case GUIDELINES = 'guidelines';
    case EDUCATIONAL = 'educational';
    case NEWS_COMMENTARY = 'news_commentary';
    case CONFERENCES = 'conferences';
    case TECHNICAL_LEGAL = 'technical_legal';

    public function label(): string
    {
        return match ($this) {
            self::JOURNAL_ARTICLES => '"Journal Article"[Publication Type]',
            self::BOOKS => '"Books"[Publication Type]',
            self::GUIDELINES => '"Guideline"[Publication Type]',
            self::EDUCATIONAL => '',
            self::NEWS_COMMENTARY => '',
            self::CONFERENCES => '"Congresses"[Publication Type]',
            self::TECHNICAL_LEGAL => '',
        };
    }

    public function filter(): string
    {
        return match ($this) {
            self::JOURNAL_ARTICLES => '"Journal Article"[Publication Type]',
            self::BOOKS => '"Books"[Publication Type]',
            self::GUIDELINES => '"Guideline"[Publication Type]',
            self::EDUCATIONAL => 'Educational Materials',
            self::NEWS_COMMENTARY => 'News and Commentary',
            self::CONFERENCES => '"Congresses"[Publication Type]',
            self::TECHNICAL_LEGAL => 'Technical and Legal',
        };
    }

    public function subfiltros(): array
    {
        return match ($this) {
            self::JOURNAL_ARTICLES => [
                'meta_analyses' => [
                    'label' => 'Meta-análisis',
                    'filter' => '"Meta-Analysis"[Publication Type]',
                    'evidence_level' => 'highest'
                ],
                'systematic_reviews' => [
                    'label' => 'Revisiones Sistemáticas',
                    'filter' => '"Systematic Review"[Publication Type]',
                    'evidence_level' => 'highest'
                ],
                'randomized_controlled_trials' => [
                    'label' => 'Ensayos Controlados Aleatorizados',
                    'filter' => '"Randomized Controlled Trial"[Publication Type]',
                    'evidence_level' => 'high'
                ],
                'controlled_clinical_trials' => [
                    'label' => 'Ensayos Clínicos Controlados',
                    'filter' => '"Controlled Clinical Trial"[Publication Type]',
                    'evidence_level' => 'high'
                ],
                'clinical_trials' => [
                    'label' => 'Ensayos Clínicos',
                    'filter' => '"Clinical Trial"[Publication Type]',
                    'evidence_level' => 'medium'
                ],
                'observational_studies' => [
                    'label' => 'Estudios Observacionales',
                    'filter' => '"Observational Study"[Publication Type]',
                    'evidence_level' => 'medium'
                ],
                'cohort_studies' => [
                    'label' => 'Estudios de Cohorte',
                    'filter' => '"Cohort Studies"[Publication Type]',
                    'evidence_level' => 'medium'
                ],
                'case_control_studies' => [
                    'label' => 'Estudios Caso-Control',
                    'filter' => '"Case-Control Studies"[Publication Type]',
                    'evidence_level' => 'medium'
                ],
                'cross_sectional_studies' => [
                    'label' => 'Estudios Transversales',
                    'filter' => '"Cross-Sectional Studies"[Publication Type]',
                    'evidence_level' => 'medium'
                ],
                'case_reports' => [
                    'label' => 'Reportes de Casos',
                    'filter' => '"Case Reports"[Publication Type]',
                    'evidence_level' => 'low'
                ],
                'reviews' => [
                    'label' => 'Revisiones',
                    'filter' => '"Review"[Publication Type]',
                    'evidence_level' => 'medium'
                ],
                'comparative_studies' => [
                    'label' => 'Estudios Comparativos',
                    'filter' => '"Comparative Study"[Publication Type]',
                    'evidence_level' => 'medium'
                ],
                'multicenter_studies' => [
                    'label' => 'Estudios Multicéntricos',
                    'filter' => '"Multicenter Study"[Publication Type]',
                    'evidence_level' => 'high'
                ]
            ],

            self::BOOKS => [
                'handbooks' => [
                    'label' => 'Manuales',
                    'filter' => '"Handbooks"[Publication Type]'
                ],
                'textbooks' => [
                    'label' => 'Libros de Texto',
                    'filter' => '"Textbooks"[Publication Type]'
                ],
                'atlases' => [
                    'label' => 'Atlas',
                    'filter' => '"Atlases"[Publication Type]'
                ],
                'dictionaries' => [
                    'label' => 'Diccionarios',
                    'filter' => '"Dictionary"[Publication Type]'
                ],
                'encyclopedias' => [
                    'label' => 'Enciclopedias',
                    'filter' => '"Encyclopedias"[Publication Type]'
                ],
                'formularies' => [
                    'label' => 'Formularios',
                    'filter' => '"Formularies"[Publication Type]'
                ],
                'pharmacopoeia' => [
                    'label' => 'Farmacopea',
                    'filter' => '"Pharmacopoeia"[Publication Type]'
                ],
                'monographs' => [
                    'label' => 'Monografías',
                    'filter' => '"Monograph"[Publication Type]'
                ]
            ],

            self::GUIDELINES => [
                'practice_guidelines' => [
                    'label' => 'Guías de Práctica Clínica',
                    'filter' => '"Practice Guideline"[Publication Type]',
                    'evidence_level' => 'high'
                ],
                'consensus_conferences' => [
                    'label' => 'Conferencias de Consenso',
                    'filter' => '"Consensus Development Conference"[Publication Type]',
                    'evidence_level' => 'high'
                ],
                'nih_consensus' => [
                    'label' => 'Consenso NIH',
                    'filter' => '"Consensus Development Conference, NIH"[Publication Type]',
                    'evidence_level' => 'high'
                ],
                'government_publications' => [
                    'label' => 'Publicaciones Gubernamentales',
                    'filter' => '"Government Publications"[Publication Type]'
                ]
            ],

            self::EDUCATIONAL => [
                'patient_education' => [
                    'label' => 'Educación del Paciente',
                    'filter' => '"Patient Education Handout"[Publication Type]'
                ],
                'lectures' => [
                    'label' => 'Conferencias',
                    'filter' => '"Lectures"[Publication Type]'
                ],
                'interactive_tutorials' => [
                    'label' => 'Tutoriales Interactivos',
                    'filter' => '"Interactive Tutorial"[Publication Type]'
                ],
                'instructional_videos' => [
                    'label' => 'Videos Instructivos',
                    'filter' => '"Instructional Films and Videos"[Publication Type]'
                ],
                'study_guides' => [
                    'label' => 'Guías de Estudio',
                    'filter' => '"Study Guide"[Publication Type]'
                ]
            ],

            self::NEWS_COMMENTARY => [
                'editorials' => [
                    'label' => 'Editoriales',
                    'filter' => '"Editorial"[Publication Type]'
                ],
                'letters' => [
                    'label' => 'Cartas',
                    'filter' => '"Letter"[Publication Type]'
                ],
                'comments' => [
                    'label' => 'Comentarios',
                    'filter' => '"Comment"[Publication Type]'
                ],
                'news' => [
                    'label' => 'Noticias',
                    'filter' => '"News"[Publication Type]'
                ],
                'newspaper_articles' => [
                    'label' => 'Artículos de Periódico',
                    'filter' => '"Newspaper Article"[Publication Type]'
                ]
            ],

            self::CONFERENCES => [
                'meeting_abstracts' => [
                    'label' => 'Resúmenes de Reuniones',
                    'filter' => '"Meeting Abstracts"[Publication Type]'
                ],
                'congresses' => [
                    'label' => 'Congresos',
                    'filter' => '"Congresses"[Publication Type]'
                ],
                'addresses' => [
                    'label' => 'Discursos',
                    'filter' => '"Addresses"[Publication Type]'
                ]
            ],

            self::TECHNICAL_LEGAL => [
                'technical_reports' => [
                    'label' => 'Reportes Técnicos',
                    'filter' => '"Technical Report"[Publication Type]'
                ],
                'legal_cases' => [
                    'label' => 'Casos Legales',
                    'filter' => '"Legal Cases"[Publication Type]'
                ],
                'legislation' => [
                    'label' => 'Legislación',
                    'filter' => '"Legislation"[Publication Type]'
                ],
                'patents' => [
                    'label' => 'Patentes',
                    'filter' => '"Patents"[Publication Type]'
                ]
            ]
        };
    }

    public function getSubfilterOptions(): array
    {
        return array_keys($this->subfiltros());
    }

    public function getSubfilterLabel(string $subfiltro): ?string
    {
        $subfiltros = $this->subfiltros();
        return $subfiltros[$subfiltro]['label'] ?? null;
    }

    public function getSubfilterFilter(string $subfiltro): ?string
    {
        $subfiltros = $this->subfiltros();
        return $subfiltros[$subfiltro]['filter'] ?? null;
    }

    public function getEvidenceLevel(string $subfiltro): ?string
    {
        $subfiltros = $this->subfiltros();
        return $subfiltros[$subfiltro]['evidence_level'] ?? null;
    }

    public function getHighestEvidenceSubfilters(): array
    {
        return array_filter(
            $this->getSubfilterOptions(),
            fn($subfiltro) => $this->getEvidenceLevel($subfiltro) === 'highest'
        );
    }

    public function getHighEvidenceSubfilters(): array
    {
        return array_filter(
            $this->getSubfilterOptions(),
            fn($subfiltro) => in_array($this->getEvidenceLevel($subfiltro), ['highest', 'high'])
        );
    }

    public static function getAllLabels(): array
    {
        return array_map(fn($case) => $case->label(), self::cases());
    }

    public static function getAllFilters(): array
    {
        return array_map(fn($case) => $case->filter(), self::cases());
    }

    public static function fromValue(string $value): ?self
    {
        return match ($value) {
            'journal_articles' => self::JOURNAL_ARTICLES,
            'books' => self::BOOKS,
            'guidelines' => self::GUIDELINES,
            'educational' => self::EDUCATIONAL,
            'news_commentary' => self::NEWS_COMMENTARY,
            'conferences' => self::CONFERENCES,
            'technical_legal' => self::TECHNICAL_LEGAL,
            default => null
        };
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label(),
            'filter' => $this->filter(),
            'subfiltros' => $this->subfiltros()
        ];
    }
}

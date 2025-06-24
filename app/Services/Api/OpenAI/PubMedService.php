<?php

namespace App\Services\Api\OpenAI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PubMedService
{
    protected $eutilsBaseUrl = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/';
    protected $biocBaseUrl = 'https://www.ncbi.nlm.nih.gov/research/bionlp/RESTful/pmcoa.cgi/';

    public function searchArticles(string $query, ?array $filters = null, $searchType): array
    {
        try {
            // ✅ PASO 1: BUSCAR PMIDs CON E-UTILITIES
            $pmids = $this->searchPMIDs($query, $filters, $searchType);
            if (empty($pmids)) {
                Log::info('📭 No se encontraron PMIDs para la consulta');
                return [];
            }

            // ✅ PASO 2: OBTENER ARTÍCULOS COMPLETOS CON BioC API
            $articles = $this->getArticlesFromBioC($pmids);
            Log::info("✅ Encontrados " . count($articles) . " artículos completos");
            return $articles;

        } catch (\Exception $e) {
            Log::error('💥 Error en PubMedService:', [
                'message' => $e->getMessage(),
                'query' => $query,
                'filters' => $filters
            ]);
            return [];
        }
    }

//    private function searchPMIDs(string $query, ?array $filters, $searchType): array
//    {
//        $searchQuery = $this->buildQuery($query, $filters);
//        switch ($searchType) {
//            case 'standard':
//                $maxArticles = 10;
//                break;
//            case 'deep_research':
//                $maxArticles = 100;
//                break;
//            default:
//                $maxArticles = 3; // Valor por defecto
//        }
//        $response = Http::timeout(30)->get($this->eutilsBaseUrl . 'esearch.fcgi', [
//            'db' => 'pubmed',
//            'term' => $searchQuery,
//            'retmax' => $maxArticles,
//            'retmode' => 'json'
//        ]);
//
//        if (!$response->successful()) {
//            return [];
//        }
//
//        $data = $response->json();
//        return $data['esearchresult']['idlist'] ?? [];
//    }
    private function searchPMIDs(string $query, ?array $filters, $searchType): array
    {
        $searchQuery = $this->buildQuery($query, $filters);

        switch ($searchType) {
            case 'standard':
                $maxArticles = 10;
                break;
            case 'deep_research':
                $maxArticles = 100;
                break;
            default:
                $maxArticles = 3;
        }

        Log::info('🔍 Búsqueda PubMed:', [
            'query' => $query,
            'filters' => $filters,
            'final_query' => $searchQuery,
            'max_articles' => $maxArticles
        ]);

        // ✅ PARÁMETROS ADICIONALES QUE SÍ PUEDES AGREGAR
        $response = Http::timeout(30)->get($this->eutilsBaseUrl . 'esearch.fcgi', [
            'db' => 'pubmed',
            'term' => $searchQuery,              // ✅ Aquí van TODOS los filtros
            'retmax' => $maxArticles,
            'retmode' => 'json',
            'sort' => 'relevance',               // ✅ Ordenar por relevancia
            'tool' => 'medchat_app',             // ✅ Identificar tu aplicación
            'email' => 'contact@medchat.com',    // ✅ Email de contacto (requerido)
            'usehistory' => 'n',                 // ✅ No usar historial
            'retstart' => 0,                     // ✅ Empezar desde el primer resultado
        ]);

        if (!$response->successful()) {
            Log::error('❌ Error en búsqueda PubMed:', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);
            return [];
        }

        $data = $response->json();
        $pmids = $data['esearchresult']['idlist'] ?? [];

        Log::info('📋 PMIDs encontrados:', [
            'count' => count($pmids),
            'pmids' => array_slice($pmids, 0, 5) // Solo mostrar primeros 5 en log
        ]);
        return $pmids;
    }


    /**
     * ✅ CONSTRUIR QUERY CON TODOS LOS FILTROS DISPONIBLES
     */
    public function buildQuery(string $query, ?array $filters = null): string
    {
        $searchTerms = [$query];

        if ($filters) {
            if ($date = $this->filterByDate($filters)) {
                $searchTerms[] = $date;
            }
            if ($types = $this->filterByArticleTypes($filters)) {
                $searchTerms[] = $types;
            }
            if ($lang = $this->filterByLanguage($filters)) {
                $searchTerms[] = $lang;
            }
            if ($text = $this->filterByTextAvailability($filters)) {
                $searchTerms[] = $text;
            }
            if ($species = $this->filterBySpecies($filters)) {
                $searchTerms[] = $species;
            }
            if ($sex = $this->filterBySex($filters)) {
                $searchTerms[] = $sex;
            }
            if ($ages = $this->filterByAgeGroups($filters)) {
                $searchTerms[] = $ages;
            }
            if ($journal = $this->filterByJournalSubset($filters)) {
                $searchTerms[] = $journal;
            }
            if ($data = $this->filterByAssociatedData($filters)) {
                $searchTerms[] = $data;
            }
        }

        return implode(' AND ', array_filter($searchTerms));
    }

    private function filterByDate(array $filters): ?string
    {
        if (isset($filters['year_from'], $filters['year_to'])) {
            return "({$filters['year_from']}[PDAT]:{$filters['year_to']}[PDAT])";
        }
        return null;
    }

    private function filterByArticleTypes(array $filters): ?string
    {
        if (empty($filters['article_types']) || !is_array($filters['article_types'])) return null;
        $map = [
            'meta_analyses' => '"Meta-Analysis"[PT]',
            'systematic_reviews' => '"Systematic Review"[PT]',
            'reviews' => '"Review"[PT]',
            'clinical_trials' => '"Clinical Trial"[PT]',
            'randomized_controlled_trials' => '"Randomized Controlled Trial"[PT]',
            'observational_studies' => '"Observational Study"[PT]',
            'case_reports' => '"Case Reports"[PT]',
            'practice_guidelines' => '"Practice Guideline"[PT]',
            'guidelines' => '"Guideline"[PT]',
            'editorials' => '"Editorial"[PT]',
            'letters' => '"Letter"[PT]',
            'news' => '"News"[PT]',
            'others' => '', // Puedes mapear a un tipo general o dejar vacío
        ];
        $selected = array_filter(array_map(fn($type) => $map[$type] ?? '', $filters['article_types']));
        return $selected ? '(' . implode(' OR ', $selected) . ')' : null;
    }

    private function filterByLanguage(array $filters): ?string
    {
        if (empty($filters['language'])) return null;
        $lang = strtolower($filters['language']);
        $map = [
            'english' => 'english[LA]',
            'spanish' => 'spanish[LA]',
            'french' => 'french[LA]',
            'german' => 'german[LA]',
            'portuguese' => 'portuguese[LA]',
            'italian' => 'italian[LA]',
            'japanese' => 'japanese[LA]',
            'russian' => 'russian[LA]',
            'chinese' => 'chinese[LA]',
            'dutch' => 'dutch[LA]',
            'norwegian' => 'norwegian[LA]',
            'swedish' => 'swedish[LA]',
            'danish' => 'danish[LA]',
            'korean' => 'korean[LA]',
            'polish' => 'polish[LA]',
        ];
        return $map[$lang] ?? null;
    }

    private function filterByTextAvailability(array $filters): ?string
    {
        $terms = [];
        if (!empty($filters['free_full_text'])) $terms[] = '"free full text"[SB]';
        if (!empty($filters['has_abstract'])) $terms[] = 'hasabstract';
        if (!empty($filters['has_structured_abstract'])) $terms[] = 'hasstructuredabstract';
        if (!empty($filters['has_associated_data'])) $terms[] = 'hasassociateddata';
        return $terms ? implode(' AND ', $terms) : null;
    }

    private function filterBySpecies(array $filters): ?string
    {
        if (empty($filters['species'])) return null;
        $map = [
            'humans' => '"Humans"[MeSH Terms]',
            'animals' => '"Animals"[MeSH Terms]',
            'mice' => '"Mice"[MeSH Terms]',
            'rats' => '"Rats"[MeSH Terms]',
        ];
        return $map[strtolower($filters['species'])] ?? null;
    }

    private function filterBySex(array $filters): ?string
    {
        if (empty($filters['sex'])) return null;
        $map = [
            'male' => '"Male"[MeSH Terms]',
            'female' => '"Female"[MeSH Terms]',
        ];
        return $map[strtolower($filters['sex'])] ?? null;
    }

    private function filterByAgeGroups(array $filters): ?string
    {
        if (empty($filters['age_groups']) || !is_array($filters['age_groups'])) return null;
        $map = [
            'infant' => '"Infant"[MeSH Terms]',
            'child' => '"Child"[MeSH Terms]',
            'adolescent' => '"Adolescent"[MeSH Terms]',
            'adult' => '"Adult"[MeSH Terms]',
            'middle_aged' => '"Middle Aged"[MeSH Terms]',
            'aged' => '"Aged"[MeSH Terms]',
        ];
        $selected = array_filter(array_map(fn($age) => $map[$age] ?? '', $filters['age_groups']));
        return $selected ? '(' . implode(' OR ', $selected) . ')' : null;
    }

    private function filterByJournalSubset(array $filters): ?string
    {
        if (empty($filters['journal_subset'])) return null;
        $map = [
            'core_clinical_journals' => '"Core Clinical Journals"[SB]',
            'dental_journals' => '"Dental Journals"[SB]',
            'nursing_journals' => '"Nursing Journals"[SB]',
        ];
        return $map[$filters['journal_subset']] ?? null;
    }

    private function filterByAssociatedData(array $filters): ?string
    {
        return !empty($filters['has_associated_data']) ? 'hasassociateddata' : null;
    }


    /**
     * ✅ OBTENER ARTÍCULOS COMPLETOS CON BioC API
     */
    private function getArticlesFromBioC(array $pmids): array
    {
        $articles = [];
        // ✅ PROCESAR EN LOTES DE 5 PARA EVITAR RATE LIMITING
        $batches = array_chunk($pmids, 5);
        foreach ($batches as $key => $batch) {
            foreach ($batch as $subKey => $pmid) {
                try {
                    // ✅ INTENTAR OBTENER ARTÍCULO COMPLETO DE PMC
                    $article = $this->getArticleFromPMC($pmid);
                    if ($article) {
                        $articles[] = $article;
                    } else {
                        // ✅ FALLBACK: USAR E-UTILITIES PARA METADATOS BÁSICOS
                        $fallbackArticle = $this->getBasicArticleInfo($pmid);

                        if ($fallbackArticle) {
                            $articles[] = $fallbackArticle;
                        }
                    }
                    // ✅ PAUSA PARA EVITAR RATE LIMITING
                    usleep(50000); // 50ms entre requests

                } catch (\Exception $e) {
                    Log::warning("Error obteniendo artículo PMID {$pmid}: " . $e->getMessage());
                    continue;
                }
            }
        }
        return $articles;
    }

    /**
     * ✅ OBTENER ARTÍCULO DE PMC (TEXTO COMPLETO)
     */
    private function getArticleFromPMC(string $pmid): ?array
    {
        try {
            // ✅ BUSCAR PMC ID DESDE PMID
            $pmcResponse = Http::timeout(15)->get($this->eutilsBaseUrl . 'elink.fcgi', [
                'dbfrom' => 'pubmed',
                'db' => 'pmc',
                'id' => $pmid,
                'retmode' => 'json'
            ]);

            if (!$pmcResponse->successful()) {
                return null;
            }

            $pmcData = $pmcResponse->json();
            $pmcIds = $pmcData['linksets'][0]['linksetdbs'][0]['links'] ?? [];

            if (empty($pmcIds)) {
                return null; // No hay artículo completo en PMC
            }

            $pmcId = $pmcIds[0];

            // ✅ OBTENER METADATOS BÁSICOS CON ESUMMARY
            $summaryResponse = Http::timeout(15)->get($this->eutilsBaseUrl . 'esummary.fcgi', [
                'db' => 'pubmed',
                'id' => $pmid,
                'retmode' => 'json'
            ]);

            // ✅ OBTENER ABSTRACT COMPLETO CON EFETCH
            $abstractResponse = Http::timeout(15)->get($this->eutilsBaseUrl . 'efetch.fcgi', [
                'db' => 'pubmed',
                'id' => $pmid,
                'retmode' => 'text',
                'rettype' => 'abstract'
            ]);

            if (!$summaryResponse->successful()) {
                return null;
            }

            $summary = $summaryResponse->json();
            $article = $summary['result'][$pmid] ?? null;

            if (!$article) {
                return null;
            }

            // ✅ EXTRAER ABSTRACT DEL TEXTO
            $fullAbstract = 'Sin resumen disponible';
            if ($abstractResponse->successful()) {
                $abstractText = $abstractResponse->body();
                $fullAbstract = $this->extractAbstractFromText($abstractText);
            }

            // ✅ GENERAR LINKS DE DESCARGA
            $downloadLinks = $this->generateDownloadLinks($pmid, $pmcId);

            return [
                'pmid' => $pmid,
                'title' => $article['title'] ?? 'Sin título',
                'authors' => $this->formatAuthors($article['authors'] ?? []),
                'journal' => $article['source'] ?? 'Sin revista',
                'year' => $article['pubdate'] ? substr($article['pubdate'], 0, 4) : 'Sin año',
                'abstract' => $fullAbstract, // ✅ ABSTRACT COMPLETO
                'url' => "https://pubmed.ncbi.nlm.nih.gov/{$pmid}/",
                'doi' => $article['elocationid'] ?? '',
                'pmcid' => "PMC{$pmcId}",
                'relevance_score' => 9,
                'has_full_text' => true,
                'fecha' => $article['pubdate'] ?? '',
                'fuente' => 'PMC',
                'pmc_url' => "https://www.ncbi.nlm.nih.gov/pmc/articles/PMC{$pmcId}/",
                'download_links' => $downloadLinks,
                // Campos de compatibilidad
                'tipo_estudio' => $this->determineStudyType($article),
                '_original_format' => 'pmc_enhanced'
            ];

        } catch (\Exception $e) {
            Log::debug("No se pudo obtener artículo completo para PMID {$pmid}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ FALLBACK: INFORMACIÓN BÁSICA CON ABSTRACT
     */
    private function getBasicArticleInfo(string $pmid): ?array
    {
        try {
            // ✅ OBTENER METADATOS CON ESUMMARY
            $summaryResponse = Http::timeout(15)->get($this->eutilsBaseUrl . 'esummary.fcgi', [
                'db' => 'pubmed',
                'id' => $pmid,
                'retmode' => 'json'
            ]);

            // ✅ OBTENER ABSTRACT CON EFETCH
            $abstractResponse = Http::timeout(15)->get($this->eutilsBaseUrl . 'efetch.fcgi', [
                'db' => 'pubmed',
                'id' => $pmid,
                'retmode' => 'text',
                'rettype' => 'abstract'
            ]);

            if (!$summaryResponse->successful()) {
                return null;
            }

            $summary = $summaryResponse->json();
            $article = $summary['result'][$pmid] ?? null;

            if (!$article) {
                return null;
            }

            // ✅ EXTRAER ABSTRACT
            $fullAbstract = 'Sin resumen disponible';
            if ($abstractResponse->successful()) {
                $abstractText = $abstractResponse->body();
                $fullAbstract = $this->extractAbstractFromText($abstractText);
            }

            return [
                'pmid' => $pmid,
                'title' => $article['title'] ?? 'Sin título',
                'authors' => $this->formatAuthors($article['authors'] ?? []),
                'journal' => $article['source'] ?? 'Sin revista',
                'year' => $article['pubdate'] ? substr($article['pubdate'], 0, 4) : 'Sin año',
                'abstract' => $fullAbstract, // ✅ ABSTRACT COMPLETO
                'url' => "https://pubmed.ncbi.nlm.nih.gov/{$pmid}/",
                'doi' => $article['elocationid'] ?? '',
                'relevance_score' => 7,
                'has_full_text' => false,

                // Campos de compatibilidad
                'fecha' => $article['pubdate'] ?? '',
                'fuente' => 'PubMed',
                'tipo_estudio' => $this->determineStudyType($article),
                '_original_format' => 'pubmed_enhanced'
            ];

        } catch (\Exception $e) {
            Log::debug("Error obteniendo info básica para PMID {$pmid}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * ✅ EXTRAER ABSTRACT DEL TEXTO PLANO
     */
    private function extractAbstractFromText(string $abstractText): string
    {
        if (empty(trim($abstractText))) {
            return 'Sin resumen disponible';
        }

        // ✅ LIMPIAR Y FORMATEAR EL TEXTO
        $lines = explode("\n", $abstractText);
        $cleanLines = [];
        $inAbstract = false;

        foreach ($lines as $line) {
            $line = trim($line);

            // Detectar inicio del abstract
            if (stripos($line, 'abstract') !== false || $inAbstract) {
                $inAbstract = true;

                // Saltar líneas vacías y títulos
                if (empty($line) || stripos($line, 'abstract') !== false) {
                    continue;
                }

                $cleanLines[] = $line;
            }
        }

        if (!empty($cleanLines)) {
            return implode(' ', $cleanLines);
        }

        // ✅ FALLBACK: USAR TODO EL TEXTO LIMPIO
        return trim(preg_replace('/\s+/', ' ', $abstractText));
    }

    /**
     * ✅ GENERAR LINKS DE DESCARGA
     */
    private function generateDownloadLinks(string $pmid, string $pmcId): array
    {
        return [
            'pmc_html' => [
                'url' => "https://www.ncbi.nlm.nih.gov/pmc/articles/PMC{$pmcId}/",
                'format' => 'HTML',
                'description' => 'Artículo completo en PMC'
            ],
            'pmc_pdf' => [
                'url' => "https://www.ncbi.nlm.nih.gov/pmc/articles/PMC{$pmcId}/pdf/",
                'format' => 'PDF',
                'description' => 'Descargar PDF desde PMC'
            ],
            'pubmed' => [
                'url' => "https://pubmed.ncbi.nlm.nih.gov/{$pmid}/",
                'format' => 'HTML',
                'description' => 'Ver en PubMed'
            ]
        ];
    }

    /**
     * ✅ CONSTRUIR QUERY CON FILTROS MEJORADOS
     */
    private function buildFilteredQuery(string $query, ?array $filters = null): string
    {
        $searchTerms = [$query];

        if ($filters) {
            // ✅ FILTRO POR FECHA
            if (isset($filters['year_from']) && isset($filters['year_to'])) {
                $yearFrom = $filters['year_from'];
                $yearTo = $filters['year_to'];
                $searchTerms[] = "({$yearFrom}[PDAT]:{$yearTo}[PDAT])";
            }

            // ✅ FILTRO POR TIPOS DE ARTÍCULOS
            if (isset($filters['article_types']) && is_array($filters['article_types'])) {
                $typeFilters = [];

                foreach ($filters['article_types'] as $type) {
                    switch ($type) {
                        case 'meta_analyses':
                            $typeFilters[] = '"Meta-Analysis"[Publication Type]';
                            break;
                        case 'systematic_reviews':
                            $typeFilters[] = '"Systematic Review"[Publication Type]';
                            break;
                        case 'reviews':
                            $typeFilters[] = '"Review"[Publication Type]';
                            break;
                        case 'clinical_trials':
                            $typeFilters[] = '"Randomized Controlled Trial"[Publication Type]';
                            break;
                        case 'observational_studies':
                            $typeFilters[] = '"Observational Study"[Publication Type]';
                            break;
                    }
                }

                if (!empty($typeFilters)) {
                    $searchTerms[] = '(' . implode(' OR ', $typeFilters) . ')';
                }
            }

            // ✅ FILTRO PARA ARTÍCULOS CON TEXTO COMPLETO EN PMC
            if (isset($filters['free_full_text']) && $filters['free_full_text']) {
                $searchTerms[] = '"PMC"[SB]';
            }
        }

        return implode(' AND ', $searchTerms);
    }

    // ✅ RESTO DE MÉTODOS IGUAL...
    private function formatAuthors(array $authors): string
    {
        if (empty($authors)) return 'Sin autores';

        $authorNames = array_map(function ($author) {
            return $author['name'] ?? '';
        }, array_slice($authors, 0, 3));

        return implode(', ', array_filter($authorNames));
    }

    private function determineStudyType(array $article): string
    {
        $title = strtolower($article['title'] ?? '');

        if (strpos($title, 'meta-analysis') !== false) {
            return 'Meta Analyses';
        }
        if (strpos($title, 'systematic review') !== false) {
            return 'Systematic Reviews';
        }
        if (strpos($title, 'review') !== false) {
            return 'Reviews';
        }
        if (strpos($title, 'clinical trial') !== false || strpos($title, 'randomized') !== false) {
            return 'Clinical Trials';
        }
        if (strpos($title, 'observational') !== false) {
            return 'Observational Studies';
        }

        return 'Otros';
    }
}

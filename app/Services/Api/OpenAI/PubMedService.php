<?php

namespace App\Services\Api\OpenAI;

use App\Enums\DocumentTypeEnum;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PubMedService
{
    protected $eutilsBaseUrl = 'https://eutils.ncbi.nlm.nih.gov/entrez/eutils/';
    protected $biocBaseUrl = 'https://www.ncbi.nlm.nih.gov/research/bionlp/RESTful/pmcoa.cgi/';

    /**
     * ✅ BUSCAR ARTÍCULOS EN PUBMED CON FILTROS Y TIPO DE BÚSQUEDA
     *
     * @param string $query
     * @param array|null $filters
     * @param string $searchType
     * @return array
     */
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
            $articles = $this->getArticlesFromBioC($pmids, $query);

            // ✅ PASO 3: Obtener el Abstract y otros metadatos básicos
            if (empty($articles)) {
                Log::info('📭 No se encontraron artículos completos para los PMIDs');
                return [];
            }
            // ✅ PASO 4: Formatear los artículos para la respuesta
            foreach ($articles as &$article) {
                // ✅ Asegurar que el campo 'tipo_estudio' esté presente
                if (!isset($article['tipo_estudio'])) {
                    $article['tipo_estudio'] = $this->determineStudyType($article);
                }
                // ✅ Asegurar que el campo 'relevance_score' esté presente
                if (!isset($article['relevance_score'])) {
                    $article['relevance_score'] = 10; // Valor por defecto
                }
            }
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

    /**
     * ✅ BUSCAR PMIDs EN PUBMED CON E-UTILITIES
     *
     * @param string $query
     * @param array|null $filters
     * @param string $searchType
     * @return array
     */
    private function searchPMIDs(string $query, ?array $filters, $searchType): array
    {
        // Validar que la query no esté vacía
        if (empty(trim($query))) {
            Log::error('Query vacía enviada a PubMed');
            return [];
        }

        $searchQuery = $this->buildQuery($query, $filters);

        // Validar que la query construida no esté vacía
        if (empty(trim($searchQuery))) {
            Log::error('Query construida está vacía', [
                'original_query' => $query,
                'filters' => $filters
            ]);
            return [];
        }

        $maxArticles = match ($searchType) {
            'standard' => 10,
            'deep_research' => 100,
            default => 3
        };

        // Respetar rate limiting ANTES de hacer la petición
        $this->respectRateLimit();

        Log::info('Enviando query a PubMed:', [
            'original' => $query,
            'built_query' => $searchQuery
        ]);

        try {
            $response = Http::timeout(30)
                ->retry(3, 1000)
                ->get($this->eutilsBaseUrl . 'esearch.fcgi', [
                    'db' => 'pubmed',
                    'term' => $searchQuery,
                    'retmax' => $maxArticles,
                    'retmode' => 'json',
                    'sort' => 'relevance',
                    'tool' => 'medchat_app',
                    'email' => 'contact@medchat.com',
                    'usehistory' => 'n',
                    'retstart' => 0,
                ]);

            if (!$response->successful()) {
                Log::error('Error HTTP en búsqueda PubMed:', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'query' => $searchQuery
                ]);

                if ($response->status() === 500) {
                    sleep(2);
                    return $this->retrySearch($searchQuery, $maxArticles);
                }

                return [];
            }

            $data = $response->json();

            if (isset($data['esearchresult']['ERROR'])) {
                Log::error('Error en respuesta de PubMed:', [
                    'error' => $data['esearchresult']['ERROR'],
                    'query' => $searchQuery
                ]);
                return [];
            }

            return $data['esearchresult']['idlist'] ?? [];

        } catch (\Exception $e) {
            Log::error('Excepción en búsqueda PubMed:', [
                'message' => $e->getMessage(),
                'query' => $searchQuery
            ]);
            return [];
        }
    }

    /**
     * ✅ CONSTRUIR QUERY CON TODOS LOS FILTROS DISPONIBLES
     */
    public function buildQuery(string $query, ?array $filters = null): string
    {
        $searchTerms = [$query];

        if ($filters) {
            // ✅ NUEVO: Filtros jerárquicos usando DocumentTypeEnum
            if ($documentTypeFilter = $this->buildDocumentTypeFilter($filters)) {
                $searchTerms[] = $documentTypeFilter;
            }

            // ✅ Filtros adicionales (mantener compatibilidad)
            if ($date = $this->filterByArticleTypes($filters)) {
                $searchTerms[] = $date;
            }
            if ($text = $this->filterByTextAvailability($filters)) {
                $searchTerms[] = $text;
            }
            if ($lang = $this->filterByLanguage($filters)) {
                $searchTerms[] = $lang;
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
        }

        $finalQuery = implode(' AND ', array_filter($searchTerms));

        Log::info('Query construida:', [
            'original' => $query,
            'filters' => $filters,
            'final_query' => $finalQuery
        ]);

        return $finalQuery;
    }

    /**
     * ✅ CONSTRUIR FILTRO DE TIPO DE DOCUMENTO
     */
    private function buildDocumentTypeFilter(array $filters): ?string
    {
        if (empty($filters['document_type'])) {
            return $this->filterByArticleTypesLegacy($filters);
        }

        // ✅ NUEVO: Soportar array de document_types
        $documentTypes = is_array($filters['document_type'])
            ? $filters['document_type']
            : [$filters['document_type']];

        $mainFilters = [];
        $allSubfilters = [];

        // ✅ Procesar cada tipo de documento
        foreach ($documentTypes as $docTypeValue) {
            $documentType = DocumentTypeEnum::fromValue($docTypeValue);
            if (!$documentType) {
                Log::warning('Tipo de documento no válido:', ['type' => $docTypeValue]);
                continue;
            }

            // Obtener filtro principal si existe
            $mainFilter = $documentType->filter();
            if (!empty(trim($mainFilter))) {
                $mainFilters[] = $mainFilter;
            }

            // Recopilar subfiltros disponibles para este tipo de documento
            if (!empty($filters['subfiltros']) && is_array($filters['subfiltros'])) {
                foreach ($filters['subfiltros'] as $subfiltro) {
                    $subfilterQuery = $documentType->getSubfilterFilter($subfiltro);
                    if ($subfilterQuery) {
                        $allSubfilters[] = $subfilterQuery;
                    }
                }
            }
        }

        // ✅ Construir query final
        $filterParts = [];

        // Combinar filtros principales con OR
        if (!empty($mainFilters)) {
            $mainFiltersCombined = count($mainFilters) > 1
                ? '(' . implode(' OR ', $mainFilters) . ')'
                : $mainFilters[0];
            $filterParts[] = $mainFiltersCombined;
        }

        // Combinar subfiltros con OR
        if (!empty($allSubfilters)) {
            $subfiltersCombined = count($allSubfilters) > 1
                ? '(' . implode(' OR ', array_unique($allSubfilters)) . ')'
                : $allSubfilters[0];
            $filterParts[] = $subfiltersCombined;
        }

        // ✅ DEBUG
        Log::info('🔧 Filtro múltiple construido:', [
            'document_types' => $documentTypes,
            'subfiltros' => $filters['subfiltros'] ?? [],
            'main_filters' => $mainFilters,
            'all_subfilters' => $allSubfilters,
            'final_result' => !empty($filterParts) ? implode(' AND ', $filterParts) : null
        ]);

        return !empty($filterParts) ? implode(' AND ', $filterParts) : null;
    }


    /**
     * ✅ FILTROS ADICIONALES
     */
    private function filterByDate(array $filters): ?string
    {
        // Filtro por rango de años
        if (isset($filters['year_from'], $filters['year_to'])) {
            return "({$filters['year_from']}[Publication Date]:{$filters['year_to']}[Publication Date])";
        }

        // Filtros predefinidos
        if (isset($filters['date_range'])) {
            $dateMap = [
                'last_year' => '("last 1 year"[Publication Date])',
                'last_5_years' => '("last 5 years"[Publication Date])',
                'last_10_years' => '("last 10 years"[Publication Date])'
            ];
            return $dateMap[$filters['date_range']] ?? null;
        }

        return null;
    }

    /**
     * ✅ FILTRO POR TIPO DE ARTÍCULO
     */
    private function filterByArticleTypes(array $filters): ?string
    {
        if (empty($filters['article_types']) || !is_array($filters['article_types'])) return null;

        $map = [
            'meta_analyses' => '"Meta-Analysis"[Publication Type]',
            'systematic_reviews' => '"Systematic Review"[Publication Type]',
            'reviews' => '"Review"[Publication Type]',
            'clinical_trials' => '"Clinical Trial"[Publication Type]',
            'randomized_controlled_trials' => '"Randomized Controlled Trial"[Publication Type]',
            'observational_studies' => '"Observational Study"[Publication Type]',
            'case_reports' => '"Case Reports"[Publication Type]',
            'practice_guidelines' => '"Practice Guideline"[Publication Type]',
            'guidelines' => '"Guideline"[Publication Type]'
        ];

        $selected = array_filter(array_map(fn($type) => $map[$type] ?? '', $filters['article_types']));
        return $selected ? '(' . implode(' OR ', $selected) . ')' : null;
    }

    /**
     * ✅ OBTENER SUBFILTROS DISPONIBLES PARA UN TIPO DE DOCUMENTO
     */
    public function getAvailableSubfilters(string $documentType): array
    {
        $docType = DocumentTypeEnum::fromValue($documentType);
        if (!$docType) {
            return [];
        }

        return $docType->getSubfilterOptions();
    }

    /**
     * ✅ OBTENER INFORMACIÓN DE TIPO DE DOCUMENTO
     */
    public function getDocumentTypeInfo(): array
    {
        $info = [];

        foreach (DocumentTypeEnum::cases() as $docType) {
            $info[$docType->value] = [
                'label' => $docType->label(),
                'filter' => $docType->filter(),
                'subfiltros' => array_map(function ($key) use ($docType) {
                    return [
                        'key' => $key,
                        'label' => $docType->getSubfilterLabel($key),
                        'evidence_level' => $docType->getEvidenceLevel($key)
                    ];
                }, $docType->getSubfilterOptions())
            ];
        }

        return $info;
    }

    /**
     * ✅ BUSCAR ARTÍCULOS POR NIVEL DE EVIDENCIA
     *
     * @param string $query
     * @param string $evidenceLevel
     * @param array|null $additionalFilters
     * @return array
     */
    public function searchByEvidenceLevel(string $query, string $evidenceLevel = 'high', ?array $additionalFilters = null): array
    {
        $filters = $additionalFilters ?? [];

        // Configurar filtros según nivel de evidencia
        switch ($evidenceLevel) {
            case 'highest':
                $filters['document_type'] = 'journal_articles';
                $filters['subfiltros'] = ['meta_analyses', 'systematic_reviews'];
                break;

            case 'high':
                $filters['document_type'] = 'journal_articles';
                $filters['subfiltros'] = [
                    'meta_analyses',
                    'systematic_reviews',
                    'randomized_controlled_trials',
                    'controlled_clinical_trials'
                ];
                break;

            case 'guidelines':
                $filters['document_type'] = 'guidelines';
                $filters['subfiltros'] = ['practice_guidelines', 'consensus_conferences'];
                break;

            default:
                $filters['document_type'] = 'journal_articles';
                break;
        }

        return $this->searchArticles($query, $filters, 'standard');
    }

    /**
     * ✅ FILTRO LEGACY POR TIPO DE ARTÍCULO (MANTENIMIENTO DE COMPATIBILIDAD)
     */
    private function filterByArticleTypesLegacy(array $filters): ?string
    {
        if (empty($filters['article_types']) || !is_array($filters['article_types'])) return null;

        $map = [
            'meta_analyses' => '"Meta-Analysis"[Publication Type]',
            'systematic_reviews' => '"Systematic Review"[Publication Type]',
            'reviews' => '"Review"[Publication Type]',
            'clinical_trials' => '"Clinical Trial"[Publication Type]',
            'randomized_controlled_trials' => '"Randomized Controlled Trial"[Publication Type]',
            'observational_studies' => '"Observational Study"[Publication Type]',
            'case_reports' => '"Case Reports"[Publication Type]',
            'practice_guidelines' => '"Practice Guideline"[Publication Type]',
            'guidelines' => '"Guideline"[Publication Type]'
        ];

        $selected = array_filter(array_map(fn($type) => $map[$type] ?? '', $filters['article_types']));
        return $selected ? '(' . implode(' OR ', $selected) . ')' : null;
    }

    /**
     * ✅ FILTROS ADICIONALES POR IDIOMA, TEXTO COMPLETO, ESPECIE Y SEXO
     */
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

    /**
     * ✅ FILTROS ADICIONALES POR DISPONIBILIDAD DE TEXTO COMPLETO, ABSTRACT Y PMC
     */
    private function filterByTextAvailability(array $filters): ?string
    {
        $terms = [];
        if (!empty($filters['free_full_text'])) $terms[] = '"free full text"[Filter]';
        if (!empty($filters['has_abstract'])) $terms[] = 'hasabstract[Filter]';
        if (!empty($filters['pmc_articles'])) $terms[] = '"pubmed pmc"[Filter]';

        return $terms ? implode(' AND ', $terms) : null;
    }

    /**
     * ✅ FILTROS ADICIONALES POR ESPECIE, SEXO Y GRUPOS DE EDAD
     */
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

    /**
     * ✅ FILTRO POR SEXO
     */
    private function filterBySex(array $filters): ?string
    {
        if (empty($filters['sex'])) return null;
        $map = [
            'male' => '"Male"[MeSH Terms]',
            'female' => '"Female"[MeSH Terms]',
        ];
        return $map[strtolower($filters['sex'])] ?? null;
    }

    /**
     * ✅ FILTRO POR GRUPOS DE EDAD
     */
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

    /**
     * ✅ OBTENER ARTÍCULOS COMPLETOS CON BioC API
     */
    private function getArticlesFromBioC(array $pmids, $originalQuery = ''): array
    {
        $articles = [];
        // ✅ PROCESAR EN LOTES DE 5 PARA EVITAR RATE LIMITING
        $batches = array_chunk($pmids, 5);

        $position = 0;
        foreach ($batches as $key => $batch) {
            foreach ($batch as $subKey => $pmid) {
                try {
                    // ✅ INTENTAR OBTENER ARTÍCULO COMPLETO DE PMC
                    $article = $this->getArticleFromPMC($pmid);
                    if ($article) {
                        $article['relevance_score'] = $this->calculateRelevanceScore(
                            $article,
                            $position,
                            $originalQuery
                        );

                        $articles[] = $article;
                        $position++; // Incrementar posición
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
                'tipo_estudio' => $article['pubtype'] ?? '',
                'authors' => $this->formatAuthors($article['authors'] ?? []),
                'journal' => $article['fulljournalname'] ?? 'Sin revista',
                'year' => $article['pubdate'] ? substr($article['pubdate'], 0, 4) : 'Sin año',
                'url' => "https://pubmed.ncbi.nlm.nih.gov/{$pmid}/",
                'pmc_url' => "https://www.ncbi.nlm.nih.gov/pmc/articles/PMC{$pmcId}/",
                'download_links' => $downloadLinks,
                'doi' => $article['elocationid'] ?? '',
                'pmcid' => "PMC{$pmcId}",
                'relevance_score' => '',
                'has_full_text' => true,
                'fecha' => $article['pubdate'] ?? '',
                'fuente' => $article['source'] ?? 'PubMed',
                '_original_format' => $article['doctype'] ?? 'pubmed_enhanced',
                'abstract' => $fullAbstract,
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

            dd($article, $fullAbstract);
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
                'tipo_estudio' => '',
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
     * ✅ RESTO DE MÉTODOS IGUAL ...
     * */
    private function formatAuthors(array $authors): string
    {
        if (empty($authors)) return 'Sin autores';

        $authorNames = array_map(function ($author) {
            return $author['name'] ?? '';
        }, array_slice($authors, 0, 3));

        return implode(', ', array_filter($authorNames));
    }

    /**
     * ✅ DETERMINAR TIPO DE ESTUDIO PARA CAMPO DE COMPATIBILIDAD
     */
    private function respectRateLimit(): void
    {
        static $lastRequest = 0;
        static $requestCount = 0;

        $now = microtime(true);
        $minInterval = 0.34;

        if ($lastRequest > 0) {
            $elapsed = $now - $lastRequest;
            if ($elapsed < $minInterval) {
                $sleepTime = ($minInterval - $elapsed) * 1000000;
                usleep((int)$sleepTime);
            }
        }

        $requestCount++;
        $lastRequest = microtime(true);

        if ($requestCount % 10 === 0) {
            sleep(1);
        }
    }

    /**
     * ✅ RETRY LOGIC PARA BUSQUEDA DE PMIDs
     */
    private function retrySearch(string $searchQuery, int $maxArticles): array
    {
        // Tu implementación actual...
        return [];
    }

    /**
     * ✅ CALCULAR RELEVANCE SCORE BASADO EN POSICIÓN Y CARACTERÍSTICAS
     */
    private function calculateRelevanceScore(array $article, int $position, string $originalQuery): float
    {
        $score = 0;

        // ✅ SCORE BASE: Posición en resultados (más alto = más relevante)
        // PubMed ya ordenó por relevancia, así que posición 0 = más relevante
        $positionScore = max(0, 100 - ($position * 2)); // Decrece 2 puntos por posición
        $score += $positionScore;

        // ✅ SCORE POR TIPO DE PUBLICACIÓN (basado en nivel de evidencia)
        $publicationTypes = $article['pubtype'] ?? [];
        foreach ($publicationTypes as $type) {
            switch ($type) {
                case 'Meta-Analysis':
                    $score += 25;
                    break;
                case 'Systematic Review':
                    $score += 20;
                    break;
                case 'Randomized Controlled Trial':
                    $score += 18;
                    break;
                case 'Clinical Trial':
                    $score += 15;
                    break;
                case 'Review':
                    $score += 12;
                    break;
                case 'Practice Guideline':
                    $score += 22;
                    break;
                case 'Guideline':
                    $score += 20;
                    break;
                case 'Observational Study':
                    $score += 10;
                    break;
                case 'Case Reports':
                    $score += 5;
                    break;
            }
        }

        // ✅ SCORE POR JOURNAL DE ALTO IMPACTO
        $journal = $article['source'] ?? '';
        $highImpactJournals = [
            'Lancet' => 25,
            'BMJ' => 20,
            'JAMA' => 20,
            'Ann Intern Med' => 18,
            'Diabetologia' => 15,
            'Circulation' => 18,
            'NEJM' => 25,
            'Nature' => 25,
            'Science' => 25
        ];

        foreach ($highImpactJournals as $journalName => $points) {
            if (stripos($journal, $journalName) !== false) {
                $score += $points;
                break;
            }
        }

        // ✅ SCORE POR RECENCIA (artículos más nuevos = más puntos)
        $pubYear = $this->extractYearFromDate($article['pubdate'] ?? '');
        $currentYear = date('Y');
        $yearDiff = $currentYear - $pubYear;

        if ($yearDiff <= 1) $score += 15;      // Último año
        elseif ($yearDiff <= 3) $score += 10; // Últimos 3 años
        elseif ($yearDiff <= 5) $score += 5;  // Últimos 5 años

        // ✅ SCORE POR CITACIONES (PMC Reference Count)
        $citations = (int)($article['pmcrefcount'] ?? 0);
        if ($citations > 100) $score += 15;
        elseif ($citations > 50) $score += 10;
        elseif ($citations > 20) $score += 8;
        elseif ($citations > 10) $score += 5;
        elseif ($citations > 5) $score += 3;

        // ✅ SCORE POR RELEVANCIA DEL TÍTULO
        $titleRelevance = $this->calculateTitleRelevance($article['title'] ?? '', $originalQuery);
        $score += $titleRelevance * 10;

        // ✅ SCORE POR DISPONIBILIDAD DE TEXTO COMPLETO
        if ($this->hasFullTextAvailable($article)) {
            $score += 5;
        }

        // ✅ SCORE POR ABSTRACT DISPONIBLE
        $attributes = $article['attributes'] ?? [];
        if (in_array('Has Abstract', $attributes)) {
            $score += 3;
        }

        return round($score, 2);
    }

    /**
     * ✅ CALCULAR RELEVANCIA DEL TÍTULO
     */
    private function calculateTitleRelevance(string $title, string $query): float
    {
        if (empty($title) || empty($query)) return 0;

        $queryWords = array_map('strtolower', preg_split('/\s+/', trim($query)));
        $titleWords = array_map('strtolower', preg_split('/\s+/', trim($title)));

        $matches = 0;
        $totalQueryWords = count($queryWords);

        foreach ($queryWords as $queryWord) {
            if (strlen($queryWord) < 3) continue; // Ignorar palabras muy cortas

            foreach ($titleWords as $titleWord) {
                if (stripos($titleWord, $queryWord) !== false) {
                    $matches++;
                    break;
                }
            }
        }

        return $totalQueryWords > 0 ? ($matches / $totalQueryWords) : 0;
    }

    /**
     * ✅ VERIFICAR SI TIENE TEXTO COMPLETO DISPONIBLE
     */
    private function hasFullTextAvailable(array $article): bool
    {
        $articleIds = $article['articleids'] ?? [];

        foreach ($articleIds as $id) {
            if (isset($id['idtype']) && in_array($id['idtype'], ['pmc', 'pmcid'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * ✅ EXTRAER AÑO DE FECHA DE PUBLICACIÓN
     */
    private function extractYearFromDate(string $date): int
    {
        if (preg_match('/(\d{4})/', $date, $matches)) {
            return (int)$matches[1];
        }

        return date('Y'); // Fallback al año actual
    }
}

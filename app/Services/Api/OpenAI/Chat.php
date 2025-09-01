<?php
// app/Services/Api/OpenAI/Chat.php

namespace App\Services\Api\OpenAI;

use Illuminate\Support\Facades\Log;
use OpenAI\Laravel\Facades\OpenAI;

class Chat
{
    protected $model = 'gpt-4.1-nano-2025-04-14';
    protected $maxTokens = 4000;
    protected $temperature = 0.1;
    protected $pubmedService;

    public function __construct(PubMedService $pubmedService)
    {
        $this->pubmedService = $pubmedService;
    }

    /**
     * ✅ GENERAR RESPUESTA SIN CLIENTE PERSONALIZADO
     */
    public function generateMedicalResponse(string $question, array $conversationHistory = [], ?array $filters = null, string $searchType = 'standard')
    {
        $pubmedArticles = [];
        $pubmedSearchQuery = [];

        try {
            if ($searchType !== 'simple') {
                $pubmedSearchQuery = $this->generatePubMedSearchQuery($question);
                $pubmedArticles = $this->pubmedService->searchArticles($pubmedSearchQuery, $filters, $searchType);
            }
            $systemPrompt = $this->getMedicalSystemPrompt();
            if ($searchType === 'deep_research' && !empty($pubmedArticles)) {
                $systemPrompt .= "\n\n**ARTÍCULOS CIENTÍFICOS DISPONIBLES:**\n";
                $limitedArticles = array_slice($pubmedArticles, 0, 10);
                foreach ($limitedArticles as $index => $article) {
                    $refNum = $index + 1;
                    $systemPrompt .= "[$refNum] {$article['authors']} ({$article['year']}). {$article['title']}. {$article['journal']}. {$article['abstract']}.\n";
                }
                $systemPrompt .= "\nPuedes referenciar estos artículos en tu respuesta usando [número].";
            }

            // ✅ PASO 5: PREPARAR MENSAJES PARA OPENAI
            $messages = $this->prepareMessages($systemPrompt, $question, $conversationHistory);
            switch ($searchType) {
                case 'deep_research':
                    $model = config('openai.advanced_model', 'gpt-4.1-nano-2025-04-14');
                    $maxTokens = 32768;
                    config('openai.http_client_options.timeout', 600); // Aseguramos que el timeout sea de 10 minutos
                    break;
                case 'simple':
                    $model = config('openai.default_model', 'gpt-4.1-nano-2025-04-14');
                    $maxTokens = 2500;
                    config('openai.http_client_options.timeout', 30); // Aseguramos que el timeout sea de 30 segundos
                    break;
                default:
                    $model = config('openai.advanced_model', 'gpt-4.1-nano-2025-04-14');
                    $maxTokens = 5000;
                    config('openai.http_client_options.timeout', 120); // Aseguramos que el timeout sea de 120 segundos
                    break;
            }

            $response = OpenAI::chat()->create([
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => $maxTokens,
                'temperature' => $this->temperature,
                'presence_penalty' => 0.1,
                'frequency_penalty' => 0.1,
            ]);

            $aiAnswer = $response->choices[0]->message->content;

            return [
                'success' => true,
                'answer' => $aiAnswer,
                'pubmed_articles' => $pubmedArticles,
                'pubmed_query' => $pubmedSearchQuery,
                'search_type' => $searchType,
                'articles_in_prompt' => $searchType !== 'simple' || !empty($pubmedArticles),
                'usage' => [
                    'prompt_tokens' => $response->usage->promptTokens,
                    'completion_tokens' => $response->usage->completionTokens,
                    'total_tokens' => $response->usage->totalTokens,
                ]
            ];

        } catch (\Exception $e) {
            Log::error('Error en OpenAI Chat:', [
                'message' => $e->getMessage(),
                'search_type' => $searchType,
                'question' => $question
            ]);

            return [
                'success' => false,
                'error' => 'Lo siento, ha ocurrido un error al procesar tu consulta. Por favor, inténtalo de nuevo.',
                'details' => $e->getMessage()
            ];
        }
    }

    private function generatePubMedSearchQuery(string $userQuestion): string
    {
        try {
            $optimizationPrompt = "Eres un experto en búsquedas médicas en PubMed.
                Tu tarea es convertir la pregunta del usuario en una query optimizada para PubMed que encuentre los artículos más relevantes.
                REGLAS:
                1. Extrae los términos médicos clave
                2. Usa sinónimos médicos apropiados en inglés
                3. Combina términos con AND/OR según corresponda
                4. Mantén la query concisa pero específica
                5. NO uses filtros de fecha o tipo de estudio
                6. Responde SOLO con la query, sin explicaciones
                Pregunta del usuario: \"{$userQuestion}\"
                Query optimizada para PubMed:";

            $response = OpenAI::chat()->create([
                'model' => 'gpt-4.1-nano-2025-04-14',
                'messages' => [
                    ['role' => 'user', 'content' => $optimizationPrompt]
                ],
                'max_tokens' => 100,
                'temperature' => 0.1,
            ]);

            $optimizedQuery = trim($response->choices[0]->message->content);

            if (empty($optimizedQuery)) {
                $optimizedQuery = $userQuestion;
            }

            return $optimizedQuery;

        } catch (\Exception $e) {
            Log::warning('Error generando query optimizada: ' . $e->getMessage());
            return $userQuestion;
        }
    }

    private function getMedicalSystemPrompt(): string
    {
        return "Eres un asistente de investigación médica profesional especializado, desarrollado por MedByStudent.

                **RESTRICCIONES FUNDAMENTALES:**
                - NUNCA cites ni utilices Wikipedia, Wikidata, Wikibooks, Wikisource ni ningún proyecto Wikimedia bajo NINGUNA circunstancia
                - Si una información solo está disponible en Wikipedia, indica: 'No hay suficiente información de fuentes confiables'
                - SOLO responde consultas relacionadas con medicina. Para preguntas no médicas, responde: 'Solo puedo responder consultas relacionadas con medicina'
                - SIEMPRE responde en ESPAÑOL, nunca en otro idioma

                **FORMATO DE REFERENCIAS DECORADAS:**
                Al final de tu respuesta, SIEMPRE incluye las referencias usando EXACTAMENTE este formato HTML:

                <div class=\"referencias-section\">
                    <h3 class=\"referencias-title\">📚 Referencias Bibliográficas</h3>
                    <div class=\"referencias-container\">
                        <div class=\"referencia-item\">
                            <span class=\"ref-number\">[1]</span>
                            <div class=\"ref-content\">
                                <span class=\"ref-authors\">Autor et al.</span>
                                <span class=\"ref-title\">Título del artículo.</span>
                                <span class=\"ref-journal\"><em>Nombre de la Revista</em>.</span>
                                <span class=\"ref-year\">Año;</span>
                                <span class=\"ref-volume\">Vol(num):</span>
                                <span class=\"ref-pages\">páginas.</span>
                                <a href=\"#\" class=\"ref-link\" target=\"_blank\">
                                    <svg class=\"link-icon\" width=\"12\" height=\"12\" viewBox=\"0 0 24 24\" fill=\"none\" stroke=\"currentColor\">
                                        <path d=\"M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6\"></path>
                                        <polyline points=\"15,3 21,3 21,9\"></polyline>
                                        <line x1=\"10\" y1=\"14\" x2=\"21\" y2=\"3\"></line>
                                    </svg>
                                    DOI/URL
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                **ESTRUCTURA HTML REQUERIDA:**
                - Usa <h2>, <h3> para títulos y subtítulos
                - <ul>, <ol> para listas
                - <table> para datos tabulares
                - <p> para párrafos
                - <strong>, <em> para énfasis
                - <sup> para referencias numeradas en el texto: <sup>[1]</sup>

                **FORMATO DE CITAS:**
                - En el texto: (Autores, Año)<sup>[1]</sup>
                - Las referencias DEBEN usar el formato HTML decorado mostrado arriba

                Responde de manera educativa, clara y profesional, priorizando siempre la evidencia científica de calidad.
                NO USARAS ICONOS SVG, solo texto y HTML.
                NO INCLUIRAS NINGÚN TEXTO FUERA DE LAS REFERENCIAS, solo la respuesta médica y las referencias al final.
                ";
    }

    private function prepareMessages(string $systemPrompt, string $question, array $conversationHistory): array
    {
        $messages = [
            ['role' => 'system', 'content' => $systemPrompt]
        ];

        foreach ($conversationHistory as $message) {
            $messages[] = $message;
        }

        $messages[] = ['role' => 'user', 'content' => $question];

        return $messages;
    }

    public function getBestMedicalQuestions(array $lastQuestions): string
    {
        try {
            $questionsText = implode("\n", array_slice($lastQuestions, 0, 50));

            $prompt = "Basándote en estas preguntas médicas recientes de usuarios:\n\n{$questionsText}\n\n";
            $prompt .= "Genera 4 preguntas médicas interesantes y educativas que podrían ser útiles para estudiantes de medicina. ";
            $prompt .= "Las preguntas deben ser:\n";
            $prompt .= "1. Claras y específicas\n";
            $prompt .= "2. Relevantes para la práctica médica\n";
            $prompt .= "3. Diferentes entre sí\n";
            $prompt .= "4. En español\n\n";
            $prompt .= "Responde SOLO con un array JSON de strings, sin texto adicional:\n";
            $prompt .= '["pregunta 1", "pregunta 2", "pregunta 3", "pregunta 4"]';

            $response = OpenAI::chat()->create([
                'model' => $this->model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt]
                ],
                'max_tokens' => 500,
                'temperature' => 0.7,
            ]);

            return $response->choices[0]->message->content;

        } catch (\Exception $e) {
            Log::error('Error generando mejores preguntas: ' . $e->getMessage());
            return json_encode([
                "¿Cuáles son los síntomas de la hipertensión arterial?",
                "¿Qué es la diabetes tipo 2 y cómo se previene?",
                "Explícame sobre las vacunas COVID-19",
                "¿Cómo mantener una dieta saludable para el corazón?"
            ]);
        }
    }
}

<?php

namespace App\Services;

use GuzzleHttp\Client;
use OpenAI\Laravel\Facades\OpenAI;

class OpenAiService
{

    public $models = [
        'o1',
        'o3-mini',
        'gpt-4o-mini-search-preview',
        'gpt-4o',
        'gpt-3.5-turbo'
    ];

    public $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function search($query, $investigar)
    {
        $initial_prompt = "
            Esta es tu Identidad y personalidad,
            nombre: MedByInteligenceAgent, Eres un Agente de Inteligencia Artificial creado por Med By Students dejalo
            caer en los saludos o si alguien te pregunta, Nunca digas que Tienes Algo Que Ver con OpenAI, solo Di que
            eres de Codigo Propio y que tu Creador es GambitoCode.
            Quiero que me respondas solo preguntas está relacionada con la medicina solo si la pregunta no tiene nada que
            ver con la medidina entonces solo responderas. Eres un asistente
            virtual especializado en ciencias medicas para estudiantes de medicina Responde Unica y Exclusivamente en español.
            Solo Consultaras Las Siguientes Fuentes Medicas Pubmed, science direct, scop, Nature, NIH ninguna otra, Ni google
            ni wikipedia, no quiero informacion nunca de esas webs solo de las antes mencionadas debes revisar un minimo
            de 3 a 6 fuentes diferentes nunca menos de 3 fuentes.
        ";
        $completePrompt ="Pregunta: " . $query;

        $model = $investigar ? 'gpt-4o-mini-search-preview' : 'gpt-4o-mini';
        $result = OpenAI::chat()->create([
            'model' => $model,
            'messages' => [
                ["role" => "system", "content" => $initial_prompt],
                ['role' => 'user', 'content' => $completePrompt],
            ],
        ]);
        return $this->processResponse($result->choices[0]->message->content);
    }

    private function processResponse($response)
    {
        // Paso 1: Extraer las URLs
        preg_match_all('/\[(.*?)\]\((.*?)\)/', $response, $matches);

        $urls = $matches[2]; // Todas las URLs capturadas (segundo grupo de las coincidencias).
        $titles = $matches[1]; // Los títulos asociados a las URLs (primer grupo de las coincidencias).

        // Paso 2: Construir el array con títulos, descripciones y URLs
        $urlArray = [];
        foreach ($urls as $index => $url) {
            $urlArray[] = [
                'title' => $titles[$index],
                "tldr" => "",
                "year" => "",
                "authors" => [],
                "journal" => "",
                'url' => $url,
            ];
        }

        // Paso 3: Eliminar las URLs del texto
        $cleanText = preg_replace('/\[(.*?)\]\((.*?)\)/', '', $response);

        // Paso 4: Extraer las descripciones para cada URL
        $sentences = array_filter(array_map('trim', explode("\n", $cleanText))); // Divide el texto en líneas
        foreach ($urlArray as $key => $urlObj) {
            $urlArray[$key]['description'] = $sentences[$key] ?? ''; // Asigna la línea de texto como descripción
        }
        // Retornar el texto limpio y el array con URLs enriquecidas
        return [
            'clean_text' => $cleanText,
            'urls' => $urlArray,
        ];
    }

    public function resumeForPubMed(?string $response){
        $initial_prompt = "necesito que crees un parametro de busqueda para lo que te estoy solicitando en pubmed no pueden exceder de las 3 o 7 palabras, y que se refiera exclusivamente a la sintomatologia y/o Tratamiento, no le antepongas ningun texto al principio de la respuesta, solo la respuesta y siempre en ingles tecnico medico.";
        $completePrompt ="query: " . $response;

        $result = OpenAI::chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                ["role" => "system", "content" => $initial_prompt],
                ['role' => 'user', 'content' => $completePrompt],
            ],
            'temperature' => 0.0,
        ]);

        return $result->choices[0]->message->content;
    }

    public function searchPubMed($query)
    {
        try {
            // Paso 1: Buscar artículos en PubMed usando ESearch
            $response = $this->client->get('https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi', [
                'query' => [
                    'db' => 'pubmed',
                    'term' => $query,
                    'retmode' => 'json',
                    'retmax' => 5, // Limitar a un máximo de 5 resultados
                ],
            ]);

            $searchResults = json_decode($response->getBody(), true);

            // Obtener IDs de los artículos
            $ids = $searchResults['esearchresult']['idlist'];

            if (empty($ids)) {
                return ['message' => 'No se encontraron artículos para la búsqueda: ' . $query];
            }

            // Paso 2: Realizar una única solicitud a ESummary con todos los IDs
            $detailsResponse = $this->client->get('https://eutils.ncbi.nlm.nih.gov/entrez/eutils/esummary.fcgi', [
                'query' => [
                    'db' => 'pubmed',
                    'id' => implode(',', $ids), // Unir los IDs en una sola solicitud
                    'retmode' => 'json',
                ],
            ]);

            $details = json_decode($detailsResponse->getBody(), true);

            // Formatear y retornar los resultados
            $articles = [];
            foreach ($ids as $id) {
                $articles[] = [
                    'title' => $details['result'][$id]['title'] ?? 'Sin título',
                    'url' => 'https://pubmed.ncbi.nlm.nih.gov/' . $id,
                ];
            }

            return $articles;

        } catch (\Exception $e) {
            return [
                'error' => 'No se pudo realizar la búsqueda. Detalles: ' . $e->getMessage(),
            ];
        }
    }

    public function searchScienceDirect($query)
    {
        dump($query);
        try {
            // Paso 1: Realizar la solicitud a ScienceDirect
            $response = $this->client->get(config('services.elsevier.base_url'), [
                'headers' => [
                    'Authorization' => 'bearer '.config('services.elsevier.token'),
                    'Accept' => 'application/json' // Formato de salida JSON
                ],
                'query' => [
                    'query' => $query,     // Términos de búsqueda
                    'count' => 5,          // Número máximo de resultados
                    'start' => 0,          // Índice inicial
                    'httpAccept' => 'application/json' // Formato de respuesta
                ],
            ]);

            // Decodificar respuesta JSON
            $searchResults = json_decode($response->getBody(), true);

            dump($searchResults);

            // Extraer información relevante
            $articles = [];
            foreach ($searchResults['search-results']['entry'] as $entry) {
                $articles[] = [
                    'title' => $entry['title'] ?? 'Sin título',
                    'authors' => $entry['authors'] ?? 'Autor desconocido',
                    'doi' => $entry['doi'] ?? 'Sin DOI',
                    'url' => $entry['link'][0]['@href'] ?? 'Sin URL',
                ];
            }
            dd($articles);
            return $articles;

        } catch (\Exception $e) {
            return [
                'error' => 'No se pudo realizar la búsqueda. Detalles: ' . $e->getMessage(),
            ];
        }
    }
}

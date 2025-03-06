<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class MedisearchController extends Controller
{
    public function chat($query){
        try
        {
            $resultados = $this->buscarMedisearchGuzzle($query);
            dump($resultados);
        }catch
        (\Exception $e) {
            dd('Error: ' . $e->getMessage());
        }
    }
    public function buscarMedisearchGuzzle($query)
    {
        $apiKey = 'TU_API_KEY'; // Reemplaza con tu API Key
        $baseUrl = 'https://api.medisearch.io/v1';
        $client = new Client();

        $response = $client->request('GET', $baseUrl . '/search', [
            'headers' => [
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ],
            'query' => [
                'query' => $query
            ]
        ]);

        $body = $response->getBody();
        return json_decode($body, true);
    }
}

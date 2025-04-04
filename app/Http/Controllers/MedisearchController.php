<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

class MedisearchController extends Controller
{


    Public function index(){
        if (Auth::user()->status === 0){
            return redirect()->route('planes');
        }
        return view('medisearch.index');
    }

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
        $apiKey = config('services.medisearch.token'); // Reemplaza con tu API Key
        $baseUrl = config('services.medisearch.base_url');
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

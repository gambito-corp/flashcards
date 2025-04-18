<?php

namespace App\Http\Controllers;

use App\Services\MercadoPagoService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
use OpenAI\Laravel\Facades\OpenAI;

class MedisearchController extends Controller
{

    protected MercadoPagoService $mercadoPagoService;

    public function __construct(MercadoPagoService $mercadoPagoService){$this->mercadoPagoService = $mercadoPagoService;}

    public function index()
    {
        if (in_array(Auth::user()->id, config('specialUsers.ids'))) {
            return view('medisearch.index');
        }

        return \auth()->user()->status == 1 ? view('medisearch.index') : redirect()->route('planes');
    }

    public function chat($query){
        try
        {
            $resultados = $this->buscarOpenAI($query);
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
    public function buscarOpenAI($query){
        $apiKey = config('services.openAI.token'); // Reemplaza con tu API Key
        $client = new Client([
            'base_uri' => config('services.openAI.base_url'),
            'headers' => [
                'Authorization' => 'Bearer ' . config('services.openAI.token'),
                'Content-Type' => 'application/json',
            ]
        ]);

        dd($query);
        $response = $client->post('completions', [
            'json' => [
                'model' => 'text-davinci-003', // Modelo avanzado para medicina.
                'prompt' => $prompt,
                'max_tokens' => 1000,
                'temperature' => 0.7,
            ],
        ]);

        dd($query);
    }
}

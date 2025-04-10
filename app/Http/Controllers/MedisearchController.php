<?php

namespace App\Http\Controllers;

use App\Services\MercadoPagoService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;

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

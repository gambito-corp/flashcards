<?php

namespace App\Services;

use GuzzleHttp\Client;
use MercadoPago\MercadoPagoConfig;

class MercadoPagoService
{
    public function __construct()
    {
        // Inicializaciones adicionales, si las hubiera.
    }

    /**
     * Configura la autenticación para Mercado Pago.
     */
    protected function authenticate()
    {
        MercadoPagoConfig::setAccessToken(config('services.mercadopago.access_token'));
        MercadoPagoConfig::setRuntimeEnviroment(MercadoPagoConfig::LOCAL);
    }

    /**
     * Envía una solicitud de preaprobación a Mercado Pago utilizando Guzzle.
     *
     * @param array $data Array con la información de la preaprobación.
     *                    Ejemplo:
     *                    [
     *                      "preapproval_plan_id" => "2c938084726fca480172750000000000",
     *                      "reason" => "Suscripcion Premium mensual",
     *                      "external_reference" => "1",
     *                      "payer_email" => "test_user@testuser.com",
     *                      "card_token_id" => "e3ed6f098462036dd2cbabe314b9de2a",
     *                      "auto_recurring" => [
     *                          "frequency" => 1,
     *                          "frequency_type" => "months",
     *                          "start_date" => "2020-06-02T13:07:14.260Z",
     *                          "end_date" => "2022-07-20T15:59:52.581Z",
     *                          "transaction_amount" => 10,
     *                          "currency_id" => "ARS"
     *                      ],
     *                      "back_url" => "https://www.mercadopago.com.ar",
     *                      "status" => "authorized"
     *                    ]
     *
     * @return array Respuesta decodificada de la API.
     *
     * @throws \Exception En caso de error en la petición.
     */
    public function createPreapproval(array $data): array
    {
        // Autenticamos.
        $this->authenticate();

        $client = new Client();

        try {
            $response = $client->post('https://api.mercadopago.com/preapproval', [
                'headers' => [
                    'Content-Type'  => 'application/json',
                    'Authorization' => 'Bearer ' . config('services.mercadopago.access_token'),
                ],
                'json' => $data,
            ]);

            return json_decode($response->getBody()->getContents(), true);
        } catch (\GuzzleHttp\Exception\GuzzleException $e) {
            throw new \Exception("Guzzle Error: " . $e->getMessage());
        }
    }
}

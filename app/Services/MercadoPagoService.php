<?php
//
//namespace App\Services;
//
//use MercadoPago\SDK;
//use MercadoPago\Preference;
//use MercadoPago\Item;
//
//class MercadoPagoService
//{
//    protected $accessToken;
//    protected $publicKey;
//
//    public function __construct()
//    {
//        // Obtén las credenciales desde el archivo de configuración (config/mercadopago.php)
//        $this->accessToken = config('mercadopago.access_token');
//        $this->publicKey   = config('mercadopago.public_key');
//
//        // Inicializa el SDK con el Access Token
//        SDK::setAccessToken($this->accessToken);
//    }
//
//    /**
//     * Crea una preferencia de pago con los datos proporcionados.
//     *
//     * @param array $items Array de items, cada uno con: title, quantity, unit_price.
//     * @param array $backUrls URLs de redirección (success, failure, pending).
//     * @param string|null $autoReturn Valor para auto_return (por ejemplo, 'approved').
//     * @return Preference
//     */
//    public function createPreference(array $items, array $backUrls = [], $autoReturn = 'approved')
//    {
//        $preference = new Preference();
//
//        $mpItems = [];
//        foreach ($items as $itemData) {
//            $item = new Item();
//            $item->title = $itemData['title'] ?? 'Producto';
//            $item->quantity = $itemData['quantity'] ?? 1;
//            $item->unit_price = $itemData['unit_price'] ?? 0;
//            $mpItems[] = $item;
//        }
//        $preference->items = $mpItems;
//
//        if (!empty($backUrls)) {
//            $preference->back_urls = $backUrls;
//            $preference->auto_return = $autoReturn;
//        }
//
//        $preference->save();
//
//        return $preference;
//    }
//
//    /**
//     * Devuelve la URL de checkout para la preferencia creada.
//     *
//     * @param Preference $preference
//     * @return string
//     */
//    public function getCheckoutUrl(Preference $preference)
//    {
//        return $preference->init_point;
//    }
//}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\PriceHistory;
use GuzzleHttp\Client;
use Telegram\Bot\Laravel\Facades\Telegram;

class ProductController extends Controller
{
    public function fetchAndProcessData()
    {
        // Obtener los datos de la API de Mercadona
        $productData = $this->getProductDataFromMercadonaAPI();

        // Procesar los datos y almacenarlos en la base de datos
        foreach ($productData as $data) {
            $productName = $data['name'];
            $productPrice = $data['price'];

            $product = Product::firstOrCreate(['name' => $productName]);

            // Comprobar si hay un cambio de precio
            $lastPrice = $product->prices()->latest()->value('price');

            if ($lastPrice !== $productPrice) {
                // Guardar el nuevo precio en el historial
                $priceHistory = new PriceHistory();
                $priceHistory->product_id = $product->id;
                $priceHistory->price = $productPrice;
                $priceHistory->save();

                // Enviar notificación de cambio de precio a través de Telegram
                $this->sendPriceChangeNotification($product, $lastPrice, $productPrice);
            }
        }

        return response()->json(['message' => 'Data processed successfully']);
    }

    private function getProductDataFromMercadonaAPI()
    {
        $client = new Client();
        $response = $client->request('GET', 'https://tienda.mercadona.es/api/categories/112/');

        if ($response->getStatusCode() == 200) {
            $data = json_decode($response->getBody(), true);
            $products = [];

            // Iterar a través de cada categoría y producto
            foreach ($data['categories'] as $category) {
                foreach ($category['products'] as $product) {
                    $products[] = [
                        'name' => $product['display_name'],
                        'price' => $product['price_instructions']['bulk_price'],
                    ];
                }
            }

            // Devuelve los datos de los productos
            return $products;
        }

        // Si algo sale mal, devuelve un array vacío
        return [];
    }

    private function sendPriceChangeNotification($product, $oldPrice, $newPrice)
    {
        $message = "El precio del producto '{$product->name}' ha cambiado de {$oldPrice} a {$newPrice}.";

        // Obtener usuarios suscritos a las notificaciones
        $users = \App\Models\User::whereNotNull('telegram_chat_id')->get();

        // Enviar notificación a cada usuario suscrito
        foreach ($users as $user) {
            Telegram::sendMessage([
                'chat_id' => $user->telegram_chat_id,
                'text' => $message
            ]);
        }
    }
}

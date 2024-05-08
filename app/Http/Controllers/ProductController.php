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
        $productData = $this->getProductDataFromMercadonaAPI();

        if (!empty($productData)) {
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

                    // Lógica para enviar notificación de cambio de precio a través de Telegram
                    $this->sendPriceChangeNotification($product, $lastPrice, $productPrice);
                }
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

            // Iterar a través de cada categoría
            foreach ($data['categories'] as $category) {
                // Iterar a través de cada producto en la categoría
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

        foreach ($users as $user) {
            Telegram::sendMessage([
                'chat_id' => $user->telegram_chat_id,
                'text' => $message
            ]);
        }
    }

}
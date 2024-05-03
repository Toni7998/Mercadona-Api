<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\PriceHistory;

class ProductController extends Controller
{
    public function fetchAndProcessData()
    {
        // Aquí iría la lógica para obtener los datos de la API de Mercadona
        // y procesarlos para detectar cambios de precio

        // Ejemplo de código para crear o actualizar un producto y su historial de precios
        
        $productData = [...]; // Datos obtenidos de la API de Mercadona
        $productName = $productData['name'];
        $productPrice = $productData['price'];

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

        return response()->json(['message' => 'Data processed successfully']);
    }

    private function sendPriceChangeNotification($product, $oldPrice, $newPrice)
    {
        // Lógica para enviar notificación de cambio de precio a través de Telegram
    }
}

<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    public function sendMessage()
    {
        $users = \App\Models\User::whereNotNull('telegram_chat_id')->get();
        $message = "¡Hola! Se ha detectado una subida de precio en algunos productos.";

        foreach ($users as $user) {
            Telegram::sendMessage([
                'chat_id' => $user->telegram_chat_id,
                'text' => $message
            ]);
        }

        return response()->json(['message' => 'Message sent successfully']);
    }
}

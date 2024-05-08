<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TelegramController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/fetch-data', [ProductController::class, 'fetchAndProcessData']);
Route::get('/send-notification', [TelegramController::class, 'sendMessage']);
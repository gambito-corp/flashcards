<?php

use Illuminate\Support\Facades\Route;

Route::get('', 'index');   // Listar
Route::post('', 'store');   // Crear
Route::get('/plans', 'plans'); // Listar planes de suscripción
Route::get('/{subscription}', 'show');    // Detalle
Route::delete('/{subscription}', 'destroy'); // Cancelar

/* Webhook Mercado Pago ---------------------------------------------- */
Route::post('mercadopago/webhook', 'webhook')
    ->withoutMiddleware('auth:sanctum')          // evita la autenticación
    ->name('mercadopago.webhook');

<?php

use Illuminate\Support\Facades\Route;

//Route::get('/pagos', 'crearSuscripcion')->withoutMiddleware('auth:sanctum'); // Evita la autenticación para esta ruta
Route::post('/plans', 'crearPlan')->withoutMiddleware('auth:sanctum'); // Evita la autenticación para esta ruta
Route::post('/pagos', 'crearSuscripcion')->withoutMiddleware('auth:sanctum'); // Evita la autenticación para esta ruta
Route::post('/webhook/mercadopago', 'handle')
    ->withoutMiddleware('auth:sanctum') // Evita la autenticación
    ->name('pagos.webhook'); // Nombre de la ruta

Route::get('', 'index');   // Listar
Route::post('', 'store');   // Crear
Route::get('/plans', 'plans'); // Listar planes de suscripción
Route::get('/{subscription}', 'show');    // Detalle
Route::delete('/{subscription}', 'destroy'); // Cancelar

/* Webhook Mercado Pago ---------------------------------------------- */
//Route::post('mercadopago/webhook', 'webhook')
//    ->withoutMiddleware('auth:sanctum')          // evita la autenticación
//    ->name('mercadopago.webhook');

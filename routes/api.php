<?php

use App\Http\Controllers\Api\Commons\MenuController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
//Route::post('/mercadopago/webhook', [WebhookController::class, 'handleWebhook'])->name('mercadopago.webhook');

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

//Route::post('conversation/{id}', [MedisearchController::class, 'conversation']);


// Rutas para el menú (requieren autenticación web)
Route::middleware('auth:sanctum')->group(function () {
//    //Auth
//
//    //Commons
    Route::get('/menu', [MenuController::class, 'getMenu']);
    Route::get('/teams', [MenuController::class, 'getTeams']);
    Route::post('/teams/switch', [MenuController::class, 'switchTeam']);
//    //Flashcards
//    Route::get('/flashcard/', [FlashcardController::class, 'index']);
//    Route::get('/flashcard/{id}', [FlashcardController::class, 'show'])->where('id', '[0-9]+');
//    Route::post('/flashcard', [FlashcardController::class, 'store']);
//    Route::put('/flashcard/{id}', [FlashcardController::class, 'update'])->where('id', '[0-9]+');
//    Route::delete('/flashcard/{id}', [FlashcardController::class, 'destroy'])->where('id', '[0-9]+');
//
////    Route::get('/flashcard/categories', [FlashcardController::class, 'categoryIndex']);
////    Route::post('/flashcard/category', [FlashcardController::class, 'categoryStore']);
//    // Nueva ruta para IA
//    Route::get('/flashcard/ai-generate', [FlashcardController::class, 'generateAI']);
//    // Ruta de Inicio del Juego
//    Route::post('/flashcard/start-game', [FlashcardController::class, 'setGame']);
//    Route::get('/flashcard/game', [FlashcardController::class, 'getGame']);
});
Route::get('/test-basic', function () {
    return response()->json(['message' => 'Ruta básica funciona']);
});






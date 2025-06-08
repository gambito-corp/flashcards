<?php

use Illuminate\Support\Facades\Route;

//Flashcards
Route::get('', 'index');
Route::get('{id}', 'show')->where('id', '[0-9]+');
Route::post('', 'store');
Route::put('{id}', 'update')->where('id', '[0-9]+');
Route::delete('{id}', 'destroy')->where('id', '[0-9]+');

Route::get('categories', 'categoryIndex');
Route::post('category', 'categoryStore');
// Nueva ruta para IA
Route::get('ai-generate', 'generateAI');
// Ruta de Inicio del Juego
Route::post('start-game', 'setGame');
Route::get('game', 'getGame');

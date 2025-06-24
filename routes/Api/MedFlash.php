<?php
// ========================================
// RUTAS DE CATEGORÍAS
// ========================================
Route::get('categories', 'categoryIndex');           // Listar con paginación
Route::get('categories/search', 'categorySearch');   // ✅ NUEVA RUTA de búsqueda
Route::post('category', 'categoryStore');            // Crear
Route::put('category/{id}', 'categoryUpdate');       // Actualizar
Route::delete('category/{id}', 'categoryDestroy');   // Eliminar individual
Route::delete('categories/bulk', 'categoryBulkDestroy'); // Eliminar múltiples
Route::delete('/delete-by-category', 'deleteByCategory');
Route::delete('/delete-all', 'deleteAll');

Route::delete('categories/all', 'categoryDestroyAll');   // Eliminar todas
Route::get('categories/count', 'categoriesWithCount');


// ========================================
// RUTAS DE FLASHCARDS
// ========================================
Route::get('', 'index');                            // Listar flashcards
Route::get('{id}', 'show')->where('id', '[0-9]+'); // Ver flashcard
Route::post('', 'store');                           // Crear flashcard
Route::put('/{id}', 'update')->where('id', '[0-9]+');
Route::post('/{id}', 'update')->where('id', '[0-9]+'); // Para formularios
Route::delete('{id}', 'destroy')->where('id', '[0-9]+'); // Eliminar flashcard

// ========================================
// RUTAS DE IA Y JUEGOS
// ========================================
Route::post('/ai-generate', 'generateAI'); // Generar con IA
Route::post('/start-game', 'setGame'); // Iniciar juego
Route::get('/game', 'getGame'); // Obtener datos del juego

// ✅ NUEVAS RUTAS PARA EL JUEGO
Route::post('/{id}/increment-error', 'incrementError')->where('id', '[0-9]+');
Route::post('/game/finish', 'finishGame');

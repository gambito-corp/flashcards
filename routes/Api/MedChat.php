<?php

use Illuminate\Support\Facades\Route;

// ========================================
// RUTAS DE MEDCHAT
// ========================================
Route::post('/conversations/{id}/clear', 'clearConversation'); // Limpiar conversación

Route::post('/ask', 'ask'); // Hacer pregunta al asistente
Route::get('/best-questions', 'getBestQuestions'); // Obtener mejores preguntas
Route::get('/conversations', 'getConversations'); // Obtener historial de conversaciones
Route::get('/conversations/{id}', 'getConversation'); // Obtener conversación específica
Route::post('/conversations', 'createConversation'); // Crear nueva conversación
Route::put('/conversations/{id}/title', 'updateConversationTitle'); // Actualizar título de conversación
Route::delete('/conversations/{id}', 'deleteConversation'); // Eliminar conversación
Route::get('/test', 'test'); // Ruta temporal

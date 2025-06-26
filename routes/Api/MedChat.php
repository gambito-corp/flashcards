<?php

use Illuminate\Support\Facades\Route;

// ========================================
// RUTAS DE MEDCHAT
// ========================================
Route::post('/ask', 'ask'); // Hacer pregunta al asistente
Route::get('/ask-test', 'ask');
Route::get('/ask-copy', 'getBestQuestions');
Route::get('/best-questions', 'getBestQuestions'); // Obtener mejores preguntas


Route::post('/conversations/{id}/clear', 'clearConversation'); // Limpiar conversación
Route::get('/conversations', 'getConversations'); // Obtener historial de conversaciones
Route::get('/conversations/{id}', 'getConversation'); // Obtener conversación específica
Route::post('/conversations', 'createConversation'); // Crear nueva conversación
Route::put('/conversations/{id}/title', 'updateConversationTitle'); // Actualizar título de conversación
Route::delete('/conversations/{id}', 'deleteConversation'); // Eliminar conversación
Route::get('/usage-limits', 'getUserLimits'); // Obtener límites del usuario
Route::post('/usage/increment', 'incrementUsage'); // Incrementar uso
Route::post('/usage/can-send', 'canSendMessage'); // Verificar si puede enviar

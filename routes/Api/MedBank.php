<?php

use Illuminate\Support\Facades\Route;

Route::get('/areas', 'getAreas');
Route::get('/categories', 'getCategories');
Route::get('/tipos', 'getTipos');
Route::get('/universidades', 'getUniversities');
Route::post('/process-pdf-exam', 'processPdfExam');
Route::post('/process-document', 'processDocument');
Route::get('/difficulties', 'getDifficulties');
Route::post('/process-pdf', 'processPdf');
Route::get('/counting-questions', 'countingQuestions');
Route::post('/generate-exam/{type}', 'generateExam');
Route::get('exam/{examId}', 'getExam');
Route::post('resolve-exam', 'resolveExam');

// Endpoints futuros (comentados por ahora)
// Route::get('/universities', [MedbanksController::class, 'getUniversities']);
// Route::get('/exam-config/standard', [MedbanksController::class, 'getStandardConfig']);
// Route::get('/exam-config/ai-topics', [MedbanksController::class, 'getAITopics']);
// Route::get('/exam-config/pdf-settings', [MedbanksController::class, 'getPDFSettings']);
// Route::get('/exam-config/personal-failed-questions', [MedbanksController::class, 'getPersonalFailedQuestions']);
// Route::get('/exam-config/community-failed-questions', [MedbanksController::class, 'getCommunityFailedQuestions']);

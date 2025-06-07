<?php

use Illuminate\Support\Facades\Route;


Route::post('import', 'importQuestionsFromCsv')->name('import');
Route::post('extract-from-pdf', 'extractQuestionsFromPdf')->name('extractFromPdf');
Route::get('get-last-exams-results', 'getLastExamsResults')->name('getLastQuestions');
Route::get('get-graph-exams-data', 'getGraphExamsDataResults')->name('getGraphExamsData');

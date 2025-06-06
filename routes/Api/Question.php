<?php

use Illuminate\Support\Facades\Route;


Route::post('import', 'importQuestionsFromCsv')->name('import');
Route::post('extract-from-pdf', 'extractQuestionsFromPdf')->name('extractFromPdf');

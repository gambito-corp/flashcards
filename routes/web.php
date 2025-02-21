<?php

use App\Http\Controllers\CurrentTeamController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\FlashcardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PreguntasController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::redirect('/home', '/dashboard');
Route::redirect('/', '/dashboard');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    Route::put('/current-team/{team}', [CurrentTeamController::class, 'update'])->name('current-team.update');
//    /*PREGUNTAS*/
    Route::get('/preguntas', [PreguntasController::class, 'index'])
        ->middleware('role:root|admin|colab')
        ->name('preguntas.index');
    Route::get('/preguntas/create', [PreguntasController::class, 'create'])
        ->middleware('role:root|admin|colab')
        ->name('preguntas.crear');
    Route::get('/preguntas/cargar', [PreguntasController::class, 'cargar'])
        ->middleware('role:root|admin|colab')
        ->name('preguntas.cargar');
    Route::get('/preguntas/carrerra', [PreguntasController::class, 'carrera'])
        ->middleware('role:root|admin')
        ->name('preguntas.carrera');
    Route::get('/preguntas/asignatura', [PreguntasController::class, 'asignatura'])
        ->middleware('role:root|admin')
        ->name('preguntas.asignatura');
    Route::get('/preguntas/categoria', [PreguntasController::class, 'categoria'])
        ->middleware('role:root|admin')
        ->name('preguntas.categoria');
    Route::get('/preguntas/tipo', [PreguntasController::class, 'tipo'])
        ->middleware('role:root|admin')
        ->name('preguntas.tipo');
    Route::get('/preguntas/universidad', [PreguntasController::class, 'universidad'])
        ->middleware('role:root|admin')
        ->name('preguntas.universidad');



    Route::get('/preguntas/download', [PreguntasController::class, 'downloadCsvModel'])
        ->middleware('role:root|admin|colab')
        ->name('csv-model.download');

//   /*EXAMENES*/
    Route::get('/examenes', [ExamController::class, 'index'])->name('examenes.index');
    Route::post('/examenes', [ExamController::class, 'createExam'])->name('examenes.create');
    Route::get('/examenes/{id}', [ExamController::class, 'showExam'])->name('examenes.show');
    Route::post('/examenes/evaluar', [ExamController::class, 'evaluarExamen'])->name('examenes.evaluar');

    /*FLASHCARD*/
    Route::get('/flashcard', [FlashcardController::class, 'index'])->name('flashcard.index');
    Route::get('/flashcard/game', [FlashcardController::class, 'game'])->name('flashcard.game');
    Route::get('/flashcard/game/result', [FlashcardController::class, 'result'])->name('flashcard.results');

});

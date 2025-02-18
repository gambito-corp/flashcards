<?php

use App\Http\Controllers\CurrentTeamController;
use App\Http\Controllers\ExamController;
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
    Route::get('/preguntas/crear_pregunta', [PreguntasController::class, 'crearPregunta'])
        ->middleware('role:root|admin|colab')
        ->name('preguntas.create');
    Route::get('/preguntas/download', [PreguntasController::class, 'downloadCsvModel'])
        ->middleware('role:root|admin|colab')
        ->name('csv-model.download');

//   /*EXAMENES*/
    Route::get('/examenes', [ExamController::class, 'index'])->name('examenes.index');
    Route::post('/examenes', [ExamController::class, 'createExam'])->name('examenes.create');
    Route::get('/examenes/{id}', [ExamController::class, 'showExam'])->name('examenes.show');
    Route::post('/examenes/evaluar', [ExamController::class, 'evaluarExamen'])->name('examenes.evaluar');

});

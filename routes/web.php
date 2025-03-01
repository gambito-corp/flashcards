<?php

use App\Http\Controllers\Admin\AdminPreguntasController;
use App\Http\Controllers\Admin\AsignaturasController;
use App\Http\Controllers\Admin\CarrerasController;
use App\Http\Controllers\Admin\CategoriasController;
use App\Http\Controllers\Admin\MainController;
use App\Http\Controllers\Admin\TiposController;
use App\Http\Controllers\Admin\UniversidadesController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CurrentTeamController;
use App\Http\Controllers\CustomLoginController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\FlashcardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PreguntasController;
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

Route::get('/', [HomeController::class, 'landing'])->name('landing');
Route::redirect('/home', '/dashboard');


Route::get('/login', [CustomLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [CustomLoginController::class, 'authenticate'])->name('login.custom');

Route::middleware([
    'auth:sanctum',
    'single.session',
    config('jetstream.auth_session'),
    'verified',
])->group(callback: function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    Route::put('/current-team/{team}', [CurrentTeamController::class, 'update'])->name('current-team.update');
//    /*PREGUNTAS*/
    Route::get('/preguntas', [PreguntasController::class, 'index'])
        ->middleware('role:root|admin|colab')
        ->name('preguntas.index');
    Route::get('/preguntas/create', [PreguntasController::class, 'create'])
        ->middleware('role:root|admin|colab')
        ->name('preguntas.crear');
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


    Route::prefix('admin')
        ->as('admin.')
        ->middleware('role:root|admin|colab') // roles en minúscula para evitar inconsistencias
        ->group(callback: function () {

            // Ruta principal (dashboard) del panel de administración
            Route::get('/', [MainController::class, 'index'])->name('index');

            // Grupo de rutas para "usuarios"
            Route::prefix('usuarios')
                ->as('usuarios.')
                ->middleware('role:root|admin')
                ->group(function () {
                    Route::get('/', [UserController::class, 'index'])->name('index');
                    Route::get('/create', [UserController::class, 'create'])->name('create');
                    Route::post('/create', [UserController::class, 'store'])->name('store');
                    Route::get('/{user}', [UserController::class, 'show'])->name('show');
                    Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
                    Route::put('/{user}', [UserController::class, 'update'])->name('update');
                    Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
                });

            // Grupo de rutas para "universidades"
            Route::prefix('universidades')
                ->as('universidades.')
                ->middleware('role:root|admin')
                ->group(function () {
                    Route::get('/', [UniversidadesController::class, 'index'])->name('index');
                    Route::get('/create', [UniversidadesController::class, 'create'])->name('create');
                    Route::post('/create', [UniversidadesController::class, 'store'])->name('store');
                    Route::get('/{universidad}', [UniversidadesController::class, 'show'])->name('show');
                    Route::get('/{universidad}/edit', [UniversidadesController::class, 'edit'])->name('edit');
                    Route::put('/{universidad}', [UniversidadesController::class, 'update'])->name('update');
                });

            // Grupo de rutas para "carreras"
            Route::prefix('teams')
                ->as('carreras.')
                ->middleware('role:root|admin')
                ->group(function () {
                    Route::get('/', [CarrerasController::class, 'index'])->name('index');
                    Route::get('/create', [CarrerasController::class, 'create'])->name('create');
                    Route::post('/create', [CarrerasController::class, 'store'])->name('store');
                    Route::get('/{team}', [CarrerasController::class, 'show'])->name('show');
                    Route::get('/{team}/edit', [CarrerasController::class, 'edit'])->name('edit');
                    Route::put('/{team}', [CarrerasController::class, 'update'])->name('update');
                });


            Route::prefix('asignaturas')
                ->as('asignaturas.')
                ->middleware('role:root|admin')
                ->group(function () {
                    Route::get('/', [AsignaturasController::class, 'index'])->name('index');
                    Route::get('/create', [AsignaturasController::class, 'create'])->name('create');
                    Route::post('/create', [AsignaturasController::class, 'store'])->name('store');
                    Route::get('/{asignatura}', [AsignaturasController::class, 'show'])->name('show');
                    Route::get('/{asignatura}/edit', [AsignaturasController::class, 'edit'])->name('edit');
                    Route::put('/{asignatura}', [AsignaturasController::class, 'update'])->name('update');
                });

            Route::prefix('categorias')
                ->as('categorias.')
                ->middleware('role:root|admin')
                ->group(function () {
                    Route::get('/', [CategoriasController::class, 'index'])->name('index');
                    Route::get('/create', [CategoriasController::class, 'create'])->name('create');
                    Route::post('/create', [CategoriasController::class, 'store'])->name('store');
                    Route::get('/{category}', [CategoriasController::class, 'show'])->name('show');
                    Route::get('/{category}/edit', [CategoriasController::class, 'edit'])->name('edit');
                    Route::put('/{category}', [CategoriasController::class, 'update'])->name('update');
                });

            Route::prefix('tipos')
                ->as('tipos.')
                ->middleware('role:root|admin')
                ->group(callback: function () {
                    Route::get('/', action: [TiposController::class, 'index'])->name('index');
                    Route::get('/create', action: [TiposController::class, 'create'])->name('create');
                    Route::post('/create', action: [TiposController::class, 'store'])->name('store');
                    Route::get('/{tipo}', action: [TiposController::class, 'show'])->name('show');
                    Route::get('/{tipo}/edit', action: [TiposController::class, 'edit'])->name('edit');
                    Route::put('/{tipo}', action: [TiposController::class, 'update'])->name('update');
                });
            Route::prefix('preguntas')
                ->as('preguntas.')
                ->middleware('role:root|admin')
                ->group(function () {
                    Route::get('/', [AdminPreguntasController::class, 'index'])->name('index');
                    Route::get('/create', [AdminPreguntasController::class, 'create'])->name('create');
                    Route::post('/create', [AdminPreguntasController::class, 'store'])->name('store');
//                    Route::get('/{pregunta}', [AdminPreguntasController::class, 'show'])->name('show');
                    Route::get('/{pregunta}/edit', [AdminPreguntasController::class, 'edit'])->name('edit');
                    Route::put('/{pregunta}', [AdminPreguntasController::class, 'update'])->name('update');
                    Route::delete('/{pregunta}', [AdminPreguntasController::class, 'destroy'])->name('destroy');
                    Route::get('/cargar', [PreguntasController::class, 'cargar'])
                        ->middleware('role:root|admin')
                        ->name('cargar');
                });
        });
});

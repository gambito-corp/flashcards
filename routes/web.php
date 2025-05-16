<?php

use App\Http\Controllers\Admin\AdminPreguntasController;
use App\Http\Controllers\Admin\AsignaturasController;
use App\Http\Controllers\Admin\CarrerasController;
use App\Http\Controllers\Admin\CategoriasController;
use App\Http\Controllers\Admin\ConfigController;
use App\Http\Controllers\Admin\MainController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\TiposController;
use App\Http\Controllers\Admin\UniversidadesController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\CurrentTeamController;
use App\Http\Controllers\CustomLoginController;
use App\Http\Controllers\ExamController;
use App\Http\Controllers\FlashcardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\MedisearchController;
use App\Http\Controllers\MercadoPagoController;
use App\Http\Controllers\PreguntasController;
use App\Http\Controllers\WebhookController;
use App\Models\User;
use Illuminate\Support\Facades\Route;


Route::get('gettigPay/{productId}', [MercadoPagoController::class, 'gettigPay'])->name('gettigPay');
Route::redirect('/home', '/dashboard');
if (config('app.env') === 'production') {
    Route::get('/robots.txt', function () {
        return response('User-agent: *' . PHP_EOL . 'Disallow: /', 200);
    })->name('robots.txt');
    Route::redirect('/', 'https://medbystudents.com/app-banqueo/');
} else {
    Route::redirect('/', '/login');
}

Route::get('/login', [CustomLoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [CustomLoginController::class, 'authenticate'])->name('login.custom');

Route::middleware([
    'auth:sanctum',
    'single.session',
    config('jetstream.auth_session'),
    'verified',
])->group(callback: function () {
    Route::get('/dashboard', [HomeController::class, 'index'])->name('dashboard');

    Route::put('/current-team/{team}', [CurrentTeamController::class, 'update'])->name('current-team.updates');
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
    Route::post('examenes/failed-global', [ExamController::class, 'createExamFailGlobal'])->name('examenes.failed-global');
    Route::post('examenes/failed-user', [ExamController::class, 'createExamUserFailed'])->name('examenes.failed-user');
    Route::post('/examenes/ia', [ExamController::class, 'examenIA'])->name('examenes.ia');
    Route::get('/examenes/estadisticas', [ExamController::class, 'estadisticas'])->name('examenes.estadisticas');
    Route::get('/examenes/{exam}', [ExamController::class, 'show'])->name('examenes.show');


    /*FLASHCARD*/
    Route::get('/flashcard', [FlashcardController::class, 'index'])->name('flashcard.index');
    Route::get('/flashcard/game', [FlashcardController::class, 'game'])->name('flashcard.game');
    Route::get('/flashcard/game/result', [FlashcardController::class, 'result'])->name('flashcard.results');

    /*MEDISEARCH API*/
    Route::get('/doctor-mbs', [MedisearchController::class, 'index'])->name('medisearch.index');


    Route::prefix('admin')
        ->as('admin.')
        ->middleware('role:root|admin|colab') // roles en minúscula para evitar inconsistencias
        ->group(callback: function () {

            // Ruta principal (dashboard) del panel de administración
            Route::get('/', [MainController::class, 'index'])->name('index');

            Route::prefix('roles')
                ->as('roles.')
                ->middleware('role:root|admin')
                ->group(callback: function () {
                    Route::get('', [RoleController::class, 'index'])->name('index');
                    Route::get('create', [RoleController::class, 'create'])->name('create');
                    Route::post('', [RoleController::class, 'store'])->name('store');
                    Route::get('{role}/edit', [RoleController::class, 'edit'])->name('edit');
                    Route::put('{role}', [RoleController::class, 'update'])->name('update');
                });
            Route::prefix('config')
                ->as('config.')
                ->middleware('role:root|admin')
                ->group(callback: function () {
                    Route::get('', [ConfigController::class, 'index'])->name('index');
                    Route::get('{config}/edit', [ConfigController::class, 'edit'])->name('edit');
                });
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
//                    Route::put('/{team}', [CarrerasController::class, 'update'])->name('update');
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


//Mercado Pago
Route::middleware(['auth'])->group(function () {
    Route::get('/planes', [MercadoPagoController::class, 'planes'])->name('planes');
    Route::post('/subscription/create/{product}', [MercadoPagoController::class, 'createSubscription'])->name('subscription.create');
    Route::get('/pago-exitoso/{preapproval_id}', function (\Illuminate\Http\Request $request) {
        // URL de la API de Mercado Pago
        $url = 'https://api.mercadopago.com/preapproval_plan/search';

        // Realizar la solicitud GET con encabezados
        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . env('MP_ACCESS_TOKEN'),
        ])->get($url);

        // Verificar si la solicitud fue exitosa
        if ($response->successful()) {
            // Retornar los datos como JSON
            $results = $response->json()['results'];
            $ultimoArray = end($results);
            $userId = auth()->user()->id;
            $user = User::find($userId);
            $user->status = 1;
            $user->save();
            $pivot = \App\Models\Purchase::create([
                'user_id' => $userId,
                'product_id' => 1,
                'purchase_at' => now(),
                'preapproval' => json_encode($request),
                'preaproval_id' => $request->get('preapproval_id'),
                'status' => $ultimoArray['status'],
                'payer_id' => $ultimoArray['collector_id'],
                'external_reference' => $ultimoArray['external_reference'],
                'init_point' => $ultimoArray['init_point'],
                'suscriptionData' => json_encode($ultimoArray),
            ]);
            return redirect()->route('dashboard');
        }

        $userId = auth()->user()->id;
        $user = User::find($userId);
        $user->status = 1;
        $user->save();
        $pivot = \App\Models\Purchase::create([
            'user_id' => $userId,
            'product_id' => 1,
            'purchase_at' => now(),
        ]);
        $user->purchases;
        dd($request);
    });
});
Route::post('/webhooks/mercadopago', [WebhookController::class, 'mercadoPago'])->name('webhooks.mercadopago');


if (config('app.env') === 'local') {
    // routes/web.php
    Route::get('chatv2', [MedisearchController::class, 'streamChat']);

    Route::get('prueba', function () {
        //Instancia del servicio
        $service = new App\Services\Usuarios\MBIAService();
        $service->newFunction();

    });


    route::get('prueba/403', function () {
        return view('errors.403');
    });
    route::get('prueba/404', function () {
        return view('errors.404');
    });
    route::get('prueba/419', function () {
        return view('errors.419');
    });
    route::get('prueba/500', function () {
        return view('errors.500');
    });
    route::get('prueba/503', function () {
        return view('errors.503');
    });


}

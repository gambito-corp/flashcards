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
use App\Http\Controllers\MedisearchController;
use App\Http\Controllers\MercadoPagoWebhookController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PreguntasController;
use App\Models\Product;
use Illuminate\Support\Facades\Route;

Route::redirect('/home', '/dashboard');
if(config('app.env') === 'production') {
    Route::get('/robots.txt', function () {
        return response('User-agent: *' . PHP_EOL . 'Disallow: /', 200);
    })->name('robots.txt');
    Route::redirect('/', 'https://medbystudents.com/app-banqueo/');
}else{
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

    Route::get('/planes', [HomeController::class, 'planes'])->name('planes');

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
    Route::get('/examenes/{exam}', [ExamController::class, 'show'])->name('examenes.show');
    Route::post('/examenes', [ExamController::class, 'createExam'])->name('examenes.create');
    Route::post('/examenes/evaluar', [ExamController::class, 'evaluarExamen'])->name('examenes.evaluar');

    /*FLASHCARD*/
    Route::get('/flashcard', [FlashcardController::class, 'index'])->name('flashcard.index');
    Route::get('/flashcard/game', [FlashcardController::class, 'game'])->name('flashcard.game');
    Route::get('/flashcard/game/result', [FlashcardController::class, 'result'])->name('flashcard.results');

    /*MEDISEARCH API*/
    Route::get('/medisearch', [MedisearchController::class, 'index'])->name('medisearch.index');


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


Route::middleware(['auth'])->group(function () {
    // Ruta para iniciar la suscripción pasando el ID del producto
    Route::get('/subscription/create/{productId}', [PaymentController::class, 'createSubscription'])
        ->name('mercadopago.createSubscription');

    // Ruta de callback (retorno) de MercadoPago
    Route::get('/mercadopago/callback', [PaymentController::class, 'callback'])
        ->name('mercadopago.callback');
});

use MercadoPago\Client\Preapproval\PreapprovalClient;
use MercadoPago\Exceptions\MPApiException;
use MercadoPago\MercadoPagoConfig;

Route::get('/pago-exitoso', function (Request $request) {
    MercadoPagoConfig::setAccessToken(config('services.mercadopago.token'));

    if (config('app.env') !== 'local') {
        dd('exit');
        try {
            $client = new PreapprovalClient();
            $preapproval = $client->get($request->preapproval_id);
//        dd($preapproval);
            // Verificar estado de la suscripción
            if ($preapproval->status === 'authorized') {
                $product = Product::query()->where('price', $request->summarized->charged_amount)->first();
                $user = auth()->user();
                $user->status = 1;
                $user->save();
                \App\Models\Purchase::create([
                    'user_id' => auth()->user()->id,
                    'product_id' => $product->id,
                    'purchase_at' => now(),
                    'preaproval_id' => $request->preapproval_id,
                ]);

                return redirect()->route('dashboard');
            }
            return redirect()->route('dashboard');

        } catch (MPApiException $e) {
            Log::error('Error MercadoPago: '.$e->getApiResponse()->getContent());
            dd($e->getApiResponse()->getContent(), $e);
        }
    }else{
//      $product = Product::query()->where('price', $request->summarized->charged_amount)->first();

        $user = auth()->user();
        dd($user);
        $user->status = 1;
        $user->save();
        \App\Models\Purchase::create([
            'user_id' => auth()->user()->id,
            'product_id' => 1,
            'purchase_at' => now(),
            'preaproval_id' => $request->preapproval_id,
        ]);

        return redirect()->route('dashboard');
    }

});

Route::post('/procesando-pago', function (Illuminate\Http\Request $request) {
    Log::info("Contenido crudo del webhook: " . $request->getContent());
    Log::info('Procesando pago');
});




if (config('app.env') === 'local')
{
    Route::get('/test', function () {
        $client = new GuzzleHttp\Client();
        $accessToken = config('services.mercadopago.token');

        $users = App\Models\User::with('latestPurchase')
            ->where('status', 1)
            ->whereHas('purchases', function ($query) {
                $query->whereNotNull('preapproval_id');
            })
            ->get();

        dd($users);

        $result = [];

        foreach ($users as $user) {
            try {
                // Se toma la primera compra disponible
                $preapproval_id = optional($user->purchases->first())->preapproval_id;

                // Si no se encontró preapproval_id saltamos el usuario
                if (!$preapproval_id) {
                    continue;
                }

                $response = $client->get("https://api.mercadopago.com/preapproval/{$preapproval_id}", [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $accessToken,
                    ]
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                // Si el estado de la preaprobación no es "authorized", se marca como inactivo.
                if (!isset($data['status']) || $data['status'] !== 'authorized') {
                    $user->update(['status' => 0]);
                    $result[] = "Usuario {$user->id} marcado como inactivo (suscripción no autorizada).";
                }
            } catch (\Exception $e) {
                \Log::error("Error al verificar la suscripción del usuario {$user->id}: " . $e->getMessage());
                $result[] = "Error al verificar suscripción del usuario {$user->id}: " . $e->getMessage();
            }
        }

        return count($result) > 0
            ? implode("<br>", $result)
            : "No se encontró usuarios para actualizar o todas las suscripciones están autorizadas.";
    });
}

<?php

namespace App\Providers;

use App\Http\Controllers\Api\Medbanks\MedbanksController;
use App\Http\Controllers\Api\MedChat\MedChatController;
use App\Http\Controllers\Api\Medflash\MedflashController;
use App\Http\Controllers\Api\Pagos\PagosController;
use App\Http\Controllers\Api\Questions\QuestionsController;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('api')
                ->prefix('api/auth')
                ->name('auth.')
                ->group(base_path('routes/Api/Auth.php'));

            Route::middleware('api', 'auth:sanctum')
                ->prefix('api/questions')
                ->name('questions.')
                ->controller(QuestionsController::class)
                ->group(base_path('routes/Api/Question.php'));

            Route::middleware('api', 'auth:sanctum')
                ->prefix('api/medflash')
                ->name('medFlash.')
                ->controller(MedflashController::class)
                ->group(base_path('routes/Api/MedFlash.php'));

            Route::middleware(['api', 'auth:sanctum'])  // Array con corchetes
            ->prefix('api/medchat')
                ->name('medChat.')
                ->controller(MedChatController::class)
                ->group(base_path('routes/Api/MedChat.php'));

            Route::middleware(['api', 'auth:sanctum'])  // Array con corchetes
            ->prefix('api/medbank')
                ->name('medBank.')
                ->controller(MedbanksController::class)
                ->group(base_path('routes/Api/MedBank.php'));

            Route::middleware(['api', 'auth:sanctum'])  // Array con corchetes
            ->prefix('api/subscriptions')
                ->name('subscriptions.')
                ->controller(PagosController::class)
                ->group(base_path('routes/Api/Pagos.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Telescope::night();

        $this->hideSensitiveRequestDetails();

        $isLocal = $this->app->environment('prod');

        Telescope::filter(function (IncomingEntry $entry) use ($isLocal) {
            return $isLocal ||
                $entry->isReportableException() ||
                $entry->isFailedRequest() ||
                $entry->isFailedJob() ||
                $entry->isScheduledTask() ||
                $entry->hasMonitoredTag();
        });
    }

    /**
     * Prevent sensitive request details from being logged by Telescope.
     */
    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters(['_token']);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
        ]);
    }

    /**
     * Register the Telescope gate.
     *
     * This gate determines who can access Telescope in non-local environments.
     */
    protected function gate(): void
    {
        $user = auth()->guard('sanctum')->user();   // o 'api'
//        $user1 = auth()->guard('api')->user();   // o 'api'
        Log::info('------------------------------------------------------------------------------------------');
        Log::info('User ID: ' . ($user ? $user->id : 'No user authenticated'));
//        Log::info('User1 ID: ' . ($user1 ? $user1->id : 'No user authenticated'));
        Log::info('------------------------------------------------------------------------------------------');
        Gate::define('viewTelescope', function () {
            $user = auth()->guard('sanctum')->user();   // o 'api'
            return $user->hasAnyRole(['admin', 'root']);

        });
    }
}

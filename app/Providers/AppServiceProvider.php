<?php

namespace App\Providers;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Queue\Events\JobQueued;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Redirect to the homepage after signing out from the Filament panel.
        $this->app->singleton(LogoutResponse::class, fn () => new class implements LogoutResponse {
            public function toResponse($request): RedirectResponse
            {
                return redirect('/');
            }
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app['events']->listen(JobQueued::class, function () {
            $token = env('QSTASH_TOKEN');
            $workerUrl = env('APP_URL') . '/worker?secret=' . urlencode(env('CRON_SECRET', ''));

            if ($token) {
                Http::withToken($token)
                    ->timeout(3)
                    ->post('https://qstash.upstash.io/v2/publish/' . urlencode($workerUrl));
            }
        });
    }
}

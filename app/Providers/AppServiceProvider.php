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
            $redis = app('redis')->connection();
            $token = env('QSTASH_TOKEN');
            $workerUrl = env('APP_URL') . '/worker?secret=' . urlencode(env('CRON_SECRET', ''));

            $log = ['ts' => now()->toIso8601String(), 'token_set' => ! empty($token), 'worker_url' => $workerUrl];

            if ($token) {
                try {
                    $response = Http::withToken($token)
                        ->timeout(3)
                        ->post('https://qstash.upstash.io/v2/publish/' . $workerUrl);

                    $log['qstash_status'] = $response->status();
                    $log['qstash_body']   = $response->body();
                } catch (\Throwable $e) {
                    $log['qstash_error'] = $e->getMessage();
                }
            } else {
                $log['skipped'] = 'QSTASH_TOKEN not set';
            }

            $redis->setex('last_job_queued_log', 300, json_encode($log));
        });
    }
}

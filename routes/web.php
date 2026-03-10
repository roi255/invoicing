<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $stats = [
        'invoices'  => Invoice::count(),
        'customers' => Customer::count(),
        'clients'   => User::count(),
    ];

    return view('welcome', compact('stats'));
});

Route::post('/register', [AuthController::class, 'register'])
    ->middleware('guest')
    ->name('register');

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::get('/debug/queue', function () {
    $results = [];

    // 1. Check Redis queue depth before dispatch
    try {
        $before = \Illuminate\Support\Facades\Redis::llen('queues:default');
        $results['redis_jobs_before_dispatch'] = $before;
    } catch (\Throwable $e) {
        $results['redis_jobs_before_dispatch'] = 'FAILED: ' . $e->getMessage();
    }

    // 2. Dispatch a test job
    try {
        dispatch(new \App\Jobs\SendInvoiceEmailJob(
            \App\Models\Invoice::first(),
            \App\Models\SentEmail::first() ?? new \App\Models\SentEmail(),
        ));
        $results['dispatch'] = 'ok';
    } catch (\Throwable $e) {
        $results['dispatch'] = 'FAILED: ' . $e->getMessage();
    }

    // 3. Check Redis queue depth after dispatch
    try {
        $after = \Illuminate\Support\Facades\Redis::llen('queues:default');
        $results['redis_jobs_after_dispatch'] = $after;
    } catch (\Throwable $e) {
        $results['redis_jobs_after_dispatch'] = 'FAILED: ' . $e->getMessage();
    }

    // 4. Check QStash logs for recent deliveries
    try {
        $response = \Illuminate\Support\Facades\Http::withToken(env('QSTASH_TOKEN'))
            ->get('https://qstash.upstash.io/v2/events', ['count' => 5]);
        $results['qstash_recent_events'] = $response->json();
    } catch (\Throwable $e) {
        $results['qstash_recent_events'] = 'FAILED: ' . $e->getMessage();
    }

    return response()->json($results);
});

Route::post('/worker', function (Request $request) {
    $secret = env('CRON_SECRET', '');

    if (empty($secret) || $request->query('secret') !== $secret) {
        abort(401);
    }

    Artisan::call('queue:work', [
        '--stop-when-empty' => true,
        '--max-time'        => 25,
        '--tries'           => 3,
        '--backoff'         => 5,
    ]);

    return response()->json(['status' => 'ok']);
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::middleware('auth')->prefix('reports')->name('reports.')->group(function () {
    Route::get('/customers', [ReportController::class, 'customers'])->name('customers');
    Route::get('/invoices',  [ReportController::class, 'invoices'])->name('invoices');
    Route::get('/payments',  [ReportController::class, 'payments'])->name('payments');
    Route::get('/products',  [ReportController::class, 'products'])->name('products');
});

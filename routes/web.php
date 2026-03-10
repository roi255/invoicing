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

    // Check env vars
    $results['env'] = [
        'QUEUE_CONNECTION' => env('QUEUE_CONNECTION'),
        'REDIS_CLIENT'     => env('REDIS_CLIENT'),
        'REDIS_URL_set'    => !empty(env('REDIS_URL')),
        'QSTASH_TOKEN_set' => !empty(env('QSTASH_TOKEN')),
        'CRON_SECRET_set'  => !empty(env('CRON_SECRET')),
    ];

    // Test Redis connection
    try {
        \Illuminate\Support\Facades\Redis::ping();
        $results['redis'] = 'connected';
    } catch (\Throwable $e) {
        $results['redis'] = 'FAILED: ' . $e->getMessage();
    }

    // Test QStash
    try {
        $token = env('QSTASH_TOKEN');
        if ($token) {
            $response = \Illuminate\Support\Facades\Http::withToken($token)
                ->get('https://qstash.upstash.io/v2/queues');
            $results['qstash'] = $response->successful() ? 'connected' : 'FAILED: ' . $response->status();
        } else {
            $results['qstash'] = 'FAILED: no token';
        }
    } catch (\Throwable $e) {
        $results['qstash'] = 'FAILED: ' . $e->getMessage();
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

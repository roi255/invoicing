<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReportController;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\SentEmail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
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


Route::post('/worker', function (Request $request) {
    $secret = env('CRON_SECRET', '');

    if (empty($secret) || $request->query('secret') !== $secret) {
        abort(401);
    }

    Artisan::call('queue:work', [
        '--stop-when-empty' => true,
        '--max-time'        => 50,
        '--tries'           => 3,
        '--backoff'         => 5,
    ]);

    return response()->json([
        'status' => 'ok',
        'output' => Artisan::output(),
    ]);
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::get('/debug/mail', function (Request $request) {
    $secret = env('CRON_SECRET', '');

    if (empty($secret) || $request->query('secret') !== $secret) {
        abort(401);
    }

    try {
        Mail::raw('Test email from ROI Invoicing', function ($message) use ($request) {
            $message->to($request->query('to', env('MAIL_FROM_ADDRESS')))
                    ->subject('Test Email');
        });

        return response()->json(['status' => 'ok', 'message' => 'Email sent successfully']);
    } catch (\Throwable $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
    }
});

Route::get('/debug/emails', function (Request $request) {
    $secret = env('CRON_SECRET', '');

    if (empty($secret) || $request->query('secret') !== $secret) {
        abort(401);
    }

    return response()->json(
        SentEmail::latest()->limit(20)->get(['id', 'type', 'recipient_email', 'subject', 'status', 'error_message', 'sent_at', 'created_at'])
    );
});

Route::middleware('auth')->prefix('reports')->name('reports.')->group(function () {
    Route::get('/customers', [ReportController::class, 'customers'])->name('customers');
    Route::get('/invoices',  [ReportController::class, 'invoices'])->name('invoices');
    Route::get('/payments',  [ReportController::class, 'payments'])->name('payments');
    Route::get('/products',  [ReportController::class, 'products'])->name('products');
});

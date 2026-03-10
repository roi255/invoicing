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

    $redis     = app('redis')->connection();
    $before    = $redis->llen('queues:default');
    $results   = [];
    $processed = 0;

    while ($processed < 10) {
        $job = \Illuminate\Support\Facades\Queue::connection('redis')->pop('default');

        if (! $job) {
            break;
        }

        $processed++;

        try {
            $job->fire();
            $results[] = ['job' => $job->getName(), 'status' => 'ok'];
        } catch (\Throwable $e) {
            try { $job->fail($e); } catch (\Throwable $ignored) {}
            $results[] = [
                'job'    => $job->getName(),
                'status' => 'failed',
                'error'  => $e->getMessage(),
                'file'   => $e->getFile() . ':' . $e->getLine(),
                'trace'  => collect(explode("\n", $e->getTraceAsString()))->take(8)->values(),
            ];
        }
    }

    $payload = [
        'ts'        => now()->toIso8601String(),
        'before'    => $before,
        'after'     => $redis->llen('queues:default'),
        'processed' => $processed,
        'results'   => $results,
    ];

    // Store result so we can retrieve it after QStash delivery
    $redis->setex('last_worker_result', 300, json_encode($payload));

    return response()->json($payload);
})->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::get('/debug/send-now', function (Request $request) {
    $secret = env('CRON_SECRET', '');

    if (empty($secret) || $request->query('secret') !== $secret) {
        abort(401);
    }

    $invoice = Invoice::with(['customer', 'items.product'])->latest()->firstOrFail();
    $invoice->loadMissing(['customer', 'items.product']);

    try {
        $pdfData = $invoice->generatePdf();

        Mail::to($invoice->getRecipientEmails())
            ->send(new \App\Mail\InvoiceEmail($invoice, $pdfData));

        return response()->json(['status' => 'ok', 'sent_to' => $invoice->getRecipientEmails()]);
    } catch (\Throwable $e) {
        return response()->json([
            'status'  => 'error',
            'message' => $e->getMessage(),
            'file'    => $e->getFile() . ':' . $e->getLine(),
            'trace'   => collect(explode("\n", $e->getTraceAsString()))->take(10)->values(),
        ], 500);
    }
});

Route::get('/debug/redis', function (Request $request) {
    $secret = env('CRON_SECRET', '');

    if (empty($secret) || $request->query('secret') !== $secret) {
        abort(401);
    }

    $redis = app('redis')->connection();

    return response()->json([
        'prefix'     => config('database.redis.options.prefix', ''),
        'queue_conn' => config('queue.default'),
        'redis_conn' => config('queue.connections.redis.connection'),
        'default'    => $redis->llen('queues:default'),
        'reserved'   => $redis->zcard('queues:default:reserved'),
        'delayed'    => $redis->zcard('queues:default:delayed'),
        'failed'     => $redis->llen('queues:failed'),
    ]);
});

Route::get('/debug/process-now', function (Request $request) {
    $secret = env('CRON_SECRET', '');

    if (empty($secret) || $request->query('secret') !== $secret) {
        abort(401);
    }

    $redis    = app('redis')->connection();
    $results  = [];
    $processed = 0;

    // Drain and process up to 10 jobs, capturing full errors
    while ($processed < 10) {
        $job = \Illuminate\Support\Facades\Queue::connection('redis')->pop('default');
        if (! $job) break;

        $processed++;
        try {
            $job->fire();
            $results[] = ['job' => $job->getName(), 'status' => 'ok'];
        } catch (\Throwable $e) {
            try { $job->fail($e); } catch (\Throwable $ignored) {}
            $results[] = [
                'job'    => $job->getName(),
                'status' => 'failed',
                'error'  => $e->getMessage(),
                'file'   => $e->getFile() . ':' . $e->getLine(),
                'trace'  => collect(explode("\n", $e->getTraceAsString()))->take(8)->values(),
            ];
        }
    }

    return response()->json(['processed' => $processed, 'remaining' => $redis->llen('queues:default'), 'results' => $results]);
});

Route::get('/debug/pdf', function (Request $request) {
    $secret = env('CRON_SECRET', '');

    if (empty($secret) || $request->query('secret') !== $secret) {
        abort(401);
    }

    $invoice = Invoice::with(['customer', 'items.product'])->latest()->firstOrFail();

    try {
        $pdf = $invoice->generatePdf();
        return response()->json(['status' => 'ok', 'size_bytes' => strlen($pdf)]);
    } catch (\Throwable $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage(), 'file' => $e->getFile(), 'line' => $e->getLine()], 500);
    }
});

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

Route::get('/debug/worker-result', function (Request $request) {
    $secret = env('CRON_SECRET', '');

    if (empty($secret) || $request->query('secret') !== $secret) {
        abort(401);
    }

    $raw = app('redis')->connection()->get('last_worker_result');

    return response()->json($raw ? json_decode($raw) : ['no_result' => true]);
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

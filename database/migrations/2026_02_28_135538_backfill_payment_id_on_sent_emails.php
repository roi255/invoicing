<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Backfill payment_id for legacy payment email logs created before
        // the payment_id column existed. Match by invoice_id + nearest timestamp.
        $orphans = DB::table('sent_emails')
            ->where('type', 'payment')
            ->whereNull('payment_id')
            ->whereNotNull('invoice_id')
            ->get(['id', 'invoice_id', 'created_at']);

        foreach ($orphans as $log) {
            $payment = DB::table('payments')
                ->where('invoice_id', $log->invoice_id)
                ->orderByRaw("ABS(CAST(strftime('%s', created_at) AS INTEGER) - CAST(strftime('%s', ?) AS INTEGER))", [$log->created_at])
                ->first(['id']);

            if ($payment) {
                DB::table('sent_emails')
                    ->where('id', $log->id)
                    ->update(['payment_id' => $payment->id]);
            }
        }
    }

    public function down(): void {}
};

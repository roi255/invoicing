<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sent_emails', function (Blueprint $table) {
            $table->string('type')->default('invoice');
        });

        // Fix existing records: any row with a payment_id or payment-related subject is a payment email
        DB::table('sent_emails')
            ->where(function ($q) {
                $q->whereNotNull('payment_id')
                  ->orWhere('subject', 'like', 'Payment Received%');
            })
            ->update(['type' => 'payment']);
    }

    public function down(): void
    {
        Schema::table('sent_emails', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};

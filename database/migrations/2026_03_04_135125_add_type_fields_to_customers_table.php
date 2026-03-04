<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('type', 20)->default('individual')->after('id');
            $table->string('contact_name')->nullable()->after('phone');
            $table->string('contact_email')->nullable()->after('contact_name');
            $table->string('contact_phone', 50)->nullable()->after('contact_email');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['type', 'contact_name', 'contact_email', 'contact_phone']);
        });
    }
};

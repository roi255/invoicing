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
        // SQLite doesn't support ALTER COLUMN, so we recreate the table
        // with the updated status constraint that includes 'pending'
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement('
            CREATE TABLE sent_emails_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                invoice_id INTEGER NOT NULL REFERENCES invoices(id) ON DELETE CASCADE,
                recipient_email TEXT NOT NULL,
                subject TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT \'pending\'
                    CHECK(status IN (\'pending\', \'sent\', \'failed\')),
                error_message TEXT NULL,
                sent_at DATETIME NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ');

        DB::statement('INSERT INTO sent_emails_new SELECT * FROM sent_emails');
        DB::statement('DROP TABLE sent_emails');
        DB::statement('ALTER TABLE sent_emails_new RENAME TO sent_emails');

        DB::statement('PRAGMA foreign_keys = ON');
    }

    public function down(): void
    {
        DB::statement('PRAGMA foreign_keys = OFF');

        DB::statement('
            CREATE TABLE sent_emails_new (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                invoice_id INTEGER NOT NULL REFERENCES invoices(id) ON DELETE CASCADE,
                recipient_email TEXT NOT NULL,
                subject TEXT NOT NULL,
                status TEXT NOT NULL DEFAULT \'sent\'
                    CHECK(status IN (\'sent\', \'failed\')),
                error_message TEXT NULL,
                sent_at DATETIME NULL,
                created_at DATETIME NULL,
                updated_at DATETIME NULL
            )
        ');

        DB::statement('INSERT INTO sent_emails_new SELECT * FROM sent_emails');
        DB::statement('DROP TABLE sent_emails');
        DB::statement('ALTER TABLE sent_emails_new RENAME TO sent_emails');

        DB::statement('PRAGMA foreign_keys = ON');
    }
};

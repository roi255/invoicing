# Session Context — ROI Invoicing App

## Stack
- **Laravel 12** + **Filament v4** + **Livewire 3** + **Pest 3**
- **Database**: SQLite (`database/database.sqlite`)
- **Queue**: Database driver (`QUEUE_CONNECTION=database`)
- **Mail**: Gmail SMTP (`MAIL_SCHEME=smtp`, port 587, STARTTLS)

---

## What Was Built This Session

### 1. Invoice Email — Send & Resend
- `app/Mail/InvoiceEmail.php` — Mailable with subject `Invoice INV-XXXXXX-XXXX from ROI Invoicing`
- `resources/views/emails/invoice.blade.php` — HTML email template (line items, totals, due date, notes)
- `Invoice::sendEmail()` — creates a `SentEmail` log entry as `pending`, dispatches `SendInvoiceEmailJob`
- `Invoice::sentEmails()` — HasMany relationship to `SentEmail`
- **Auto-send on creation**: `CreateInvoice::afterCreate()` calls `recalculateTotals()` + `markAsSent()` + `sendEmail()`

### 2. Queued Email Job
- `app/Jobs/SendInvoiceEmailJob.php`
  - `$tries = 3`, `$backoff = 60`
  - `handle()`: sends mail, updates log to `sent`
  - `failed()`: updates log to `failed` with error message
- Queue worker must be running: `php artisan queue:work --tries=3 --timeout=60`

### 3. Email Log
- **Migration**: `create_sent_emails_table` — fields: `invoice_id`, `recipient_email`, `subject`, `status` (pending/sent/failed), `error_message`, `sent_at`
- **Migration fix**: `update_sent_emails_status_column` — recreated table to allow `pending` status (SQLite CHECK constraint workaround)
- `app/Models/SentEmail.php` — `belongsTo(Invoice::class)`
- `app/Filament/Resources/EmailLogs/EmailLogResource.php` — sidebar item under Settings → Email Log (read-only, no create/edit)
- `app/Filament/Resources/EmailLogs/Tables/EmailLogsTable.php` — shows all sent emails, Resend action per row
- `app/Filament/Resources/Invoices/RelationManagers/SentEmailsRelationManager.php` — "Email History" tab on each invoice

### 4. View/Table Actions — Send & Resend
- `ViewInvoice.php` — "Send Invoice" action (Draft only) + "Resend Invoice" action (non-Draft/Cancelled)
- `InvoicesTable.php` — same two actions in the grouped record actions
- Every resend calls `$record->sendEmail()` → always logged

### 5. ActionGroup on All Tables
All `recordActions()` now wrapped in `ActionGroup::make([...])` **except** `EmailLogsTable`.

Files updated:
- `Customers/Tables/CustomersTable.php`
- `Products/Tables/ProductsTable.php`
- `Invoices/Tables/InvoicesTable.php`
- `Invoices/RelationManagers/PaymentsRelationManager.php`
- `Invoices/RelationManagers/SentEmailsRelationManager.php`
- `Customers/RelationManagers/InvoicesRelationManager.php`

---

## Gmail SMTP Config
```env
MAIL_MAILER=smtp
MAIL_SCHEME=smtp        # NOT "tls" — Symfony Mailer uses "smtp" for STARTTLS on port 587
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=loishori21st@gmail.com
MAIL_PASSWORD="xxxx xxxx xxxx xxxx"   # Gmail App Password (requires 2FA enabled)
MAIL_FROM_ADDRESS="loishori21st@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```
> After any `.env` change, restart the queue worker — it caches config at startup.

---

## Key Filament v4 Patterns Confirmed
- `Section` → `Filament\Schemas\Components\Section`
- `Placeholder` → `Filament\Forms\Components\Placeholder` (NOT Schemas)
- `ActionGroup` → `Filament\Actions\ActionGroup`
- Resources auto-discovered via `discoverResources()` in `AdminPanelProvider`
- `$relationship` in RelationManager must be `protected static string`
- `getNavigationIcon()` returns `string|BackedEnum|Htmlable|null`

---

## Test Status
All **57 tests passing** (157 assertions) as of this session.
```
Tests\Unit\ExampleTest                 1 test
Tests\Feature\CustomerResourceTest    14 tests
Tests\Feature\ExampleTest              1 test
Tests\Feature\InvoiceResourceTest     32 tests
Tests\Feature\ProductResourceTest      9 tests
```
Run with: `php artisan test`

---

## Pending / Next Steps
- Run the full test suite: `php artisan test` (tests not run since decimal dollar refactor)
- Consider adding an **Overdue** invoice detection (scheduled command to mark past-due Sent invoices as Overdue)
- Consider a **PDF download** of the invoice
- Consider a **customer-facing invoice portal** (public URL per invoice)
- Production: set up queue worker as a **systemd/supervisor** service so it stays alive
- Production: switch to a dedicated email domain with SPF/DKIM for better deliverability

---

## Running the App
```bash
# Start Laravel dev server
php artisan serve

# Start queue worker (separate terminal — required for email sending)
php artisan queue:work --tries=3 --timeout=60
```

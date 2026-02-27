<?php

namespace App\Models;

use App\Enums\InvoiceStatus;
use App\Enums\PaymentMethod;
use App\Jobs\SendInvoiceEmailJob;
use App\Mail\InvoiceEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'invoice_number',
        'status',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_rate',
        'tax_amount',
        'total',
        'amount_paid',
        'notes',
        'sent_at',
        'paid_at',
    ];

    protected $casts = [
        'status' => InvoiceStatus::class,
        'invoice_date' => 'date',
        'due_date' => 'date',
        'sent_at' => 'datetime',
        'paid_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateInvoiceNumber();
            }
        });
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class)->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function balanceDue(): Attribute
    {
        return Attribute::get(fn () => $this->total - $this->amount_paid);
    }

    public static function generateInvoiceNumber(): string
    {
        $prefix = 'INV-' . now()->format('Ym') . '-';
        $last = self::withTrashed()
            ->where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')
            ->value('invoice_number');

        $next = $last ? ((int) substr($last, -4)) + 1 : 1;

        return $prefix . str_pad($next, 4, '0', STR_PAD_LEFT);
    }

    public function recalculateTotals(): void
    {
        $subtotal = (float) $this->items()->sum('total');
        $taxAmount = round($subtotal * ((float) $this->tax_rate / 100), 2);
        $total = round($subtotal + $taxAmount, 2);

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total' => $total,
        ]);
    }

    public function markAsSent(): void
    {
        $this->update([
            'status' => InvoiceStatus::Sent,
            'sent_at' => now(),
        ]);
    }

    public function sentEmails(): HasMany
    {
        return $this->hasMany(SentEmail::class);
    }

    public function sendEmail(): void
    {
        $this->loadMissing('customer');

        $subject = 'Invoice ' . $this->invoice_number . ' from ' . config('app.name');

        $log = $this->sentEmails()->create([
            'recipient_email' => $this->customer->email,
            'subject'         => $subject,
            'status'          => 'pending',
            'sent_at'         => null,
        ]);

        SendInvoiceEmailJob::dispatch($this, $log);
    }

    public function markAsPaid(): void
    {
        $this->update([
            'status' => InvoiceStatus::Paid,
            'paid_at' => now(),
            'amount_paid' => $this->total,
        ]);
    }

    public function recordPayment(float $amount, PaymentMethod $method, ?string $reference, string $date, ?string $notes = null): Payment
    {
        $payment = $this->payments()->create([
            'amount' => $amount,
            'method' => $method,
            'reference' => $reference,
            'payment_date' => $date,
            'notes' => $notes,
        ]);

        $newAmountPaid = $this->amount_paid + $amount;
        $isPaid = $newAmountPaid >= $this->total;

        $this->update([
            'amount_paid' => $newAmountPaid,
            'status' => $isPaid ? InvoiceStatus::Paid : InvoiceStatus::Sent,
            'paid_at' => $isPaid ? now() : null,
        ]);

        return $payment;
    }
}

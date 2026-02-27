<?php

namespace Database\Factories;

use App\Enums\InvoiceStatus;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        $invoiceDate = fake()->dateTimeBetween('-6 months', 'now');

        return [
            'customer_id' => Customer::factory(),
            'invoice_number' => 'INV-' . now()->format('Ym') . '-' . str_pad(fake()->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'status' => InvoiceStatus::Draft,
            'invoice_date' => $invoiceDate,
            'due_date' => fake()->dateTimeBetween($invoiceDate, '+30 days'),
            'subtotal' => 0.00,
            'tax_rate' => 0,
            'tax_amount' => 0.00,
            'total' => 0.00,
            'amount_paid' => 0.00,
            'notes' => fake()->optional()->paragraph(),
        ];
    }

    public function sent(): static
    {
        return $this->state([
            'status' => InvoiceStatus::Sent,
            'sent_at' => now(),
        ]);
    }

    public function paid(): static
    {
        return $this->state([
            'status' => InvoiceStatus::Paid,
            'sent_at' => now()->subDay(),
            'paid_at' => now(),
        ]);
    }
}

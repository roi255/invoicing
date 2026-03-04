<?php

namespace App\Models;

use App\Enums\CustomerType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'type',
        'name',
        'email',
        'phone',
        'contact_name',
        'contact_email',
        'contact_phone',
        'address_line_1',
        'address_line_2',
        'city',
        'state',
        'postal_code',
        'country',
        'notes',
    ];

    protected $casts = [
        'type' => CustomerType::class,
    ];

    public function isCompany(): bool
    {
        return $this->type === CustomerType::Company;
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }
}

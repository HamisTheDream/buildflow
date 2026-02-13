<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerSalary extends Model
{
    protected $fillable = [
        'employee_name',
        'role',
        'amount_cents',
        'currency',
        'frequency',
        'next_payment_date',
        'is_active',
    ];

    protected $casts = [
        'next_payment_date' => 'date',
        'is_active' => 'boolean',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerExpense extends Model
{
    protected $fillable = [
        'title',
        'amount_cents',
        'currency',
        'date',
        'category',
        'details',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}

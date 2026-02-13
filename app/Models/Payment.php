<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'organization_id',
        'user_id',
        'plan_id',
        'invoice_id',
        'gateway',
        'reference',
        'amount_cents',
        'currency',
        'status',
        'meta',
        'raw_payload',
        'payer_email',
        'gateway_reference',
    ];

    protected $casts = [
        'meta' => 'array',
        'raw_payload' => 'array',
        'amount_cents' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function invoice()
    {
        return $this->belongsTo(\App\Models\Finance\Invoice::class);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaystackWebhookEvent extends Model
{
    protected $fillable = [
        'event','reference','signature','payload','ip','user_agent','received_at'
    ];

    protected $casts = [
        'payload' => 'array',
        'received_at' => 'datetime',
    ];
}

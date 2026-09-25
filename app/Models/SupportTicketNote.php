<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SupportTicketNote extends Model
{
    use SoftDeletes;

    protected $fillable = ['support_ticket_id', 'admin_id', 'user_id', 'note', 'is_public'];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public function admin()
    {
        return $this->belongsTo(\App\Models\Admin::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function ticket()
    {
        return $this->belongsTo(\App\Models\SupportTicket::class, 'support_ticket_id');
    }
}

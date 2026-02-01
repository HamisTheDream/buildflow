<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicketNote extends Model
{
    protected $fillable = ['support_ticket_id','admin_id','note'];

    public function admin()
    {
        return $this->belongsTo(\App\Models\Admin::class);
    }

    public function ticket()
    {
        return $this->belongsTo(\App\Models\SupportTicket::class, 'support_ticket_id');
    }
}

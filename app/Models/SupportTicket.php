<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupportTicket extends Model
{
    protected $fillable = [
        'organization_id','created_by_user_id',
        'subject','message','status','priority','category',
    ];

    public function organization()
    {
        return $this->belongsTo(\App\Models\Organization::class);
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by_user_id');
    }

    public function notes()
    {
        return $this->hasMany(\App\Models\SupportTicketNote::class);
    }
}

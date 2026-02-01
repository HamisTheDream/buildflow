<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentNote extends Model
{
    protected $fillable = ['payment_id','admin_id','note'];

    public function admin()
    {
        return $this->belongsTo(\App\Models\Admin::class);
    }

    public function payment()
    {
        return $this->belongsTo(\App\Models\Payment::class);
    }
}

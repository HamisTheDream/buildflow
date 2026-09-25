<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PaymentNote extends Model
{
    use SoftDeletes;

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

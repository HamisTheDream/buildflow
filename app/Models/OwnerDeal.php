<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OwnerDeal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'value_cents',
        'currency',
        'status',
        'organization_id',
        'details',
        'close_date',
    ];

    protected $casts = [
        'close_date' => 'date',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}

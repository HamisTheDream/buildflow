<?php

namespace App\Models\Finance;

use App\Models\Organization;
use App\Models\User;
use App\Models\CRM\Lead;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Concerns\CascadesSoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, CascadesSoftDeletes;

    protected array $cascadeSoftDeletes = ['payments'];

    protected $guarded = [];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'total_amount_cents' => 'integer',
        'meta' => 'array',
    ];

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }

    public function clientUser()
    {
        return $this->belongsTo(User::class, 'client_user_id');
    }

    public function clientLead()
    {
        return $this->belongsTo(Lead::class, 'client_lead_id');
    }

    public function payments()
    {
        return $this->hasMany(InvoicePayment::class);
    }

    public function project()
    {
        return $this->belongsTo(\App\Models\Project::class);
    }
}

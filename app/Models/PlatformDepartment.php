<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformDepartment extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function manager()
    {
        return $this->belongsTo(Admin::class, 'manager_id');
    }

    public function members()
    {
        return $this->hasMany(Admin::class, 'department_id');
    }
}

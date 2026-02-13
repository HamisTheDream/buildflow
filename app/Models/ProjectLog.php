<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectLog extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'log_date',
        'log_time',
        'type',
        'title',
        'body',
        'workforce_count',
        'weather',
        'materials_delivered',
        'blockers',
        'next_day_plan',
        'project_unit_id',
    ];

    protected $casts = [
        'log_date' => 'date',
    ];

    public function unit(): BelongsTo
    {
        return $this->belongsTo(ProjectUnit::class, 'project_unit_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): \Illuminate\Database\Eloquent\Relations\MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable')->orderByDesc('id');
    }
}

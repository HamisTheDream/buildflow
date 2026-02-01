<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TodayLog extends Model
{
    protected $fillable = [
        'project_id',
        'user_id',
        'log_date',
        'work_done',
        'blockers',
        'next_steps',
        'progress_percent',
        'weather',
    ];

    protected $casts = [
        'log_date' => 'date',
    ];

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

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProjectReport extends Model
{
    protected $fillable = [
        'project_id',
        'generated_by',
        'from_date',
        'to_date',
        'title',
        'type',
        'options',
        'pdf_disk',
        'pdf_path',
        'share_token',
        'share_expires_at',
        'share_password_hash',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'options' => 'array',
        'share_expires_at' => 'datetime',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function generator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function pdfUrl(): string
    {
        return Storage::disk($this->pdf_disk)->url($this->pdf_path);
    }
}

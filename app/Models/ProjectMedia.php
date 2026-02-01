<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ProjectMedia extends Model
{
    protected $fillable = [
        'project_id',
        'uploaded_by',
        'disk',
        'path',
        'original_name',
        'mime',
        'size',
        'caption',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function isImage(): bool
    {
        return $this->mime ? str_starts_with($this->mime, 'image/') : false;
    }
}

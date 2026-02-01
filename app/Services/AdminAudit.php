<?php

namespace App\Services;

use App\Models\AdminAuditLog;
use Illuminate\Database\Eloquent\Model;

class AdminAudit
{
    public function log(
        int $adminId,
        string $action,
        ?Model $subject = null,
        array $before = [],
        array $after = [],
        ?string $reason = null,
        ?string $ip = null,
        ?string $ua = null
    ): void {
        AdminAuditLog::create([
            'admin_id' => $adminId,
            'action' => $action,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(),
            'before' => $before ?: null,
            'after' => $after ?: null,
            'reason' => $reason,
            'ip' => $ip,
            'user_agent' => $ua,
        ]);
    }
}

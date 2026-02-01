<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ActivityLogger
{
    public function log(
        Request $request,
        int $organizationId,
        ?int $projectId,
        ?int $userId,
        string $action,
        string $entityType,
        ?int $entityId = null,
        ?array $before = null,
        ?array $after = null
    ): ActivityLog {
        return ActivityLog::create([
            'organization_id' => $organizationId,
            'project_id' => $projectId,
            'user_id' => $userId,
            'action' => $action,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'before' => $before,
            'after' => $after,
            'ip' => $request->ip(),
            'user_agent' => substr((string)$request->userAgent(), 0, 255),
        ]);
    }

    /**
     * Convenience: log model changes
     */
    public function logModel(
        Request $request,
        int $organizationId,
        ?int $projectId,
        ?int $userId,
        string $action,
        string $entityType,
        ?Model $modelBefore,
        ?Model $modelAfter
    ): ActivityLog {
        $before = $modelBefore ? $this->safeModel($modelBefore) : null;
        $after  = $modelAfter ? $this->safeModel($modelAfter) : null;

        return $this->log(
            $request,
            $organizationId,
            $projectId,
            $userId,
            $action,
            $entityType,
            $modelAfter?->getKey() ?? $modelBefore?->getKey(),
            $before,
            $after
        );
    }

    /**
     * Prevent leaking sensitive fields.
     */
    private function safeModel(Model $m): array
    {
        $arr = $m->toArray();

        unset(
            $arr['password'],
            $arr['remember_token'],
            $arr['two_factor_secret'],
            $arr['two_factor_recovery_codes']
        );

        // avoid dumping huge text blobs
        foreach (['description', 'body', 'work_done', 'blockers', 'next_steps'] as $field) {
            if (isset($arr[$field]) && is_string($arr[$field]) && strlen($arr[$field]) > 500) {
                $arr[$field] = substr($arr[$field], 0, 500) . '...';
            }
        }

        return $arr;
    }
}

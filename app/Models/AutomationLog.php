<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutomationLog extends Model
{
    protected $guarded = [];

    protected $casts = [
        'details' => 'array',
    ];

    /**
     * Record that an automation acted, for audit + idempotency.
     */
    public static function record(
        int $organizationId,
        string $automationKey,
        string $action,
        ?string $entityType = null,
        $entityId = null,
        ?array $details = null
    ): static {
        return static::create([
            'organization_id' => $organizationId,
            'automation_key' => $automationKey,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'details' => $details,
        ]);
    }

    /**
     * Has this automation already performed this action on this entity?
     */
    public static function alreadyDid(
        int $organizationId,
        string $automationKey,
        string $action,
        ?string $entityType = null,
        $entityId = null
    ): bool {
        return static::where('organization_id', $organizationId)
            ->where('automation_key', $automationKey)
            ->where('action', $action)
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->exists();
    }

    public function organization()
    {
        return $this->belongsTo(Organization::class);
    }
}

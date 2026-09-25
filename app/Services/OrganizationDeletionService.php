<?php

namespace App\Services;

use App\Models\Organization;
use Illuminate\Support\Facades\DB;

/**
 * Soft-deletes (and restores) an organization together with its entire
 * database, inside a transaction.
 *
 * Design notes:
 * - Child models are soft-deleted via `$child->delete()` (not bulk queries)
 *   so nested aggregates (Project, SupportTicket, Invoice, CRM Property,
 *   Payment) fire their own CascadesSoftDeletes events for deeper levels.
 * - Tables without an Eloquent model (ERP `properties` / `property_units`)
 *   and the membership pivots are handled with the query builder.
 * - Restore only reverses rows whose deleted_at is at/after the
 *   organization's own deleted_at, so rows deleted individually *before*
 *   the organization was deleted stay deleted.
 * - R2/storage objects are never touched: this service only stamps
 *   deleted_at. Permanent file removal stays exclusively behind the
 *   user-facing delete buttons that call forceDelete().
 */
class OrganizationDeletionService
{
    /**
     * Direct organization child relations, in cascade order.
     */
    protected array $childRelations = [
        'projects',
        'supportTickets',
        'invoices',
        'crmProperties',
        'payments',
        'invites',
        'notes',
        'crmTasks',
        'activityLogs',
        'announcements',
        'leads',
        'deals',
        'expenses',
        'budgets',
        'departments',
        'employees',
        'payrolls',
        'leaves',
        'pageVisits',
        'ownerDeals',
    ];

    public function delete(Organization $organization): void
    {
        $now = now();

        DB::transaction(function () use ($organization, $now) {
            foreach ($this->childRelations as $relation) {
                $organization->{$relation}()->reorder()->chunkById(200, function ($rows) {
                    foreach ($rows as $row) {
                        $row->delete();
                    }
                });
            }

            // ERP properties module (no Eloquent model): units before parents.
            $propertyIds = DB::table('properties')
                ->where('organization_id', $organization->id)
                ->pluck('id');
            DB::table('property_units')
                ->whereIn('property_id', $propertyIds)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => $now]);
            DB::table('properties')
                ->where('organization_id', $organization->id)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => $now]);

            // Membership pivots. Normal queries filter these via
            // wherePivotNull() on the belongsToMany relations.
            DB::table('organization_user')
                ->where('organization_id', $organization->id)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => $now]);

            $projectIds = $organization->projects()->withTrashed()->pluck('projects.id');
            DB::table('project_members')
                ->whereIn('project_id', $projectIds)
                ->whereNull('deleted_at')
                ->update(['deleted_at' => $now]);
        });
    }

    public function restore(Organization $organization): void
    {
        // Called from the `restoring` event, so deleted_at is still set.
        $deletedAt = $organization->deleted_at;

        DB::transaction(function () use ($organization, $deletedAt) {
            foreach ($this->childRelations as $relation) {
                $query = $organization->{$relation}()->withTrashed()->reorder();
                if ($deletedAt) {
                    $query->where('deleted_at', '>=', $deletedAt);
                }
                $query->chunkById(200, function ($rows) {
                    foreach ($rows as $row) {
                        $row->restore();
                    }
                });
            }

            $cascadeScope = function ($query) use ($deletedAt) {
                $query->whereNotNull('deleted_at');
                if ($deletedAt) {
                    $query->where('deleted_at', '>=', $deletedAt);
                }
                return $query;
            };

            // ERP properties module.
            $propertyIds = DB::table('properties')
                ->where('organization_id', $organization->id)
                ->pluck('id');
            $cascadeScope(DB::table('property_units')->whereIn('property_id', $propertyIds))
                ->update(['deleted_at' => null]);
            $cascadeScope(DB::table('properties')->where('organization_id', $organization->id))
                ->update(['deleted_at' => null]);

            // Membership pivots.
            $cascadeScope(DB::table('organization_user')->where('organization_id', $organization->id))
                ->update(['deleted_at' => null]);

            $projectIds = $organization->projects()->withTrashed()->pluck('projects.id');
            $cascadeScope(DB::table('project_members')->whereIn('project_id', $projectIds))
                ->update(['deleted_at' => null]);
        });
    }
}

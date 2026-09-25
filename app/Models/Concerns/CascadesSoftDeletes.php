<?php

namespace App\Models\Concerns;

/**
 * Cascades soft-deletes and restores through declared child relations.
 *
 * Usage: add `use CascadesSoftDeletes;` alongside `SoftDeletes` and declare
 * the direct child relations to cascade through:
 *
 *     protected array $cascadeSoftDeletes = ['projects', 'invites'];
 *
 * Delete cascade runs on the `deleted` model event (after this model's
 * deleted_at is set) and skips force-deletes so explicit permanent deletes
 * (e.g. file removals that also wipe R2 objects) are unaffected.
 *
 * Restore cascade runs on the `restoring` event and only restores children
 * whose deleted_at is at/after this model's own deleted_at — i.e. rows that
 * were deleted as part of this cascade. Rows deleted individually *before*
 * the parent was deleted stay deleted.
 *
 * Child models are expected to use SoftDeletes themselves; parents that are
 * also aggregate roots (Project, SupportTicket, Invoice, Property) use this
 * same trait so the cascade nests correctly.
 */
trait CascadesSoftDeletes
{
    /**
     * Relations to cascade soft-deletes/restores through. Models declare:
     *
     *     protected array $cascadeSoftDeletes = ['relation', ...];
     *
     * (The property lives on the model, not the trait: redefining a trait
     * property with a different default is a fatal error in PHP.)
     */
    protected function cascadeSoftDeleteRelations(): array
    {
        return isset($this->cascadeSoftDeletes) && is_array($this->cascadeSoftDeletes)
            ? $this->cascadeSoftDeletes
            : [];
    }

    protected static function bootCascadesSoftDeletes(): void
    {
        static::deleted(function ($model) {
            if ($model->isForceDeleting()) {
                return;
            }
            $model->cascadeSoftDeleteChildren();
        });

        static::restoring(function ($model) {
            $model->cascadeRestoreChildren($model->getOriginal('deleted_at') ?? $model->deleted_at);
        });
    }

    public function cascadeSoftDeleteChildren(): void
    {
        foreach ($this->cascadeSoftDeleteRelations() as $relation) {
            $this->{$relation}()->reorder()->chunkById(500, function ($children) {
                foreach ($children as $child) {
                    $child->delete();
                }
            });
        }
    }

    public function cascadeRestoreChildren($deletedAt): void
    {
        foreach ($this->cascadeSoftDeleteRelations() as $relation) {
            $query = $this->{$relation}()->withTrashed()->reorder();
            if ($deletedAt) {
                $query->where('deleted_at', '>=', $deletedAt);
            }
            $query->chunkById(500, function ($children) {
                foreach ($children as $child) {
                    $child->restore();
                }
            });
        }
    }
}

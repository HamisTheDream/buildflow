<?php

namespace App\Policies\Owner;

use App\Models\Admin;
use App\Models\OrganizationTask;

class OrganizationTaskPolicy
{
    /**
     * Determine whether the admin can delete the model.
     */
    public function delete(Admin $user, OrganizationTask $task): bool
    {
        // Super admins can delete anything
        if ($user->is_super) {
            return true;
        }

        // Admins can delete their own tasks AND tasks assigned to them
        return $task->created_by === $user->id || $task->assigned_to === $user->id;
    }
}

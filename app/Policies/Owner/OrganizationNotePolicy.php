<?php

namespace App\Policies\Owner;

use App\Models\Admin;
use App\Models\OrganizationNote;

class OrganizationNotePolicy
{
    /**
     * Determine whether the admin can delete the model.
     */
    public function delete(Admin $user, OrganizationNote $note): bool
    {
        if ($user->is_super) {
            return true;
        }

        return $note->admin_id === $user->id;
    }
}

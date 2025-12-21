<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function view(User $user, User $model): bool
    {
        return $user->canManageUsers();
    }

    public function create(User $user): bool
    {
        return $user->canManageUsers();
    }

    public function update(User $user, User $model): bool
    {
        if (!$user->canManageUsers()) {
            return false;
        }

        // Superadmin can update anyone
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin cannot update superadmins
        return !$model->isSuperAdmin();
    }

    public function delete(User $user, User $model): bool
    {
        if (!$user->canManageUsers()) {
            return false;
        }

        // Cannot delete yourself
        if ($user->id === $model->id) {
            return false;
        }

        // Superadmin can delete anyone
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin cannot delete superadmins
        return !$model->isSuperAdmin();
    }
}

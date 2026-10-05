<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;

/**
 * Department management is an administrator-only area.
 * (Viewers still see departments as a filter, but that data comes from the folder page.)
 */
class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function create(User $user): bool
    {
        return $user->isAdministrator();
    }

    public function update(User $user, Department $department): bool
    {
        return $user->isAdministrator();
    }

    public function delete(User $user, Department $department): bool
    {
        return $user->isAdministrator();
    }
}

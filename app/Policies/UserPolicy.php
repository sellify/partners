<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function view(User $user, User $resource)
    {
        if ($resource->isSuperAdmin()) {
            return $user->isSuperAdmin();
        }

        if ($resource->isAdmin()) {
            return $user->isAdmin();
        }

        return $user->id == $resource->id || $user->isAdmin();
    }

    public function viewAny(User $user)
    {
        return $user->isSuperAdmin() || $user->isAdmin();
    }

    public function create(User $user)
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $resource)
    {
        if ($resource->isSuperAdmin()) {
            return $user->isSuperAdmin();
        }

        if ($resource->isAdmin()) {
            return $user->isAdmin();
        }

        return $user->isAdmin();
    }

    public function delete(User $user, User $resource)
    {
        return !$resource->isAdmin() && $user->isSuperAdmin();
    }

    public function restore(User $user, User $resource)
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, User $resource)
    {
        return false;
    }
}

<?php

namespace App\Policies;

use App\Commission;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommissionPolicy
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

    public function view(User $user, Commission $resource)
    {
        return $user->isAdmin() || $resource->user_id === $user->id;
    }

    public function viewAny(User $user)
    {
        return true;
    }

    public function create(User $user)
    {
        return $user->isSuperAdmin();
    }

    public function update(User $user, Commission $resource)
    {
        return $user->isSuperAdmin() && !$resource->paid;
    }

    public function delete(User $user, Commission $resource)
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Commission $resource)
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Commission $resource)
    {
        return false;
    }
}

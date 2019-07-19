<?php

namespace App\Policies;

use App\Email;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EmailPolicy
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

    public function view(User $user, Email $resource)
    {
        return $user->isAdmin();
    }

    public function viewAny(User $user)
    {
        return $user->isAdmin();
    }

    public function create(User $user)
    {
        return $user->isAdmin();
    }

    public function update(User $user, Email $resource)
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Email $resource)
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Email $resource)
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Email $resource)
    {
        return false;
    }
}

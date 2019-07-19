<?php

namespace App\Policies;

use App\Earning;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EarningPolicy
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

    public function view(User $user, Earning $resource)
    {
        return $user->isSuperAdmin();
    }

    public function viewAny(User $user)
    {
        return $user->isAdmin();
    }

    public function create(User $user)
    {
        return false;
    }

    public function update(User $user, Earning $resource)
    {
        return false;
    }

    public function delete(User $user, Earning $resource)
    {
        return false;
    }

    public function restore(User $user, Earning $resource)
    {
        return false;
    }

    public function forceDelete(User $user, Earning $resource)
    {
        return false;
    }
}

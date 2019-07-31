<?php

namespace App\Policies;

use App\Payout;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PayoutPolicy
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

    public function view(User $user, Payout $resource)
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

    public function update(User $user, Payout $resource)
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Payout $resource)
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Payout $resource)
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Payout $resource)
    {
        return false;
    }
}

<?php

namespace App\Policies;

use App\Earning;
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

    public function create(User $user)
    {
        return false;
    }

    public function viewAny(User $user)
    {
        return true;
    }

    public function view(User $user, Earning $earning)
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Earning $earning)
    {
        return false;
    }
}

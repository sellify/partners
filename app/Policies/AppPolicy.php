<?php

namespace App\Policies;

use App\App;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AppPolicy
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

    public function view(User $user, App $resource)
    {
        return true;
    }

    public function create(User $user)
    {
        return $user->isAdmin();
    }

    public function update(User $user, App $resource)
    {
        return $user->isSuperAdmin();
    }

    public function delete(User $user, App $resource)
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, App $resource)
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, App $resource)
    {
        return false;
    }
}

<?php

namespace App\Policies;

use App\Setting;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SettingPolicy
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

    public function view(User $user, Setting $resource)
    {
        return $user->isAdmin();
    }

    public function viewAny(User $user)
    {
        return $user->isAdmin();
    }

    public function create(User $user)
    {
        return ($user->isAdmin() && Setting::count() < 1);
    }

    public function update(User $user, Setting $resource)
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Setting $resource)
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Setting $resource)
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Setting $resource)
    {
        return false;
    }
}

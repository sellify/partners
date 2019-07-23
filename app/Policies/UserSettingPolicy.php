<?php

namespace App\Policies;

use App\UserSetting;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserSettingPolicy
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

    public function view(User $user, UserSetting $resource)
    {
        return $user->isAdmin() || $resource->user_id === $user->id;
    }

    public function viewAny(User $user)
    {
        return true;
    }

    public function create(User $user)
    {
        return $user->isAdmin();
    }

    public function update(User $user, UserSetting $resource)
    {
        return $user->isAdmin() || ($resource->user_id === $user->id && $resource->setting->is_editable);
    }

    public function delete(User $user, UserSetting $resource)
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, UserSetting $resource)
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, UserSetting $resource)
    {
        return false;
    }
}

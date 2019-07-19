<?php

namespace App\Policies;

use App\Shop;
use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShopPolicy
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

    public function view(User $user, Shop $resource)
    {
        return $user->isAdmin() || $user->id === $resource->user_id;
    }

    public function create(User $user)
    {
        return $user->isAdmin();
    }

    public function update(User $user, Shop $resource)
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Shop $resource)
    {
        return $user->isSuperAdmin();
    }

    public function restore(User $user, Shop $resource)
    {
        return $user->isSuperAdmin();
    }

    public function forceDelete(User $user, Shop $resource)
    {
        return false;
    }
}

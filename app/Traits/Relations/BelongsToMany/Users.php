<?php

namespace App\Traits\Relations\BelongsToMany;

use App\User;

trait Users
{
    /**
     * @return mixed
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_setting', 'setting_id')->withPivot(['value']);
    }
}

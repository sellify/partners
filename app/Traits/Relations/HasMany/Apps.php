<?php

namespace App\Traits\Relations\HasMany;

trait Apps
{
    /**
     * Has many apps
     */
    public function apps()
    {
        return $this->hasMany(\App\App::class);
    }
}

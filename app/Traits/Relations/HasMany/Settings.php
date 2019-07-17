<?php

namespace App\Traits\Relations\HasMany;

trait Settings
{
    /**
     * Has many
     */
    public function settings()
    {
        return $this->hasMany(\App\Setting::class);
    }
}

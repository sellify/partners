<?php

namespace App\Traits\Relations\HasMany;

trait Commissions
{
    /**
     * Has many
     */
    public function commissions()
    {
        return $this->hasMany(\App\Commission::class);
    }
}

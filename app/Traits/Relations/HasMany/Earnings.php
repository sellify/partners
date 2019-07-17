<?php

namespace App\Traits\Relations\HasMany;

trait Earnings
{
    /**
     * Has many
     */
    public function earnings()
    {
        return $this->hasMany(\App\Earning::class);
    }
}

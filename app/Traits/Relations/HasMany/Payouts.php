<?php

namespace App\Traits\Relations\HasMany;

trait Payouts
{
    /**
     * Has many
     */
    public function payouts()
    {
        return $this->hasMany(\App\Payout::class);
    }
}

<?php

namespace App\Traits\Relations\HasMany;

trait Shops
{
    /**
     * Has many
     */
    public function shops()
    {
        return $this->hasMany(\App\Shop::class);
    }
}

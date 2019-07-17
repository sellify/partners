<?php

namespace App\Traits\Relations\HasMany;

trait Emails
{
    /**
     * Has many
     */
    public function emails()
    {
        return $this->hasMany(\App\Email::class);
    }
}

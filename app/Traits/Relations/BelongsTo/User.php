<?php

namespace App\Traits\Relations\BelongsTo;

trait User
{
    public function user()
    {
        return $this->belongsTo(\App\User::class);
    }
}

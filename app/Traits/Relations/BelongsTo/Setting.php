<?php

namespace App\Traits\Relations\BelongsTo;

trait Setting
{
    public function setting()
    {
        return $this->belongsTo(\App\Setting::class);
    }
}

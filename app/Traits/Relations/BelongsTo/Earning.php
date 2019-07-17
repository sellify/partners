<?php

namespace App\Traits\Relations\BelongsTo;

trait Earning
{
    public function earning()
    {
        return $this->belongsTo(\App\Earning::class);
    }
}

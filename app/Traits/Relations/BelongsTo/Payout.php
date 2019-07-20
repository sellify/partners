<?php

namespace App\Traits\Relations\BelongsTo;

trait Payout
{
    public function payout()
    {
        return $this->belongsTo(\App\Payout::class);
    }
}

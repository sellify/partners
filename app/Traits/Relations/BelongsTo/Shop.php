<?php

namespace App\Traits\Relations\BelongsTo;

trait Shop
{
    public function shop()
    {
        return $this->belongsTo(\App\Shop::class);
    }
}

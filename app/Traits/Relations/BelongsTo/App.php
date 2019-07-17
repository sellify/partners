<?php

namespace App\Traits\Relations\BelongsTo;

trait App
{
    public function app()
    {
        return $this->belongsTo(\App\App::class);
    }
}

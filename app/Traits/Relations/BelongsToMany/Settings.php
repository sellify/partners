<?php

namespace App\Traits\Relations\BelongsToMany;

use App\Setting;

trait Settings
{
    /**
     * @return mixed
     */
    public function settings()
    {
        return $this->belongsToMany(Setting::class, 'user_setting', 'user_id')->withPivot(['value']);
    }
}

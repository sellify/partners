<?php

namespace App;

use App\Traits\Relations\BelongsTo\App as BelongsToApp;
use App\Traits\Relations\BelongsTo\User as BelongsToUser;
use App\Traits\Relations\HasMany\Earnings as HasManyEarnings;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use BelongsToUser, BelongsToApp, HasManyEarnings;

    protected $guarded = [
    ];

    protected $dates = [
        'last_charge_at',
    ];
}

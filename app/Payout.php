<?php

namespace App;

use App\Traits\Relations\BelongsTo\User as BelongsToUser;
use App\Traits\Relations\HasMany\Commissions as HasManyCommissions;
use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    use BelongsToUser, HasManyCommissions;

    protected $dates = [
        'payout_at',
    ];
}

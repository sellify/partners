<?php

namespace App;

use App\Traits\Relations\BelongsTo\User as BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    use BelongsToUser;

    protected $dates = [
        'payout_at',
    ];
}

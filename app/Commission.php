<?php

namespace App;

use App\Traits\Relations\BelongsTo\Earning as BelongsToEarning;
use App\Traits\Relations\BelongsTo\User as BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class Commission extends Model
{
    use BelongsToUser, BelongsToEarning;
}

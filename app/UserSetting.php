<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Relations\BelongsTo\User as BelongsToUser;
use App\Traits\Relations\BelongsTo\Setting as BelongsToSetting;

class UserSetting extends Model
{
    use BelongsToUser, BelongsToSetting;

    protected $table = 'user_setting';
}

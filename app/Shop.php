<?php

namespace App;

use App\Traits\Relations\BelongsTo\App as BelongsToApp;
use App\Traits\Relations\BelongsTo\User as BelongsToUser;
use Illuminate\Database\Eloquent\Model;

class Shop extends Model
{
    use BelongsToUser, BelongsToApp;

    protected $guarded = [

    ];
}

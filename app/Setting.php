<?php

namespace App;

use App\Traits\Relations\BelongsToMany\Users as BelongsToManyUsers;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use BelongsToManyUsers;
}

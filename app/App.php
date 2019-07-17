<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Relations\HasMany\Shops as HasManyShops;

class App extends Model
{
    use HasManyShops;
}

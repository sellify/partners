<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Relations\HasMany\Shops as HasManyShops;
use App\Traits\Relations\HasMany\Earnings as HasManyEarnings;
use App\Traits\Relations\HasMany\Commissions as HasManyCommissions;

class App extends Model
{
    use HasManyShops, HasManyEarnings, HasManyCommissions;
}

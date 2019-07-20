<?php

namespace App;

use App\Traits\Relations\HasMany\Commissions as HasManyCommissions;
use App\Traits\Relations\BelongsTo\App as BelongsToApp;
use App\Traits\Relations\BelongsTo\Shop as BelongsToShop;
use Illuminate\Database\Eloquent\Model;

class Earning extends Model
{
    use HasManyCommissions, BelongsToApp, BelongsToShop;

    protected $guarded = [
    ];

    /**
     * Dates
     * @var array
     */
    protected $dates = [
        'start_date',
        'end_date',
        'payout_date',
        'charge_created_at',
    ];
}

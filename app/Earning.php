<?php

namespace App;

use App\Traits\Relations\HasMany\Commissions as HasManyCommissions;
use Illuminate\Database\Eloquent\Model;

class Earning extends Model
{
    use HasManyCommissions;

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

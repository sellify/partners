<?php

namespace App;

use App\Traits\Relations\HasMany\Commissions as HasManyCommissions;
use Illuminate\Database\Eloquent\Model;

class Payout extends Model
{
    use HasManyCommissions;

    /**
     * Guarded columns
     * @var array
     */
    protected $guarded = [
    ];

    /**
     * Dates columns
     * @var array
     */
    protected $dates = [
        'payout_at',
    ];
}

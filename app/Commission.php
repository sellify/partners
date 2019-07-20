<?php

namespace App;

use App\Traits\Relations\BelongsTo\Earning as BelongsToEarning;
use App\Traits\Relations\BelongsTo\User as BelongsToUser;
use App\Traits\Relations\BelongsTo\App as BelongsToApp;
use App\Traits\Relations\BelongsTo\Shop as BelongsToShop;
use App\Traits\Relations\BelongsTo\Payout as BelongsToPayout;
use Illuminate\Database\Eloquent\Model;
use Laravel\Nova\Actions\Actionable;

class Commission extends Model
{
    use Actionable, BelongsToUser, BelongsToEarning, BelongsToApp, BelongsToShop, BelongsToPayout;

    protected $guarded = [
      'earning_id',
    ];

    protected $dates = [
        'paid_at',
    ];
}

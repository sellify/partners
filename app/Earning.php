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

    /**
     * Create or update earning
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param                                       $data
     *
     * @return \Illuminate\Database\Eloquent\Builder|Model
     */
    public function scopeStore(\Illuminate\Database\Eloquent\Builder $query, $data)
    {
        $data['shop_id'] = $data['shop_id'] ?? $query->getBindings()[0];

        $conditions = [
            'shop_id'           => $data['shop_id'],
            'app_id'            => $data['app_id'],
            'charge_created_at' => $data['charge_created_at'],
            'charge_type'       => $data['charge_type'],
            'payout_date'       => $data['payout_date'],
        ];

        if(!$data['shopify_earning_id'])
        {
            $conditions['amount'] = $data['amount'];
        }

        // Find if already exists
        $earning = self::where($conditions)
                      ->when(
                            isset($data['shopify_earning_id']) && $data['shopify_earning_id'],
                            function ($query) use ($data) {
                                return $query->whereNull('shopify_earning_id')->orWhere('shopify_earning_id', $data['shopify_earning_id']);
                            }
                       )
                       ->first();

        if ($earning) {
            $earning->update([
                'start_date'         => $data['start_date'] ?? null,
                'end_date'           => $data['end_date'] ?? null,
                'payout_date'        => $data['payout_date'],
                'shop_id'            => $data['shop_id'],
                'app_id'             => $data['app_id'],
                'amount'             => $data['amount'],
                'charge_created_at'  => $data['charge_created_at'],
                'charge_type'        => $data['charge_type'],
                'category'           => $data['category'] ?? null,
                'theme_name'         => $data['theme_name'] ?? null,
                'shopify_earning_id' => $data['shopify_earning_id'] ?? $earning->shopify_earning_id,
            ]);
        } else {
            $earning = self::create([
                'start_date'                => $data['start_date'] ?? null,
                'end_date'                  => $data['end_date'] ?? null,
                'payout_date'               => $data['payout_date'],
                'shop_id'                   => $data['shop_id'],
                'app_id'                    => $data['app_id'],
                'amount'                    => $data['amount'],
                'charge_created_at'         => $data['charge_created_at'],
                'charge_type'               => $data['charge_type'],
                'category'                  => $data['category'] ?? null,
                'theme_name'                => $data['theme_name'] ?? null,
                'shopify_earning_id'        => $data['shopify_earning_id'] ?? null,
            ]);
        }

        return $earning;
    }
}

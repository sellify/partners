<?php

namespace App\Imports;

use App\App;
use App\Earning;
use App\Events\EarningAdded;
use App\Shop;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class EarningsImporter implements ToCollection, WithHeadingRow
{
    use Importable;

    public function collection(Collection $rows)
    {
        $mappings = [
            'start_date'                   => 'payout_period',
            'end_date'                     => 'payout_period',
            'payout_date'                  => 'payout_date',
            'shop_id'                      => 'shop',
            'amount'                       => 'partner_share',
            'app_id'                       => 'app_title',
            'charge_created_at'            => 'charge_creation_time',
            'charge_type'                  => 'charge_type',
            'category'                     => 'category',
            'theme_name'                   => 'theme_name',
        ];

        $apps = App::select('id', 'name')->pluck('id', 'name')->toArray();
        $shops = [];

        $rows = $rows->map(function ($row) use ($mappings, $apps, &$shops) {
            $data = [];
            if (isset($row['payout_period'], $row['payout_date'], $row['shop'], $row['payout_date'], $row['partner_share'], $row['app_title'], $apps[$row['app_title']], $row['charge_creation_time'], $row['charge_type'], $row['category'], $row['theme_name'])) {
                $app = $apps[$row['app_title']];

                $shops[$row['shop'] . '_' . $app] = [
                    'shopify_domain' => $row['shop'],
                    'app_id'         => $app,
                ];

                $data = [
                    'start_date'           => null,
                    'end_date'             => null,
                    'payout_date'          => null,
                    'shop'                 => $row['shop'],
                    'amount'               => null,
                    'app_id'               => $app,
                    'charge_created_at'    => null,
                    'charge_type'          => $row['charge_type'],
                    'category'             => $row['category'],
                    'theme_name'           => $row['theme_name'],
                ];

                $payoutPeriod = $row['payout_period'] ?? null;
                $payoutPeriod = $payoutPeriod ? explode(' - ', $payoutPeriod) : null;

                $data['start_date'] = $payoutPeriod ? Carbon::createFromFormat('F d, Y', $payoutPeriod[0])->format('Y-m-d 00:00:00') : null;
                $data['end_date'] = $payoutPeriod ? Carbon::createFromFormat('F d, Y', $payoutPeriod[1])->format('Y-m-d 00:00:00') : null;

                $data['payout_date'] = $row['payout_date'] ? Carbon::parse($row['payout_date']) : null;
                $data['charge_created_at'] = $row['payout_date'] ? Carbon::parse($row['payout_date']) : null;

                $data['amount'] = $row['partner_share'] ? $row['partner_share'] * 100 : null;
            }

            return $data;
        });

        $shops = Shop::where(function ($query) use ($shops) {
            foreach ($shops as $shop) {
                $query->orWhere(function ($query) use ($shop) {
                    $query->where('shopify_domain', $shop['shopify_domain'])
                          ->where('app_id', $shop['app_id']);
                });
            }
        })->select('id', 'shopify_domain', 'app_id', 'last_charge_at')->get();

        foreach ($rows as $row) {
            if ($row
                && $row['start_date']
                && $row['end_date']
                && $row['payout_date']
                && $row['shop']
                && $row['amount']
                && $row['charge_created_at']
                && $row['charge_type']
                && $row['app_id']
            ) {
                $shop = $shops->where('shopify_domain', $row['shop'])->where('app_id', $row['app_id'])->first();

                if (!$shop) {
                    $shop = Shop::create([
                        'app_id'         => $row['app_id'],
                        'shopify_domain' => $row['shop'],
                        'user_id'        => null,
                        'last_charge_at' => $row['payout_date'],
                    ]);

                    $shops->prepend($shop);
                } else {
                    $lastChargeAt = is_string($shop->last_charge_at) ? Carbon::parse($shop->last_charge_at) : $shop->last_charge_at;
                    if ($row['payout_date']->gt($lastChargeAt)) {
                        $shop->update([
                            'last_charge_at' => $row['payout_date'],
                        ]);

                        $shops->prepend($shop);
                    }
                }

                Earning::updateOrCreate(
                        [
                            'start_date'        => $row['start_date'],
                            'end_date'          => $row['end_date'],
                            'shop_id'           => $shop->id,
                            'app_id'            => $row['app_id'],
                        ],
                        [
                            'start_date'                => $row['start_date'],
                            'end_date'                  => $row['end_date'],
                            'payout_date'               => $row['payout_date'],
                            'shop_id'                   => $shop->id,
                            'app_id'                    => $row['app_id'],
                            'amount'                    => $row['amount'],
                            'charge_created_at'         => $row['charge_created_at'],
                            'charge_type'               => $row['charge_type'],
                            'category'                  => $row['category'],
                            'theme_name'                => $row['theme_name'],
                        ]
                    );
            }
        }

        // [Event]
        event(new EarningAdded());
    }
}

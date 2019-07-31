<?php

namespace App\Imports;

use App\App;
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
        // All apps
        $apps = (new \App\App())->appsByNames(true);
        $shops = [];

        // Iterate over rows
        $rows = $rows->map(function ($row) use (&$apps, &$shops) {
            $data = [];

            if (isset(
                $row['shop'],
                $row['payout_date'],
                $row['partner_share'],
                $row['charge_creation_time'],
                $row['charge_type'],
                $row['category']
            )) {
                // App id
                $appId = $row['app_title'] && isset($apps[strtolower($row['app_title'])]) ? $apps[strtolower($row['app_title'])] : null;

                if ($row['app_title'] && !$appId) {
                    // Create app
                    $app = App::create([
                       'name'          => $row['app_title'],
                        'slug'         => '',
                        'url'          => '',
                        'appstore_url' => '',
                        'price'        => ceil($row['partner_share'] * 1.25 * 100),
                    ]);

                    $apps[$app->name] = $app->id;
                    $apps[strtolower($app->name)] = $app->id;
                    $appId = $app->id;
                }

                // Store shops in variable so we can find all shops beforehand
                $shops[$row['shop'] . '_' . $appId] = [
                    'shopify_domain' => $row['shop'],
                    'app_id'         => $appId,
                ];

                $data = [
                    'start_date'           => null,
                    'end_date'             => null,
                    'payout_date'          => null,
                    'shop'                 => $row['shop'],
                    'amount'               => null,
                    'app_id'               => $appId,
                    'charge_created_at'    => null,
                    'charge_type'          => ucwords($row['charge_type']),
                    'category'             => $row['category'],
                    'theme_name'           => $row['theme_name'],
                ];

                // Payout period has format like July  1, 2019 - July 15, 2019
                $payoutPeriod = $row['payout_period'] ?? null;
                $payoutPeriod = $payoutPeriod ? explode(' - ', $payoutPeriod) : null;

                $data['start_date'] = $payoutPeriod ? Carbon::createFromFormat('F d, Y', $payoutPeriod[0])->format('Y-m-d 00:00:00') : null;
                $data['end_date'] = $payoutPeriod ? Carbon::createFromFormat('F d, Y', $payoutPeriod[1])->format('Y-m-d 00:00:00') : null;

                $data['payout_date'] = isset($row['payout_date']) && $row['payout_date'] ? Carbon::parse($row['payout_date'])->format('Y-m-d H:i:s') : null;
                $data['charge_created_at'] = $row['charge_creation_time'] ? Carbon::parse($row['charge_creation_time'])->format('Y-m-d H:i:s') : null;

                $data['amount'] = $row['partner_share'] ? $row['partner_share'] * 100 : null;
            }

            return $data;
        });

        // Find shops
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
                && $row['payout_date']
                && $row['shop']
                && $row['amount']
                && $row['charge_created_at']
                && $row['charge_type']
            ) {
                // Find shop from collection
                $shop = $shops->where('shopify_domain', $row['shop'])
                              ->where('app_id', $row['app_id'])
                              ->first();

                // Update last payout date if older
                if ($shop) {
                    $lastChargeAt = is_string($shop->last_charge_at) ? Carbon::parse($shop->last_charge_at) : $shop->last_charge_at;
                    if ($row['payout_date'] && Carbon::parse($row['payout_date'])->gt($lastChargeAt)) {
                        $shop->update([
                            'last_charge_at' => $row['payout_date'],
                        ]);

                        $shops->prepend($shop);
                    }
                } else {
                    $shop = Shop::create([
                        'app_id'         => $row['app_id'],
                        'shopify_domain' => $row['shop'],
                        'user_id'        => null,
                        'last_charge_at' => $row['payout_date'],
                    ]);

                    $shops->prepend($shop);
                }

                $row['shop_id'] = $shop->id;

                // Create or update
                $shop->earnings()->store($row);
            }
        }

        // [Event]
        event(new EarningAdded());
    }
}

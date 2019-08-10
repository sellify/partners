<?php

namespace App\Nova\Actions;

use App\Imports\EarningsImporter;
use Epartment\NovaDependencyContainer\NovaDependencyContainer;
use Illuminate\Bus\Queueable;
use Anaseqal\NovaImport\Actions\Action;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\Number;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;
use Maatwebsite\Excel\Facades\Excel;

class ImportEarnings extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnIndex = true;

    /**
     * Indicates if this action is available to run against the entire resource.
     *
     * @var bool
     */
    public $availableForEntireResource = true;

    /**
     * Message
     *
     * @var string
     */
    public $message;

    /**
     * Perform the action.
     *
     * @param  \Laravel\Nova\Fields\ActionFields $fields
     *
     * @return mixed
     */
    public function handle(ActionFields $fields)
    {
        if ($fields->file && $fields->import_type === 'csv') {
            Excel::import(new EarningsImporter(), $fields->csv_file);

            return Action::message('Import complete');
        } elseif ($fields->partners_cookie && $fields->import_type === 'api') {
            if ($this->auth($fields->account_id, $fields->partners_cookie)) {
                // Save data to cache
                Cache::add('shopify_partners_id', $fields->account_id, now()->addDays(7));
                Cache::add('shopify_partners_cookie', $fields->partners_cookie, now()->addDays(1));

                Artisan::queue('shopify:fetch_payments', [
                    'cookie'    => $fields->partners_cookie,
                    'id'        => $fields->account_id,
                    '--pending' => !!$fields->pending,
                    '--paid'    => !!$fields->paid,
                    'limit'     => $fields->limit,
                ]);

                return Action::message('The earnings are being imported. It may take a while to complete.');
            } else {
                return Action::danger($this->message ?? 'The auth with Shopify Partners was failed. Please check your cookie or account ID.');
            }
        }

        return Action::danger('Please provide one of CSV file or Cookie.');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Select::make('Import Type')->options([
                'csv' => 'CSV file',
                'api' => 'Shopify Partners Account',
            ])->rules('required'),

            NovaDependencyContainer::make([
                File::make('CSV File', 'csv_file')
                    ->help('Note: Import only csv file generated from Shopify Partners -> Payout -> Export payouts as CSV'),
            ])->dependsOn('import_type', 'csv'),

            NovaDependencyContainer::make([
                Number::make('Partners Account ID', 'account_id')
                    ->help('Copy it from your partners url. For eg. if you partners account url looks like https://partners.shopify.com/3223222/payments then 3223222 is the account ID')
                    ->withMeta([
                        'value' => Cache::get('shopify_partners_id', ''),
                    ])
                      ->rules('required'),
                Textarea::make('Shopify Partners Cookie', 'partners_cookie')
                        ->withMeta([
                            'value'           => Cache::get('shopify_partners_cookie', ''),
                            'extraAttributes' => [
                                'placeholder' => 'master_device_id=c489454c-cb09-4e94-9b9a-64e5da299bca;......................._gat__other=1',
                            ],
                        ])
                        ->help('Paste your Shopify Partners cookie here. You can get it by visiting your partners account -> Open developers tool -> refresh the page -> Got to Networks tab -> Copy copy cookie from one of request header')
                ->rules('required'),

                Boolean::make('Import Pending Earnings', 'pending')->withMeta([
                    'value' => true,
                ]),
                Boolean::make('Import Previous Earnings', 'paid'),

                NovaDependencyContainer::make([
                Number::make('Number of Previous Payouts to Fetch', 'limit')
                      ->min(0)
                      ->help('If you need to pull all records, enter 0')
                      ->withMeta([
                          'value' => 1,
                      ]),
                    ])->dependsOn('paid', true),
            ])->dependsOn('import_type', 'api'),
        ];
    }

    /**
     * Dummy request to check auth
     * @return mixed
     */
    private function auth($accountId, $cookie)
    {
        try {
            $earnings = rest('GET', "https://partners.shopify.com/{$accountId}/payments/pending.json", [
                'limit' => 1,
                'page'  => 1,
            ], [
                'Cookie' => '_ENV["' . $cookie . '"]',
            ])->body;

            return $earnings;
        } catch (\Exception $e) {
            $this->message = $e->getCode() === 404 ? 'Account ID is invalid.' : $e->getMessage();
        }

        return false;
    }
}

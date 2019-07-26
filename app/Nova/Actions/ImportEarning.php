<?php

namespace App\Nova\Actions;

use App\Imports\EarningsImporter;
use Illuminate\Bus\Queueable;
use Anaseqal\NovaImport\Actions\Action;
use Illuminate\Support\Facades\Artisan;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\Number;
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
        if ($fields->file) {
            Excel::import(new EarningsImporter(), $fields->file);

            return Action::message('Import complete');
        } elseif ($fields->cookie) {
            if ($this->auth($fields->account_id, $fields->cookie)) {
                Artisan::queue('shopify:fetch_payments', [
                    'cookie'    => $fields->cookie,
                    'id'        => $fields->account_id,
                    '--pending' => !!$fields->pending,
                    '--paid'    => !!$fields->paid,
                ]);

                return Action::message('The command is added to queue. It will be process shortly.');
            } else {
                return Action::danger($this->message ?? 'The auth with Shopify Partners was failed. Please check your cookie or account ID.');
            }
        }

        return Action::danger('Please provide one of CSV file or Cookies.');
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            File::make('CSV File', 'file')
                ->help('Note: Import only csv file generated from Shopify Partners -> Payout -> Export payouts as CSV'),

            Textarea::make('Cookie', 'cookie')
                    ->withMeta([
                        'extraAttributes' => [
                            'placeholder' => 'master_device_id=c489454c-cb09-4e94-9b9a-64e5da299bca;......................._gat__other=1',
                        ],
                    ])
                    ->help('Paste your Shopify Parteners cookie here if you want to pull from API.'),
            Number::make('Partners Account ID', 'account_id'),
            Boolean::make('Pending'),
            Boolean::make('Paid'),
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

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
            Artisan::call('shopify:fetch_payments', [
                'cookie'    => $fields->cookie,
                'id'        => $fields->id,
                '--pending' => !!$fields->pending,
                '--paid'    => !!$fields->paid,
            ]);

            return Action::message(Artisan::output());
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
            Number::make('id'),
            Boolean::make('Pending'),
            Boolean::make('Paid'),
        ];
    }
}

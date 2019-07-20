<?php

namespace App\Nova\Actions;

use App\Imports\EarningsImporter;
use Illuminate\Bus\Queueable;
use Anaseqal\NovaImport\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Nova\Fields\File;
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
     * Perform the action.
     *
     * @param  \Laravel\Nova\Fields\ActionFields $fields
     *
     * @return mixed
     */
    public function handle(ActionFields $fields)
    {
        Excel::import(new EarningsImporter(), $fields->file);

        return Action::message('Import complete');
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
                ->rules('required')
                ->help('Note: Import only csv file generated from Shopify Partners -> Payout -> Export payouts as CSV'),
        ];
    }
}

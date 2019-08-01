<?php

namespace App\Nova\Actions;

use App\Payout;
use Illuminate\Bus\Queueable;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Nova\Fields\Select;

class PayCommission extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The displayable name of the action.
     *
     * @var string
     */
    public $name = 'Mark as paid';

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $totalRequested = $models->count();
        $totalUpdated = 0;
        $totalNotUpdated = 0;
        $payout = Payout::where('id', $fields->payout_id)->first();

        if ($payout) {
            foreach ($models as $model) {
                if (!$model->paid_at) {
                    $model->payout_id = $payout->id;
                    $model->paid_at = $payout->payout_at;
                    $model->transaction_id = $payout->transaction_id;
                    $model->transaction_status = $payout->status;
                    $model->save();
                    $totalUpdated++;
                    $this->markAsFinished($model);
                } else {
                    $this->markAsFailed($model, 'Already paid.');
                    $totalNotUpdated++;
                }
            }

            if ($totalUpdated) {
                return Action::message($totalUpdated . ' of ' . $totalRequested . ' commission(s) are marked as paid' . ($totalNotUpdated ? 'But ' . $totalNotUpdated . ' commissions not updated because they were already paid.' : ''));
            } else {
                return Action::danger('Selected commissions are already paid and cannot be updated again.');
            }
        } else {
            return Action::danger('Payout is required to mark selected commissions as paid.');
        }
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Select::make('Payout', 'payout_id')
            ->options(Payout::all()->pluck('title', 'id')->toArray())
            ->help('Commissions can only be marked paid when you attach to a payout.'),
        ];
    }
}

<?php

namespace App\Nova\Actions;

use App\Commission;
use App\Payout;
use App\Services\PayPal\CommissionsPayouts;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Actions\Action;
use Illuminate\Support\Collection;
use Laravel\Nova\Fields\ActionFields;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Laravel\Nova\Fields\Text;
use PayPal\Exception\PayPalConnectionException;

class PayCommissions extends Action
{
    use InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Perform the action on the given models.
     *
     * @param  \Laravel\Nova\Fields\ActionFields  $fields
     * @param  \Illuminate\Support\Collection  $models
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        if ($fields->client_id && $fields->client_secret) {
            // Mark commissions as paying
            $payableCommissions = Commission::whereIn('user_id', $models->pluck('id'))
                                            ->join('users', 'users.id', '=', 'commissions.user_id')
                                            ->join('earnings', 'earnings.id', '=', 'commissions.earning_id')
                                            ->where(function ($query) {
                                                return $query->whereNull('commissions.paid_at')
                                                             ->orWhereNull('commissions.payout_id');
                                            })
                                            ->where('earnings.payout_date', '<=', Carbon::now())
                                            ->select([
                                                'users.id',
                                                'users.paypal_email',
                                                'users.minimum_payout',
                                                \DB::raw('sum(commissions.amount) as amount'),
                                            ])
                                            ->groupBy('commissions.user_id')
                                            ->having('amount', '>=', DB::raw('users.minimum_payout'));

            $payableCommissionsDetails = $payableCommissions->get();

            // If payable commissions are found
            if ($payableCommissionsDetails->count($payableCommissionsDetails)) {
                try {
                    $commissionsPayouts = (new CommissionsPayouts($fields->client_id, $fields->client_secret));
                    $payoutBatch = $commissionsPayouts->create($payableCommissionsDetails);

                    // Batch headers
                    $payoutBatchHeaders = $payoutBatch->getBatchHeader();
                    $payoutBatchSenderHeaders = $payoutBatchHeaders->getSenderBatchHeader();

                    // Create payout entry
                    $payout = Payout::create([
                        'title'          => 'Commissions Payout - ' . $payoutBatchSenderHeaders->getSenderBatchId(),
                        'amount'         => $payableCommissionsDetails->sum('amount'),
                        'payment_method' => 'PayPal',
                        'transaction_id' => $payoutBatchHeaders->getPayoutBatchId(),
                        'status'         => $payoutBatchHeaders->getBatchStatus(),
                        'payout_at'      => Carbon::now(),
                        'notes'          => 'PayPal Batch Payout',
                    ]);

                    // Update commissions details
                    $updatedCommissionsCount = $payableCommissions->update([
                        'payout_id'          => $payout->id,
                        'paid_at'            => $payout->payout_at,
                        'transaction_status' => $payout->status,
                    ]);

                    return Action::message($updatedCommissionsCount . ' commissions with a total amount of $' . ($payableCommissionsDetails->sum('amount') / 100) . ' are being proceed. The current status is ' . $payout->status . '.');
                } catch (PayPalConnectionException $pce) {
                    Log::critical($pce->getData());
                    Log::critical($pce->getMessage());

                    return Action::danger($pce->getMessage());
                }
            } else {
                return Action::message('No commissions are due for payout.');
            }
        } else {
            return Action::danger('Client ID and Client Secret are required and cannot be empty.');
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
            Text::make('PayPal Client ID', 'client_id')
                ->help('You can get your credentials from https://developer.paypal.com/developer/applications')
                ->withMeta([
                    'placeholder' => 'AS6GjAo1qrlcHjg92EwWPP81-Kq96uYU-fVAkJPtdD-AIDaLjJ3I425jeW5mnZHrTcIQuGIx7fzvGtJ1',
                ])
                ->rules('required'),

            Text::make('PayPal Client Secret', 'client_secret')
                ->help('You can get your credentials from https://developer.paypal.com/developer/applications')
                ->withMeta([
                    'placeholder' => 'EJ2abBaiNSgucDsHGnujZuc5Z4HPps3E5XZXuH5yZ4Utz7CZdcsQKGkLSqkVzCGkLzy_s5tXcNh2WtmO',
                ])
                ->rules('required'),
        ];
    }
}

<?php

namespace App\Jobs;

use App\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Payout;
use App\Services\PayPal\CommissionsPayouts;
use Illuminate\Support\Facades\Log;
use PayPal\Exception\PayPalConnectionException;

class CheckPayPalPendingPayoutsStatus implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Pending payouts
        $payouts = Payout::where('status', 'PENDING')->where('payment_method', 'PayPal')->get();

        if (count($payouts)) {
            foreach ($payouts as $payout) {
                try {
                    // Get details from API
                    $payoutBatch = (new CommissionsPayouts())->get($payout->transaction_id);

                    // Batch headers
                    $payoutBatchHeaders = $payoutBatch->getBatchHeader();

                    // Update payout details
                    $payout->update([
                        'status' => $payoutBatchHeaders->getBatchStatus(),
                        'amount' => $payoutBatchHeaders->getAmount()->getValue() * 100,
                    ]);

                    // Iterate over items
                    foreach ($payoutBatch->getItems() as $item) {
                        // Update the commissions associated with this payout
                        $totalPaidCount = $payout->commissions()
                             ->where('commissions.user_id', $item->getPayoutItem()->getSenderItemId())
                             ->update([
                                 'transaction_status' => $item->getTransactionStatus(),
                                 'transaction_id'     => $item->getTransactionId(),
                             ]);

                        $user = User::where('id', $item->getPayoutItem()->getSenderItemId())->first();

                        if ($totalPaidCount > 0 && $user && $user->setting('user.successful_payout_email')) {
                            $user->notify(new \App\Notifications\CommissionsPaid([
                                    'receiver'           => $item->getPayoutItem()->getReceiver(),
                                    'type'               => $item->getPayoutItem()->getRecipientType(),
                                    'amount'             => $item->getPayoutItem()->getAmount()->getValue() . ' ' . $item->getPayoutItem()->getAmount()->getCurrency(),
                                    'note'               => $item->getPayoutItem()->getNote(),
                                    'count'              => $totalPaidCount,
                                    'transaction_status' => $item->getTransactionStatus(),
                                    'transaction_id'     => $item->getTransactionId(),
                                ]));
                        }
                    }
                } catch (PayPalConnectionException $pce) {
                    Log::critical($pce->getData());
                    Log::critical($pce->getMessage());
                }
            }
        }
    }
}

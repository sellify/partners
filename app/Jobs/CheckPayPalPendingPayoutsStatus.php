<?php

namespace App\Jobs;

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
                        $payout->commissions()
                               ->join('users', 'commissions.user_id', '=', 'users.id')
                               ->where('paypal_email', $item->getPayoutItem()->getReceiver())
                               ->update([
                                   'transaction_status' => $item->getTransactionStatus(),
                                   'transaction_id'     => $item->getTransactionId(),
                               ]);
                    }
                } catch (PayPalConnectionException $ppc) {
                    Log::critical($ppc->getMessage());
                }
            }
        }
    }
}

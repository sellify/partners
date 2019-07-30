<?php

namespace App\Console\Commands;

use App\Payout;
use App\Services\PayPal\CommissionsPayouts;
use Illuminate\Console\Command;
use PayPal\Exception\PayPalConnectionException;

class CheckPayPalPayoutsStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paypal:check_batch_status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check PayPal pending batches status and update commissions and payout status in the app.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Pending payouts
        $payouts = Payout::where('status', 'PENDING')->where('payment_method', 'PayPal')->get();

        if (count($payouts)) {
            $bar = $this->output->createProgressBar(count($payouts));
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
                    $this->output->error($ppc->getMessage());
                }

                $bar->advance();
            }

            $bar->finish();
        } else {
            $this->output->write('No pending payouts');
        }
    }
}

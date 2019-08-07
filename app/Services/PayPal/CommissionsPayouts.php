<?php

namespace App\Services\PayPal;

use App\User;
use Carbon\Carbon;
use PayPal\Api\PayoutItem;

class CommissionsPayouts
{
    /**
     * @var string Client Id
     */
    private $clientId;

    /**
     * @var string Client secret
     */
    private $clientSecret;

    /**
     * @var string mode
     */
    private $mode;

    /**
     * CommissionsPayouts constructor.
     *
     * @param        $clientId
     * @param        $clientSecret
     * @param string $mode
     */
    public function __construct($clientId, $clientSecret, $mode = 'live')
    {
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->mode = $mode === 'sandbox' ? 'sandbox' : 'live';
    }

    /**
     * Api credentials
     *
     * @return \PayPal\Rest\ApiContext
     */
    public function credentials()
    {
        // Api context
        $apiContext = new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                $this->clientId, // ClientID
                $this->clientSecret // ClientSecret
            )
        );

        $apiContext->setConfig([
            'mode' => $this->mode,
        ]);

        return $apiContext;
    }

    /**
     * Headers
     *
     * @return \PayPal\Api\PayoutSenderBatchHeader
     */
    public function headers()
    {
        // Header
        $senderBatchHeader = new \PayPal\Api\PayoutSenderBatchHeader();
        $senderBatchHeader->setSenderBatchId(Carbon::now()->format('Y-m-d H:i:s'))
                          ->setEmailSubject((new User())->setting('commissions.payout_email_subject', 'You have a Payout from ' . config('app.name')));

        return $senderBatchHeader;
    }

    /**
     * Create batch
     *
     * @param $payableCommissions
     *
     * @return \PayPal\Api\PayoutBatch
     */
    public function create($payableCommissions)
    {
        // Create payout object
        $payouts = new \PayPal\Api\Payout();
        $payouts->setSenderBatchHeader($this->headers());

        foreach ($payableCommissions as $payableCommission) {
            $item = new PayoutItem();
            $item->setRecipientType('Email');
            $item->setNote((new User())->setting('commissions.payout_note', 'Commissions Payout'))
                 ->setReceiver($payableCommission->paypal_email)
                 ->setAmount((new \PayPal\Api\Currency())->setCurrency('USD')->setValue($payableCommission->amount / 100))
                 ->setSenderItemId($payableCommission->id);

            $payouts->addItem($item);
        }

        return $payouts->create(null, $this->credentials());
    }

    /**
     * Get batch details
     *
     * @param $payoutBatchId
     *
     * @return \PayPal\Api\PayoutBatch
     */
    public function get($payoutBatchId)
    {
        return \PayPal\Api\Payout::get($payoutBatchId, $this->credentials());
    }
}

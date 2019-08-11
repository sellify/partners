<?php

namespace App\Console\Commands;

use App\App;
use App\Events\EarningAdded;
use App\Shop;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FetchPaymentsFromShopify extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'shopify:fetch_payments
                            {id : Your Shopify Partners ID} 
                            {cookie : Shopify Partners cookie from one of your logged in account having permissions to financial. }
                            {limit=0 : Maximum payouts to fetch}
                            {--paid : Fetch paid earnings}
                            {--pending : Fetch pending earnings}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch payments from Shopify Partners API';

    protected $perPage = 250;
    protected $limit = 0;
    protected $cookie;
    protected $accountId;
    protected $payments = [];
    protected $shops;
    protected $apps;

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
        $this->accountId = $this->argument('id');
        $this->cookie = $this->argument('cookie');
        $this->limit = (int) $this->argument('limit');

        if (!$this->accountId) {
            $this->accountId = Cache::get('shopify_partners_id', '');
        }

        if (!$this->cookie) {
            $this->cookie = Cache::get('shopify_partners_cookie', '');
        }

        if ($this->accountId && $this->cookie) {
            if (!$this->auth()) {
                Cache::forget('shopify_partners_cookie');
                $this->error('It seems like the cookie is not valid. Please get a new one. Exiting...');

                return false;
            }

            $this->info('The cookie is valid and authentication was successful.');
            $this->apps = (new \App\App())->appsBy('name', true);

            if ($this->option('paid')) {
                $this->info('Fetching paid payouts....');
                $payments = $this->fetchAllPayments();

                $this->info('Found ' . count($payments) . ' payouts...');

                $counter = 1;
                foreach ($payments as $payment) {
                    $this->info('Fetching earnings for ' . $payment->name);
                    $this->fetchAllEarnings($payment);

                    if ($this->limit && $this->limit <= $counter) {
                        break;
                    }

                    $counter++;
                }
            }

            // Fetch pending earnings
            if ($this->option('pending')) {
                $this->info('Fetching pending earnings....');
                $this->fetchPendingEarnings();
            }

            // [Event]
            event(new EarningAdded());

            Log::info('Earnings import complete');
        }
    }

    /**
     * Save earnings
     * @param      $earnings
     * @param null $payment
     */
    public function saveEarnings($earnings, $payment = null)
    {
        $this->comment('Saving earnings for ' . ($payment->name ?? 'pending payout.'));
        $bar = $this->output->createProgressBar(count($earnings));

        foreach ($earnings as $earning) {
            $charge_type = preg_replace('/(?<=\\w)(?=[A-Z])/', ' $1', $earning->kind);
            $charge_type = trim($charge_type);

            $appId = $this->apps[strtolower($earning->api_client_title)] ?? null;

            if ($earning->api_client_title && !$appId && Str::contains($charge_type, 'Application')) {
                // Create app
                $app = App::create([
                    'name'         => $earning->api_client_title,
                    'slug'         => '',
                    'url'          => '',
                    'appstore_url' => '',
                    'price'        => ceil($earning->total_price * 1.25 * 100),
                ]);

                $this->apps[$app->name] = $app->id;
                $this->apps[strtolower($app->name)] = $app->id;
                $appId = $app->id;
            }

            $shop = Shop::where('shopify_domain', $earning->shop_permanent_domain)->where('app_id', $appId)->first();

            if (!$shop) {
                $shop = Shop::create([
                    'app_id'         => $appId,
                    'shopify_domain' => $earning->shop_permanent_domain,
                    'user_id'        => null,
                    'last_charge_at' => Carbon::parse($earning->release_at)->setTimezone('UTC'),
                ]);
            } else {
                if (Carbon::parse($earning->created_at)->setTimezone('UTC')->gt($shop->last_charge_at)) {
                    $shop->update([
                        'last_charge_at' => Carbon::parse($earning->created_at)->setTimezone('UTC'),
                    ]);
                }
            }
            $data = [
                'start_date'        => null,
                'end_date'          => null,
                'payout_date'       => null,
                'shop'              => $shop->id,
                'amount'            => null,
                'app_id'            => $appId,
                'charge_created_at' => Carbon::parse($earning->created_at)->setTimezone('UTC'),
                'charge_type'       => $charge_type,
                'category'          => Str::contains($charge_type, 'Theme') ? 'Theme revenue' : (
                Str::contains($charge_type, 'Application') ? 'App revenue' : (
                Str::contains($charge_type, 'Affiliate') ? 'Affiliate fee' : 'Other'
                )
                ),
                'theme_name'         => Str::contains($charge_type, 'Theme') ? $earning->api_client_title : null,
                'shopify_earning_id' => $earning->id,
            ];

            $payoutPeriod = $payment->name ?? null;
            $payoutPeriod = $payoutPeriod ? explode(' - ', $payoutPeriod) : null;

            $data['start_date'] = $payoutPeriod ? Carbon::createFromFormat('F d, Y', $payoutPeriod[0])->format('Y-m-d 00:00:00') : null;
            $data['end_date'] = $payoutPeriod ? Carbon::createFromFormat('F d, Y', $payoutPeriod[1])->format('Y-m-d 00:00:00') : null;

            $data['payout_date'] = $earning->release_at ? Carbon::parse($earning->release_at) : null;

            $data['amount'] = ($earning->total_price ?? 0) * 100;

            $data['shop_id'] = $shop->id;

            // Create or update
            $shop->earnings()->store($data);

            $bar->advance();
        }

        $bar->finish();

        $this->line('');
        $this->info('Done saving earnings for ' . ($payment->name ?? 'pending payout.'));
    }

    /**
     * Fetch all payments
     * @return array
     */
    public function fetchAllPayments()
    {
        $totalPages = 1;
        $currentPage = 1;

        for ($i = 1; $i <= $totalPages; $i++) {
            $payments = $this->getPayments($currentPage);
            $this->payments = array_merge($this->payments, $payments->payments);
            $totalPages = $this->limit && $this->limit <= $payments->meta->page_count ? $this->limit : $payments->meta->page_count;
            $currentPage = $payments->meta->page + 1;
        }

        return $this->payments;
    }

    /**
     * Fetch all earnings
     * @param $payment
     */
    public function fetchAllEarnings($payment)
    {
        $totalPages = 1;
        $currentPage = 1;

        for ($i = 1; $i <= $totalPages; $i++) {
            $earnings = $this->getEarnings($payment->id, $currentPage);
            $totalPages = $earnings->meta->page_count;
            $currentPage = $earnings->meta->page + 1;

            $this->saveEarnings($earnings->earnings, $payment);
        }
    }

    /**
     * Fetch pending earnings
     */
    public function fetchPendingEarnings()
    {
        $totalPages = 1;
        $currentPage = 1;
        for ($i = 1; $i <= $totalPages; $i++) {
            $earnings = $this->getEarnings('pending', $currentPage);
            $totalPages = $earnings->meta->page_count;
            $currentPage = $earnings->meta->page + 1;
            $this->saveEarnings($earnings->earnings);
        }
    }

    /**
     * @param $page
     * @param $accountId
     * @param $cookie
     *
     * @return mixed
     */
    private function getPayments($page)
    {
        $payments = rest('GET', "https://partners.shopify.com/{$this->accountId}/payments.json", [
            'limit' => $this->perPage,
            'page'  => $page,
        ], [
            'Cookie' => '_ENV["' . $this->cookie . '"]',
        ])->body;

        return $payments;
    }

    /**
     * @param $page
     * @param $accountId
     * @param $cookie
     *
     * @return mixed
     */
    private function getEarnings($paymentId, $page)
    {
        $earnings = rest('GET', "https://partners.shopify.com/{$this->accountId}/payments/{$paymentId}.json", [
            'limit' => $this->perPage,
            'page'  => $page,
        ], [
            'Cookie' => '_ENV["' . $this->cookie . '"]',
        ])->body;

        return $earnings;
    }

    /**
     * Dummy request to check auth
     * @return mixed
     */
    private function auth()
    {
        $earnings = rest('GET', "https://partners.shopify.com/{$this->accountId}/payments/pending.json", [
            'limit' => 1,
            'page'  => 1,
        ], [
            'Cookie' => '_ENV["' . $this->cookie . '"]',
        ])->body;

        return $earnings;
    }
}

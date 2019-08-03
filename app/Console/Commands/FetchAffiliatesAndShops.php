<?php

namespace App\Console\Commands;

use App\Http\Requests\ShopRequest;
use App\Http\Utilities\RestClient;
use App\Shop;
use App\User;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class FetchAffiliatesAndShops extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:affiliates_and_shops {start_date=0} {end_date=0}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch shops and affiliates from an external URL.';

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
        $startDate = $this->input->getArgument('start_date');
        $endDate = $this->input->getArgument('end_date');

        $affiliateApiUrl = config('apis.fetch_affiliates.url');

        if (!$affiliateApiUrl) {
            $this->warn('URL not set for fetch affiliates api.');

            return;
        }

        $restClient = new RestClient();

        $response = $restClient->request('GET', $affiliateApiUrl, [
            'start_date' => $startDate ? $startDate : Carbon::now()->subDays(config('api.fetch_affiliates.range'))->format('Y-m-d'),
            'end_date'   => $endDate ? $endDate : Carbon::now()->format('Y-m-d'),
        ])->body;

        $success = 0;
        $errors = 0;

        if ($response) {
            $affiliates = $response->affiliates ?? null;

            if ($affiliates) {
                $apps = (new \App\App())->appsBy('slug', true);

                $referrers = [];

                foreach ($affiliates as $affiliate) {
                    if (isset($affiliate->name) && $affiliate->name && !isset($referrers[$affiliate->name])) {
                        $referrer = User::whereUsername($affiliate->name)->first();

                        if ($referrer) {
                            $referrers[$referrer->username] = $referrer->id;
                        }
                    }

                    $data = [
                        'user_id'        => $referrers[$affiliate->name] ?? null,
                        'app_id'         => $apps[$affiliate->app] ?? null,
                        'shopify_domain' => $affiliate->shopify_domain ?? $affiliate->shop,
                    ];

                    if ($data['user_id']
                        && $data['app_id']
                        && $data['shopify_domain']
                    ) {
                        $validator = Validator::make($data, (new ShopRequest())->rules($data));

                        if (!$validator->fails()) {
                            $data = $validator->validated();
                            if (isset($affiliate->created_at) && $affiliate->created_at) {
                                $data['created_at'] = $affiliate->created_at;
                            }
                            $shop = Shop::create($data);
                            $success++;
                        } else {
                            $errors++;
                        }
                    }
                }
            }
        } else {
            $this->warn('No response from api');
        }

        $this->info("Errors: $errors \nSuccess: $success");
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShopRequest;
use App\Http\Resources\ShopResource;
use App\Shop;

class ShopController extends Controller
{
    public function store(ShopRequest $request)
    {
        $shop = Shop::create([
            'user_id' => $request->get('user_id'),
            'app_id'         => $request->get('app_id'),
            'shopify_domain' => $request->get('shopify_domain'),
        ]);

        return new ShopResource($shop);
    }
}

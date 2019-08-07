<?php

namespace App\Http\Controllers;

use App\Http\Requests\ShopRequest;
use App\Http\Resources\ShopResource;
use App\Shop;

class ShopController extends Controller
{
    public function store(ShopRequest $request)
    {
        $shop = Shop::create($request->validated());

        return new ShopResource($shop);
    }
}

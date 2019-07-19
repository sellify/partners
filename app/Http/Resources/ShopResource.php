<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ShopResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'app_id' => $this->app_id,
            'created_at' => $this->created_at,
            'shopify_domain' => $this->shopify_domain,
            'updated_at' => $this->updated_at,
            'user_id' => $this->user_id,
        ];
    }
}

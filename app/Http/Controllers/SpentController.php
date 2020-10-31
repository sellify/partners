<?php

namespace App\Http\Controllers;

use App\Earning;
use Illuminate\Http\Request;

class SpendingController extends Controller
{
    public function find(Request $request)
    {
        $perPage = $request->json('limit') ?? $request->get('limit') ?? 100;

        return Earning::with('shop:id,shopify_domain')
                      ->when(
                          $request->get('app') || $request->json('app'),
                          function ($query) use ($request) {
                              $apps = (new \App\App())->appsBy('slug', true);

                              $appId = $apps[$request->json('app') ?? $request->get('app')] ?? null;

                              return $query->where('app_id', $appId);
                          }
                      )
                      ->when(
                          $request->get('start_date') || $request->json('start_date'),
                          function ($query) use ($request) {
                              $startDate = $request->json('start_date') ?? $request->get('start_date') ?? null;

                              return $query->whereDate('created_at', '>=', $startDate);
                          }
                      )
                      ->when(
                          $request->get('end_date') || $request->json('end_date'),
                          function ($query) use ($request) {
                              $endDate = $request->json('end_date') ?? $request->get('end_date') ?? null;

                              return $query->whereDate('created_at', '<=', $endDate);
                          }
                      )
                      ->when(
                          $request->get('category') || $request->json('category'),
                          function ($query) use ($request) {
                              $category = $request->json('category') ?? $request->get('category') ?? null;

                              return $query->where('category', $category);
                          }
                      )
                      ->when(
                          $request->get('charge_type') || $request->json('charge_type'),
                          function ($query) use ($request) {
                              $charge_type = $request->json('charge_type') ?? $request->get('charge_type') ?? null;

                              return $query->where('charge_type', $charge_type);
                          }
                      )
                      ->paginate($perPage);
    }
}

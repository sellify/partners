<?php

return [
  'fetch_affiliates' => [
      'url'   => env('FETCH_AFFILIATES_API_URL'),
      'range' => env('FETCH_AFFILIATES_API_DAYS', 3),
  ],
];

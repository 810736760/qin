<?php

return [
    'sandbox_token'     => env('TIKTOK_SANDBOX_TOKEN', '7b3f1426853e6db3246b25266869da457f5ddbf0'),
    'sandbox_aid'       => env('TIKTOK_SANDBOX_AID', '7080704806184075266'),
    'sandbox_open_api'  => env('TIKTOK_SANDBOX_OPEN_API', 'https://sandbox-ads.tiktok.com/open_api/'),
    'open_api'          => env('TIKTOK_OPEN_API', 'https://ads.tiktok.com/open_api/'),
    'app_id'            => env('TIKTOK_APP_ID', '7080515019766513665'),
    'app_secret'        => env('TIKTOK_APP_SECRET', 'dde72e2e0844b7cb6261262b86372b5a70f9e3fb'),
    'api_version'       => env('TIKTOK_API_VERSION', 'v1.3'),
    'ads_url'           => env('TIKTOK_ADS_URL', 'https://ads.tiktok.com/'),
    'access_token_path' => env('TIKTOK_ACCESS_TOKEN_PATH', "open_api/%s/oauth2/access_token/"),
    'marketing_api_url' => env('TIKTOK_MARKETING_API_URL', 'https://business-api.tiktok.com/open_api/%s/'),
    'access_token'      => env('TIKTOK_ACCESS_TOKEN', "a9fea3d9d6830e2f4dfe31e56f858888fff43000"),
];

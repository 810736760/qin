<?php
/**
 * 番茄配置
 */
return [
    'url'  => env('OUTSIDE_AMO_API_URL', 'https://www.i18nnovel.com/novelsale/distributor/openapi/{path}/get/v1'),
    'list' => [
        [
            'distributor_id' => env('OUTSIDE_AMO_DISTRIBUTOR_ID', 1746725064709142),
            'secret_key'     => env('OUTSIDE_AMO_SECRET_KEY', 'tgWXzHgZF2dGXtN6uCltiJz8OLYlvdoz'),
            'account_name'   => 'ios@kwmobi.com',
            'id'             => 1
        ],
        [
            'distributor_id' => env('OUTSIDE_AMO_DISTRIBUTOR_ID', 1751099149272102),
            'secret_key'     => env('OUTSIDE_AMO_SECRET_KEY', 'AUKp8ZKEcVTK9pCKkk3ZczNNN10vue4v'),
            'account_name'   => 'zhengwei@zeenovel.com',
            'id'             => 2
        ]
    ]
];

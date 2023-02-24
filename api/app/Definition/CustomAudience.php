<?php

namespace App\Definition;

class CustomAudience
{
    const OPR_IN = 'IN';
    const OPR_NOT_EQUAL = 'NOT_EQUAL';
    const OPR_EQUAL = 'EQUAL';
    const OPR_CONTAIN = 'CONTAIN';
    const FILTERING = [
        [
            'field'    => ['key' => 'name_or_id', 'name' => '按名称或受众编号搜索'],
            'operator' => [self::OPR_CONTAIN],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'quicklook', 'name' => 'Status'],
            'operator' => [self::OPR_IN],
            'type'     => 'array',
            'default'  => [
                ['key' => 'recently_used', 'name' => '正在用于广告'],
                ['key' => 'action_needed', 'name' => '近期使用'],
                ['key' => 'shared', 'name' => '共享受众'],
                ['key' => 'in_active_ads', 'name' => '需要执行操作'],
            ]
        ],
        [
            'field'    => ['key' => 'subtype', 'name' => 'Type'],
            'operator' => [self::OPR_NOT_EQUAL, self::OPR_EQUAL],
            'type'     => 'array',
            'default'  => [
                ['key' => 'LOOKALIKE', 'name' => '自定义受众'],
                ['key' => 'LOOKALIKE', 'name' => '类似受众'],
                ['key' => 'LOOKALIKE', 'name' => '保存的受众'],
            ]
        ],
        [
            'field'    => ['key' => 'operation_status.code', 'name' => 'Availability'],
            'operator' => [self::OPR_IN],
            'type'     => 'array',
            'default'  => [
                ['key' => '0,200,411,413,412,450,441,300', 'name' => '可用'],
                ['key' => '434', 'name' => '不可用'],
                ['key' => '432,433,470,500,100,442,423,422,421,431,400', 'name' => '有错误'],
            ]
        ],
        [
            'field'    => ['key' => 'data_source.subtype', 'name' => 'Source'],
            'operator' => [self::OPR_IN],
            'type'     => 'array',
            'default'  => [
                ['key' => '2001,2005', 'name' => '网站'],
                ['key' => '1001,1002,1003,1004,1005,1006', 'name' => '客户名单'],
                ['key' => '3002', 'name' => '公共主页'],
                ['key' => '2011', 'name' => 'Instagram 业务主页'],
                ['key' => '2007', 'name' => '线下事件'],
                ['key' => '2014', 'name' => 'Facebook 活动'],
                ['key' => '2002,2003', 'name' => '移动应用']
            ]
        ],
        [
            'field'    => ['key' => 'rule', 'name' => 'Source'],
            'operator' => [self::OPR_IN],
            'type'     => 'array',
            'default'  => [
                ['key' => 'video_', 'name' => '视频'],
                ['key' => 'lead_generation_', 'name' => '线索广告'],
            ]
        ],
    ];
}

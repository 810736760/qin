<?php

namespace App\Definition;

class Filter
{
    const OPR_GREATER_THAN = 'GREATER_THAN';
    const OPR_GREATER_THAN_AND_EQUAL = 'GREATER_THAN_AND_EQUAL';
    const OPR_LESS_THAN = 'LESS_THAN';
    const OPR_LESS_THAN_AND_EQUAL = 'LESS_THAN_AND_EQUAL';
    const OPR_IN_RANGE = 'IN_RANGE';
    const OPR_NOT_IN_RANGE = 'NOT_IN_RANGE';
    const OPR_IN = 'IN';
    const OPR_NOT_IN = 'NOT_IN';
    const OPR_ANY = 'ANY';
    const OPR_ALL = 'ALL';
    const OPR_NONE = 'NONE';
    const OPR_NOT_CONTAIN = 'NOT_CONTAIN';
    const OPR_CONTAIN = 'CONTAIN';

    const OPR_UP = 'UP';
    const OPR_DOWN = 'DOWN';
    const OPR_UP_TO = 'UP_TO';
    const OPR_DOWN_TO = 'DOWN_TO';

    const BASE_OPR = [
        self::OPR_GREATER_THAN           => '>',
        self::OPR_GREATER_THAN_AND_EQUAL => '>=',
        self::OPR_LESS_THAN              => '<',
        self::OPR_LESS_THAN_AND_EQUAL    => '<=',
    ];

    const RULE_OPR_MAP = [
        'greater' => self::OPR_GREATER_THAN_AND_EQUAL,
        'less'    => self::OPR_LESS_THAN_AND_EQUAL
    ];


    // 广告报告层级 过滤条件
    const REPORT_CONF = [
        [
            'field'    => ['key' => 'campaign_name', 'name' => '广告系列名称'],
            'operator' => [self::OPR_CONTAIN, self::OPR_NOT_CONTAIN],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'adset_name', 'name' => '广告组名称'],
            'operator' => [self::OPR_CONTAIN, self::OPR_NOT_CONTAIN],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'ad_name', 'name' => '广告名称'],
            'operator' => [self::OPR_CONTAIN, self::OPR_NOT_CONTAIN],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'campaign_id', 'name' => '广告系列编号'],
            'operator' => [self::OPR_IN, self::OPR_NOT_IN],
            'type'     => 'array',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'adset_id', 'name' => '广告组编号'],
            'operator' => [self::OPR_IN, self::OPR_NOT_IN],
            'type'     => 'array',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'ad_id', 'name' => '广告编号'],
            'operator' => [self::OPR_IN, self::OPR_NOT_IN],
            'type'     => 'array',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'campaign_delivery_status', 'name' => '广告系列投放状态'],
            'operator' => [self::OPR_IN, self::OPR_NOT_IN],
            'type'     => 'array',
            'default'  => [
                ['key' => 'active', 'name' => '投放中'],
                ['key' => 'deleted', 'name' => '已删除'],
                ['key' => 'error', 'name' => '错误'],
                ['key' => 'inactive', 'name' => '已暂停'],
                ['key' => 'off', 'name' => '已关闭'],
                ['key' => 'pending', 'name' => '审核中']
            ]
        ],
        [
            'field'    => ['key' => 'adset_delivery_status', 'name' => '广告组投放状态'],
            'operator' => [self::OPR_IN, self::OPR_NOT_IN],
            'type'     => 'array',
            'default'  => [
                ['key' => 'active', 'name' => '投放中'],
                ['key' => 'deleted', 'name' => '已删除'],
                ['key' => 'error', 'name' => '错误'],
                ['key' => 'inactive', 'name' => '已暂停'],
                ['key' => 'off', 'name' => '已关闭'],
                ['key' => 'pending', 'name' => '审核中']
            ]
        ],
        [
            'field'    => ['key' => 'ad_delivery_status', 'name' => '广告投放状态'],
            'operator' => [self::OPR_IN, self::OPR_NOT_IN],
            'type'     => 'array',
            'default'  => [
                ['key' => 'active', 'name' => '投放中'],
                ['key' => 'deleted', 'name' => '已删除'],
                ['key' => 'error', 'name' => '错误'],
                ['key' => 'inactive', 'name' => '已暂停'],
                ['key' => 'off', 'name' => '已关闭'],
                ['key' => 'pending', 'name' => '审核中']
            ]
        ],
        [
            'field'    => ['key' => 'objective', 'name' => '营销目标'],
            'operator' => [self::OPR_IN, self::OPR_NOT_IN],
            'type'     => 'array',
            'default'  => [
                ['key' => 'STORE_VISITS', 'name' => '店铺客流量'],
                ['key' => 'REACH', 'name' => '覆盖人数'],
                ['key' => 'EVENT_RESPONSES', 'name' => '活动响应'],
                ['key' => 'LINK_CLICKS', 'name' => '流量'],
                ['key' => 'PRODUCT_CATALOG_SALES', 'name' => '目录促销'],
                ['key' => 'BRAND_AWARENESS', 'name' => '品牌知名度'],
                ['key' => 'LEAD_GENERATION', 'name' => '潜在客户开发'],
                ['key' => 'VIDEO_VIEWS', 'name' => '视频观看量'],
                ['key' => 'POST_ENGAGEMENT', 'name' => '帖文互动'],
                ['key' => 'MESSAGES', 'name' => '消息互动量'],
                ['key' => 'MOBILE_APP_INSTALLS', 'name' => '移动应用安装量'],
                ['key' => 'MOBILE_APP_ENGAGEMENT', 'name' => '移动应用使用率'],
                ['key' => 'APP_INSTALLS', 'name' => '应用安装量'],
                ['key' => 'OFFER_CLAIMS', 'name' => '优惠领取'],
                ['key' => 'PAGE_LIKES', 'name' => '主页赞'],
                ['key' => 'CONVERSIONS', 'name' => '转化量'],
                ['key' => 'CANVAS_APP_INSTALLS', 'name' => '桌面应用安装量'],
                ['key' => 'CANVAS_APP_ENGAGEMENT', 'name' => '桌面应用使用率']
            ]
        ],
    ];

    const CONF = [
        [
            'field'    => ['key' => 'campaign.name', 'name' => '广告系列名称'],
            'operator' => [self::OPR_CONTAIN, self::OPR_NOT_CONTAIN],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'adset.name', 'name' => '广告组名称'],
            'operator' => [self::OPR_CONTAIN, self::OPR_NOT_CONTAIN],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'ad.name', 'name' => '广告名称'],
            'operator' => [self::OPR_CONTAIN, self::OPR_NOT_CONTAIN],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'campaign.id', 'name' => '广告系列编号'],
            'operator' => [self::OPR_IN, self::OPR_NOT_IN],
            'type'     => 'array',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'adset.id', 'name' => '广告组编号'],
            'operator' => [self::OPR_IN, self::OPR_NOT_IN],
            'type'     => 'array',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'ad.id', 'name' => '广告编号'],
            'operator' => [self::OPR_IN, self::OPR_NOT_IN],
            'type'     => 'array',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'campaign.delivery_status', 'name' => '广告系列投放状态'],
            'operator' => [self::OPR_IN, self::OPR_NOT_IN],
            'type'     => 'array',
            'default'  => [
                ['key' => 'active', 'name' => '投放中'],
                ['key' => 'deleted', 'name' => '已删除'],
                ['key' => 'error', 'name' => '错误'],
                ['key' => 'inactive', 'name' => '已暂停'],
                ['key' => 'off', 'name' => '已关闭'],
                ['key' => 'pending', 'name' => '审核中']
            ]
        ],
        [
            'field'    => ['key' => 'adset.delivery_status', 'name' => '广告组投放状态'],
            'operator' => [self::OPR_IN, self::OPR_NOT_IN],
            'type'     => 'array',
            'default'  => [
                ['key' => 'active', 'name' => '投放中'],
                ['key' => 'deleted', 'name' => '已删除'],
                ['key' => 'error', 'name' => '错误'],
                ['key' => 'inactive', 'name' => '已暂停'],
                ['key' => 'off', 'name' => '已关闭'],
                ['key' => 'pending', 'name' => '审核中']
            ]
        ],
        [
            'field'    => ['key' => 'ad.delivery_status', 'name' => '广告投放状态'],
            'operator' => [self::OPR_IN, self::OPR_NOT_IN],
            'type'     => 'array',
            'default'  => [
                ['key' => 'active', 'name' => '投放中'],
                ['key' => 'deleted', 'name' => '已删除'],
                ['key' => 'error', 'name' => '错误'],
                ['key' => 'inactive', 'name' => '已暂停'],
                ['key' => 'off', 'name' => '已关闭'],
                ['key' => 'pending', 'name' => '审核中']
            ]
        ],
        [
            'field'    => ['key' => 'campaign.objective', 'name' => '营销目标'],
            'operator' => [self::OPR_IN, self::OPR_NOT_IN],
            'type'     => 'array',
            'default'  => [
                ['key' => 'LINK_CLICKS', 'name' => '流量'],
                ['key' => 'CONVERSIONS', 'name' => '转化量'],
                ['key' => 'POST_ENGAGEMENT', 'name' => '帖文互动'],
                ['key' => 'PAGE_LIKES', 'name' => '主页赞'],
                ['key' => 'MOBILE_APP_INSTALLS', 'name' => '移动应用安装量'],
                ['key' => 'MOBILE_APP_ENGAGEMENT', 'name' => '移动应用使用率'],
                ['key' => 'CANVAS_APP_INSTALLS', 'name' => '桌面应用安装量']
            ]
        ],
        [
            'field'    => ['key' => 'adset.placement.page_types', 'name' => '版位'],
            'operator' => [self::OPR_ANY, self::OPR_ALL, self::OPR_NONE],
            'type'     => 'array',
            'default'  => [
                ['key' => 'desktopfeed', 'name' => 'Facebook 动态（桌面版）'],
                ['key' => 'mobilefeed', 'name' => 'Facebook 动态（移动版）'],
                ['key' => 'rightcolumn', 'name' => 'Facebook 右边栏'],
                ['key' => 'mobile-marketplace', 'name' => 'Facebook Marketplace'],
                ['key' => 'instagramstream', 'name' => 'Instagram 动态'],
                ['key' => 'instagramstory', 'name' => 'Instagram 快拍'],
                ['key' => 'mobileexternal', 'name' => 'Audience Network'],
                ['key' => 'messenger_home', 'name' => 'Messenger 收件箱'],
                ['key' => 'messenger_story', 'name' => 'Messenger 快拍'],
                ['key' => 'desktop-instream-video', 'name' => 'Facebook 视频插播位（桌面版）'],
                ['key' => 'mobile-instream-video', 'name' => 'Facebook 视频插播位（移动版）']
            ]
        ],
        [
            'field'    => ['key' => 'campaign.cost_per', 'name' => '单次成效费用（广告系列）'],
            'operator' => [self::OPR_GREATER_THAN, self::OPR_LESS_THAN, self::OPR_IN_RANGE, self::OPR_NOT_IN_RANGE],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'campaign.cpa', 'name' => '单次操作费用（广告系列）'],
            'operator' => [self::OPR_GREATER_THAN, self::OPR_LESS_THAN, self::OPR_IN_RANGE, self::OPR_NOT_IN_RANGE],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'campaign.cpm', 'name' => '千次展示费用（广告系列）'],
            'operator' => [self::OPR_GREATER_THAN, self::OPR_LESS_THAN, self::OPR_IN_RANGE, self::OPR_NOT_IN_RANGE],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'campaign.frequency', 'name' => '频次（广告系列）'],
            'operator' => [self::OPR_GREATER_THAN, self::OPR_LESS_THAN, self::OPR_IN_RANGE, self::OPR_NOT_IN_RANGE],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'campaign.impressions', 'name' => '展示次数（广告系列）'],
            'operator' => [self::OPR_GREATER_THAN, self::OPR_LESS_THAN, self::OPR_IN_RANGE, self::OPR_NOT_IN_RANGE],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'campaign.lifetime_spent', 'name' => '总花费（广告系列）'],
            'operator' => [self::OPR_GREATER_THAN, self::OPR_LESS_THAN, self::OPR_IN_RANGE, self::OPR_NOT_IN_RANGE],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'campaign.reach', 'name' => '覆盖人数（广告系列）'],
            'operator' => [self::OPR_GREATER_THAN, self::OPR_LESS_THAN, self::OPR_IN_RANGE, self::OPR_NOT_IN_RANGE],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'adset.cost_per', 'name' => '单次成效费用（广告组）'],
            'operator' => [self::OPR_GREATER_THAN, self::OPR_LESS_THAN, self::OPR_IN_RANGE, self::OPR_NOT_IN_RANGE],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'adset.cpa', 'name' => '单次操作费用（广告组）'],
            'operator' => [self::OPR_GREATER_THAN, self::OPR_LESS_THAN, self::OPR_IN_RANGE, self::OPR_NOT_IN_RANGE],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'adset.cpm', 'name' => '千次展示费用（广告组）'],
            'operator' => [self::OPR_GREATER_THAN, self::OPR_LESS_THAN, self::OPR_IN_RANGE, self::OPR_NOT_IN_RANGE],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'adset.frequency', 'name' => '频次（广告组）'],
            'operator' => [self::OPR_GREATER_THAN, self::OPR_LESS_THAN, self::OPR_IN_RANGE, self::OPR_NOT_IN_RANGE],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'adset.impressions', 'name' => '展示次数（广告组）'],
            'operator' => [self::OPR_GREATER_THAN, self::OPR_LESS_THAN, self::OPR_IN_RANGE, self::OPR_NOT_IN_RANGE],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'adset.lifetime_spent', 'name' => '总花费（广告组）'],
            'operator' => [self::OPR_GREATER_THAN, self::OPR_LESS_THAN, self::OPR_IN_RANGE, self::OPR_NOT_IN_RANGE],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'adset.reach', 'name' => '覆盖人数（广告组）'],
            'operator' => [self::OPR_GREATER_THAN, self::OPR_LESS_THAN, self::OPR_IN_RANGE, self::OPR_NOT_IN_RANGE],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'ad.cost_per', 'name' => '单次成效费用（广告）'],
            'operator' => [self::OPR_GREATER_THAN, self::OPR_LESS_THAN, self::OPR_IN_RANGE, self::OPR_NOT_IN_RANGE],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'ad.cpa', 'name' => '单次操作费用（广告）'],
            'operator' => [self::OPR_GREATER_THAN, self::OPR_LESS_THAN, self::OPR_IN_RANGE, self::OPR_NOT_IN_RANGE],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'ad.cpm', 'name' => '千次展示费用（广告）'],
            'operator' => [self::OPR_GREATER_THAN, self::OPR_LESS_THAN, self::OPR_IN_RANGE, self::OPR_NOT_IN_RANGE],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'ad.frequency', 'name' => '频次（广告）'],
            'operator' => [self::OPR_GREATER_THAN, self::OPR_LESS_THAN, self::OPR_IN_RANGE, self::OPR_NOT_IN_RANGE],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'ad.impressions', 'name' => '展示次数（广告）'],
            'operator' => [self::OPR_GREATER_THAN, self::OPR_LESS_THAN, self::OPR_IN_RANGE, self::OPR_NOT_IN_RANGE],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'ad.lifetime_spent', 'name' => '总花费（广告）'],
            'operator' => [self::OPR_GREATER_THAN, self::OPR_LESS_THAN, self::OPR_IN_RANGE, self::OPR_NOT_IN_RANGE],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'ad.reach', 'name' => '覆盖人数（广告）'],
            'operator' => [self::OPR_GREATER_THAN, self::OPR_LESS_THAN, self::OPR_IN_RANGE, self::OPR_NOT_IN_RANGE],
            'type'     => 'string',
            'default'  => []
        ],
        [
            'field'    => ['key' => 'adset.delivery_age', 'name' => '受众年龄'],
            'operator' => [self::OPR_NONE, self::OPR_ANY],
            'type'     => 'array',
            'default'  => [
                ['key' => '13-17', 'name' => '13-17'],
                ['key' => '18-24', 'name' => '18-24'],
                ['key' => '25-34', 'name' => '25-34'],
                ['key' => '35-44', 'name' => '35-44'],
                ['key' => '45-54', 'name' => '45-54'],
                ['key' => '55-64', 'name' => '55-64'],
                ['key' => '>64', 'name' => '65+'],
            ]
        ],
        [
            'field'    => ['key' => 'adset.targeting_state', 'name' => '受众如何影响广告投放'],
            'operator' => [self::OPR_IN, self::OPR_NOT_IN],
            'type'     => 'array',
            'default'  => [
                ['key' => 'deprecating', 'name' => '将受影响'],
                ['key' => 'delivery_affected', 'name' => '目前已受影响'],
                ['key' => 'delivery_paused', 'name' => '未投放'],
            ]
        ],
        [
            'field'    => ['key' => 'adset.delivery_gender', 'name' => '受众性别'],
            'operator' => [self::OPR_NONE, self::OPR_ANY],
            'type'     => 'array',
            'default'  => [
                ['key' => 'female', 'name' => '女'],
                ['key' => 'male', 'name' => '男'],
                ['key' => 'unknown', 'name' => '未分类'],
            ]
        ],
    ];
}

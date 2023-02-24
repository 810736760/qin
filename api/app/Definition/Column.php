<?php

namespace App\Definition;

class Column
{
    const COLUMN_NAME_MAP = [
        '成效'       => [
            'c1',
            'c62',
            'c2',
            'c3',
            'c4',
            'c39',
            'c5',
            'c6',
            'c72',
            'c7',
            'c8',
            'c71',
            'c230',
            'c231',
            'c233',
            'c120',
            'c122',
            'c180',
            'c181',
        ],
        '费用'       => [
            'c9',
            'c10',
            'c11',
        ],
        '指标'       => [
            'c59',
            'd59',
            'c40',
            'c41',
            'c60',
            'c33',
            'c61',
            //   'c42' => '应用启用价值',
            'c43',
            'c34',
            'c35',
            'c36',
            'c63',
            'd63',
            'c220',
            'd220',
            'c219',
            'd219',
            'c14',
            'c65',
            'c66',
        ],
        '对象名称和编号'  => [
            'c44',
            'c45',
            'c46',
            'c47',
            'c48',
            'c49'
        ],
        '状态和日期'    => [
            'c50',
            'c51'
        ],
        '目标、预算和排期' => [
            'c52',
            'c53',
            'c54',
            'c55'
        ],
        '优化'       => [
            'c56',
            'c57',
            'c58'
        ],
    ];

    const CUSTOMIZE = ['b0', 'b1', 'b2', 'c52', 'c53', 'c54', 'c55', 'c39', 'c64', 'c50', 'c45', 'c46'];

    //  广告素材的列
    const CREATIVE_ITEM = ['b2'];

    // 需要更高权限查询
    const SPECIAL_ITEM = ['c1', 'c56', 'c57', 'c62'];

    // 重点统计列
    const STAT_ITEM = ["c1", "c3", "c2", "c4", "c11", "c6", "c7", "c8", "c9", "c5", "c35", "c36", "c40", "c63"];

    const DEFAULT_COLUMN = 'c39,c53,c5,c71,c63,c149,c4,c11,c6,c224,c72,c35,c36,c230';

    // 给前端特殊展示
    const SHOW_NAME = [
        'b0' => 'name',
        'b1' => 'status',
        'b2' => 'thumbnail_url'
    ];

    // af双数据单独展示
    const AF_SHOW = [
        'c63', 'c35'
    ];

    // 普通查询 不需要更高级的权限
    const NORMAL_ITEM = [
        "c5", "c63", "c35", "c6", "c4"
    ];

    // 后台自定义成效栏
    const BK_FIELD = 'bkCustom';

    // 一些“率”的计算公式 列C59 = c63/c5*100
    const CALC_RATE_MAP = [
        'c59'  => ['c63', 'c5', 100], // ROI
        'c93'  => ['c63', 'c5', 100], // ROI
        'd59'  => ['d63', 'c5', 100], // ROI
        'c36'  => ['c5', 'c35'], // CPI
        'c67'  => ['c5', 'c35'], // CPI
        'c72'  => ['c6', 'c4', 100], // 点击率
        'c68'  => ['c71', 'c35', 100], //充值转化率
        'c69'  => ['c35', 'c6', 100], //点击安装转化率
        'c73'  => ['c35', 'c4', 100], //展示安装转化率
        'c70'  => ['c5', 'c4', 1000], // CPM
        'c11'  => ['c5', 'c4', 1000], // CPM
        'c8'   => ['c6', 'c4', 100], // 点击率
        'c91'  => ['c6', 'c4', 100], // 点击率
        'c149' => ['c5', 'c71'],// 单次购物费用,
        'c228' => ['c4', 'c2'],// 单人展示次数,
        'c229' => ['c35', 'c6'],// CVR,
        'c233' => ['c5', 'c230'],// 购物独立成本,
        'c94'  => ['c215', 'c35'] //付费率，
    ];


    const COLUMN_NAME = [
        "c1"   => "成效",
        "c2"   => "覆盖人数",
        "c3"   => "频次",
        "c4"   => "展示次数",
        "c5"   => "花费金额",
        "c6"   => "点击量",
        "c7"   => "CPC",
        "c8"   => "CTR",
        "c9"   => "单次成效费用",
        "c10"  => "千次覆盖费用",
        "c11"  => "千次展示费用",
        "c14"  => "贴文互动",
        "c33"  => "启用应用的独立用户人数",
        "c34"  => "应用启用独立操作费用",
        "c35"  => "应用安装",
        "c36"  => "应用安装量（费用）",
        "c39"  => "投放状态",
        "c40"  => "ROAS-手机",
        "c41"  => "应用启用",
        "c43"  => "应用启用费用",
        "c44"  => "广告系列名称",
        "c45"  => "广告系列编号",
        "c46"  => "广告组名称",
        "c47"  => "广告组编号",
        "c48"  => "广告编号",
        "c49"  => "广告名称",
        "c50"  => "创建时间",
        "c51"  => "上次编辑",
        "c52"  => "竞争策略",
        "c53"  => "预算",
        "c54"  => "剩余预算",
        "c55"  => "排期",
        "c56"  => "优化事件",
        "c57"  => "单次优化事件费用",
        "c58"  => "上次重大修改",
        "c59"  => "ROAS",
        "d59"  => "首日Roi",
        "c60"  => "移动应用会话",
        "c61"  => "移动应用会话-独立用户",
        "c62"  => "成效获得率",
        "c63"  => "购物转化价值",
        "d63"  => "购物转化价值(首日)",
        "c65"  => "互动率",
        "c66"  => "发起结账",
        "c67"  => "CPI",
        "c68"  => "充值转化率",
        "c69"  => "【点击】安装转换率",
        "c70"  => "CPM",
        "c71"  => "购买",
        "c72"  => "链接点击率",
        "c73"  => "【展示】安装转换率",
        "c74"  => "帐户名称",
        "c75"  => "帐户编号",
        "c76"  => "日期",
        "c77"  => "周",
        "c78"  => "2 周",
        "c79"  => "月份",
        "c80"  => "年龄",
        "c81"  => "性别",
        "c82"  => "国家/地区",
        "c83"  => "地域",
        "c84"  => "设备",
        "c85"  => "平台",
        "c86"  => "版位",
        "c87"  => "设备平台",
        "c88"  => "投放时间（广告帐户时区）",
        "c89"  => "转化设备",
        "c90"  => "视频观看类型",
        "c91"  => "CTR",
        "c92"  => "CVR",
        "c93"  => "ROI",
        "c94"  => "付费率",
        "c95"  => "总展示次数（包括非人为访问的无效展示次数）",
        "c96"  => "自动刷新展示次数",
        "c97"  => "广告投放",
        "c98"  => "广告组投放",
        "c99"  => "广告系列投放",
        "c100" => "内容浏览",
        "c101" => "单次内容查看费用",
        "c102" => "内容浏览转化价值",
        "c103" => "加入心愿单",
        "c104" => "单次加入心愿单费用",
        "c105" => "“加入心愿单”转化价值",
        "c106" => "加入购物车",
        "c107" => "单次加入购物车费用",
        "c108" => "加入购物车转化价值",
        "c109" => "单次发起结账费用",
        "c110" => "发起结账转化价值",
        "c111" => "Facebook 站内流程完成",
        "c112" => "单次 Facebook 站内流程完成费用",
        "c113" => "Facebook 站内流程完成转化价值",
        "c114" => "完成关卡",
        "c115" => "单次完成关卡费用",
        "c116" => "完成关卡转化价值",
        "c117" => "完成教程学习",
        "c118" => "单次完成教程学习费用",
        "c119" => "完成教程学习转化价值",
        "c120" => "完成注册",
        "c121" => "单次完成注册费用",
        "c122" => "注册完成转化价值",
        "c123" => "应用启用转化价值",
        "c124" => "成就解锁",
        "c125" => "单次成就解锁费用",
        "c126" => "成就解锁转化价值",
        "c127" => "点数花费",
        "c128" => "点数花费单次费用",
        "c129" => "点数花费转化价值",
        "c130" => "提交评分",
        "c131" => "单次提交评分费用",
        "c132" => "提交评分转化价值",
        "c133" => "搜索",
        "c134" => "单次搜索费用",
        "c135" => "搜索转化价值",
        "c136" => "添加支付信息",
        "c137" => "单次添加支付信息费用",
        "c138" => "添加支付信息转化价值",
        "c139" => "潜在客户",
        "c140" => "潜在客户单次转化费用",
        "c141" => "潜在客户转化价值",
        "c142" => "线下其他转化",
        "c143" => "单次其他线下转化费用",
        "c144" => "线下其他转化价值",
        "c145" => "自定义事件",
        "c146" => "单次自定义事件费用",
        "c147" => "落地页浏览量",
        "c148" => "落地页单次浏览费用",
        "c149" => "单次购物费用",
        "c150" => "移动应用内容查看",
        "c151" => "网站内容查看",
        "c152" => "线下内容查看",
        "c153" => "Facebook 站内内容查看",
        "c154" => "移动应用内容查看转化价值",
        "c155" => "网站内容查看转化价值",
        "c156" => "线下内容查看转化价值",
        "c157" => "移动应用加入心愿单",
        "c158" => "网站加入心愿单",
        "c159" => "线下加入心愿单",
        "c160" => "移动应用加入心愿单转化价值",
        "c161" => "网站加入心愿单转化价值",
        "c162" => "线下加入心愿单转化价值",
        "c163" => "移动应用加入购物车",
        "c164" => "网站加入购物车",
        "c165" => "线下加入购物车",
        "c166" => "Facebook 站内加入购物车次数",
        "c167" => "移动应用加入购物车转化价值",
        "c168" => "网站加入购物车转化价值",
        "c169" => "线下加入购物车转化价值",
        "c170" => "移动应用发起结账",
        "c171" => "网站发起结账",
        "c172" => "线下发起结账",
        "c173" => "移动应用发起结账转化价值",
        "c174" => "发起结账转化价值（网站）",
        "c175" => "线下发起结账转化价值",
        "c176" => "移动应用关卡完成",
        "c177" => "“移动应用关卡完成”转化价值",
        "c178" => "移动应用完成教程学习",
        "c179" => "移动应用完成教程转化价值",
        "c180" => "移动应用完成注册",
        "c181" => "网站注册完成次数",
        "c182" => "线下完成注册",
        "c183" => "移动应用完成注册转化价值",
        "c184" => "网站注册完成转化价值",
        "c185" => "线下注册完成转化价值",
        "c186" => "移动应用会话转化价值",
        "c187" => "移动应用安装",
        "c188" => "桌面应用安装量",
        "c189" => "移动应用成就解锁",
        "c190" => "移动应用成就解锁转化价值",
        "c191" => "移动应用点数花费",
        "c192" => "桌面应用点数花费",
        "c193" => "移动应用点数花费转化价值",
        "c194" => "桌面应用点数花费转化价值",
        "c195" => "移动应用评分提交",
        "c196" => "移动应用评分提交转化价值",
        "c197" => "移动应用搜索",
        "c198" => "网站搜索",
        "c199" => "线下搜索",
        "c200" => "移动应用搜索转化价值",
        "c201" => "网站搜索转化价值",
        "c202" => "线下搜索转化价值",
        "c203" => "移动应用添加支付信息",
        "c204" => "网站添加支付信息",
        "c205" => "线下添加支付信息",
        "c206" => "移动应用支付信息添加转化价值",
        "c207" => "网站添加支付信息转化价值",
        "c208" => "线下添加支付信息转化价值",
        "c209" => "网站潜在客户",
        "c210" => "线下潜在客户",
        "c211" => "Facebook 站内线索",
        "c212" => "网站潜在客户转化价值",
        "c213" => "线下潜在客户转化价值",
        "c214" => "移动应用自定义事件",
        "c215" => "移动应用",
        "c216" => "网站购物",
        "c217" => "线下购物",
        "c218" => "Facebook 站内购物",
        "c219" => "移动应用购物转化价值",
        "d219" => "移动应用购物转化价值(首日)",
        "c220" => "网站购物转化价值",
        "d220" => "网站购物转化价值(首日)",
        "c221" => "线下购物转化价值",
        "d221" => "线下购物转化价值(首日)",
        "c222" => "Facebook 站内购买转化价值",
        "c223" => '链接点击',
        'c224' => '单次链接点击费用',
        'c225' => '主页ID',
        'c226' => '次留', // 自定义成效
        'c227' => '7留',// 自定义成效
        'c228' => '单人展示次数',// 自定义成效
        'c229' => 'CVR',// CVR
        'c230' => '购物 - 独立用户',
        'c231' => '购物 - 独立用户(首日)',
        'c232' => '应用安装',
        'c233' => '购物 - 独立用户成本',
    ];

    const COLUMN_MAP = [
        'b0'   => 'name',
        'b1'   => 'status',
        'b2'   => 'creative:thumbnail_url',
        'c1'   => 'results',
        'c2'   => 'reach',
        'c3'   => 'frequency',
        'c4'   => 'impressions',
        'c5'   => 'spend',
        'c6'   => 'clicks',
        'c7'   => 'cpc',
        'c8'   => 'ctr',
        'c9'   => 'cost_per_action_type:purchase',
        'c10'  => 'cpp',
        'c11'  => 'cpm',
        'c12'  => 'actions:page_engagement',
        'c13'  => 'actions:comment',
        'c14'  => 'actions:post_engagement',
        'c15'  => 'actions:post_reaction',
        'c16'  => 'actions:onsite_conversion.post_save',
        'c17'  => 'actions:post',
        'c18'  => 'inline_link_clicks',
        'c19'  => 'unique_inline_link_clicks',
        'c20'  => 'outbound_clicks',
        'c21'  => 'unique_outbound_clicks',
        'c22'  => 'inline_link_click_ctr',
        'c23'  => 'Unique CTR (link click-through rate)',
        'c24'  => 'outbound_clicks_ctr',
        'c25'  => 'unique_outbound_clicks_ctr',
        'c26'  => 'unique_clicks',
        'c27'  => 'unique_ctr',
        'c28'  => 'cost_per_inline_link_click',
        'c29'  => 'cost_per_unique_action_type',
        'c30'  => 'cost_per_outbound_click',
        'c31'  => 'cost_per_unique_outbound_click',
        'c32'  => 'cost_per_unique_click',
        'c33'  => 'unique_actions:omni_activate_app',
        'c34'  => 'cost_per_unique_action_type:omni_activate_app',
        'c35'  => 'actions:mobile_app_install',
        'c36'  => 'cost_per_action_type:omni_app_install',
        'c37'  => 'unique_actions:omni_initiated_checkout',
        'c38'  => 'cost_per_unique_action_type:omni_initiated_checkout',
        'c39'  => 'effective_status',
        'c40'  => 'mobile_app_purchase_roas:mobile_app_purchase_roas',
        'c41'  => 'actions:omni_activate_app',
        'c43'  => 'cost_per_action_type:omni_activate_app',
        'c44'  => 'campaign_name',
        'c45'  => 'campaign_id',
        'c46'  => 'adset_name',
        'c47'  => 'adset_id',
        'c48'  => 'ad_id',
        'c49'  => 'ad_name',
        'c50'  => 'created_time',
        'c51'  => 'updated_time',
        'c52'  => 'bid_strategy',
        'c53'  => 'budget',
        'c55'  => 'start_time',
        'c54'  => 'budget_remaining',
        'c56'  => 'optimization_results',
        'c57'  => 'cost_per_optimization_result',
        'c58'  => 'last_significant_edit',
        'c59'  => 'purchase_roas:omni_purchase',
        'd59'  => 'purchase_roas:omni_purchase',
        'c60'  => 'actions:app_custom_event.fb_mobile_activate_app',
        'c61'  => 'unique_actions:app_custom_event.fb_mobile_activate_app',
        'c62'  => 'result_rate',
        'c63'  => 'action_values:omni_purchase', // 指标改动由原来的购物转化价值=>移动*
        'd63'  => 'action_values:omni_purchase@1d_click', // 指标改动由原来的购物转化价值=>移动*
        'c64'  => 'delivery_info',
        'c65'  => 'quality_score_ectr',
        'c66'  => 'actions:omni_initiated_checkout',
        // 'c67'  => 'custom_derived_metrics:170252447787467',
        // 'c68'  => 'custom_derived_metrics:196027468547297',
        // 'c69'  => 'custom_derived_metrics:169077291238316',
        // 'c70'  => 'custom_derived_metrics:172626970883348',
        'c71'  => 'actions:omni_purchase',
        'c72'  => 'website_ctr:link_click',
        // 'c73'  => 'custom_derived_metrics:172378401230871',
        "c74"  => "account_name",
        "c75"  => "account_id",
        "c76"  => "days_1",
        "c77"  => "days_7",
        "c78"  => "days_14",
        "c79"  => "monthly",
        "c80"  => "age",
        "c81"  => "gender",
        "c82"  => "country",
        "c83"  => "region",
        "c84"  => "impression_device",
        "c85"  => "publisher_platform",
        "c86"  => "device_platform",
        "c87"  => "platform_position",
        "c88"  => "hourly_stats_aggregated_by_advertiser_time_zone",
        "c89"  => "action_device",
        "c90"  => "action_video_type",
        // "c91"  => "custom_derived_metrics:162180568917321",
        // "c92"  => "custom_derived_metrics:237683521367025",
        // "c93"  => "custom_derived_metrics:309990111134298",
        // "c94"  => "custom_derived_metrics:198421745326682",
        "c95"  => "impressions_gross",
        "c96"  => "impressions_auto_refresh",
        "c97"  => "ad_delivery",
        "c98"  => "adset_delivery",
        "c99"  => "campaign_delivery",
        "c100" => "actions:omni_view_content",
        "c101" => "cost_per_action_type:omni_view_content",
        "c102" => "action_values:omni_view_content",
        "c103" => "actions:add_to_wishlist",
        "c104" => "cost_per_action_type:add_to_wishlist",
        "c105" => "action_values:add_to_wishlist",
        "c106" => "actions:omni_add_to_cart",
        "c107" => "cost_per_action_type:omni_add_to_cart",
        "c108" => "action_values:omni_add_to_cart",
        "c109" => "cost_per_action_type:omni_initiated_checkout",
        "c110" => "action_values:omni_initiated_checkout",
        "c111" => "actions:onsite_conversion.flow_complete",
        "c112" => "cost_per_action_type:onsite_conversion.flow_complete",
        "c113" => "action_values:onsite_conversion.flow_complete",
        "c114" => "actions:omni_level_achieved",
        "c115" => "cost_per_action_type:omni_level_achieved",
        "c116" => "action_values:omni_level_achieved",
        "c117" => "actions:omni_tutorial_completion",
        "c118" => "cost_per_action_type:omni_tutorial_completion",
        "c119" => "action_values:omni_tutorial_completion",
        "c120" => "actions:omni_complete_registration",
        "c121" => "cost_per_action_type:omni_complete_registration",
        "c122" => "action_values:omni_complete_registration",
        "c123" => "action_values:omni_activate_app",
        "c124" => "actions:omni_achievement_unlocked",
        "c125" => "cost_per_action_type:omni_achievement_unlocked",
        "c126" => "action_values:omni_achievement_unlocked",
        "c127" => "actions:omni_spend_credits",
        "c128" => "cost_per_action_type:omni_spend_credits",
        "c129" => "action_values:omni_spend_credits",
        "c130" => "actions:omni_rate",
        "c131" => "cost_per_action_type:omni_rate",
        "c132" => "action_values:omni_rate",
        "c133" => "actions:omni_search",
        "c134" => "cost_per_action_type:omni_search",
        "c135" => "action_values:omni_search",
        "c136" => "actions:add_payment_info",
        "c137" => "cost_per_action_type:add_payment_info",
        "c138" => "action_values:add_payment_info",
        "c139" => "actions:lead",
        "c140" => "cost_per_action_type:lead",
        "c141" => "action_values:lead",
        "c142" => "actions:offline_conversion.other",
        "c143" => "cost_per_action_type:offline_conversion.other",
        "c144" => "action_values:offline_conversion.other",
        "c145" => "actions:omni_custom",
        "c146" => "cost_per_action_type:omni_custom",
        "c147" => "actions:landing_page_view",
        "c148" => "cost_per_action_type:landing_page_view",
        "c149" => "cost_per_action_type:omni_purchase",
        "c150" => "actions:app_custom_event.fb_mobile_content_view",
        "c151" => "actions:offsite_conversion.fb_pixel_view_content",
        "c152" => "actions:offline_conversion.view_content",
        "c153" => "actions:onsite_conversion.view_content",
        "c154" => "action_values:app_custom_event.fb_mobile_content_view",
        "c155" => "action_values:offsite_conversion.fb_pixel_view_content",
        "c156" => "action_values:offline_conversion.view_content",
        "c157" => "actions:app_custom_event.fb_mobile_add_to_wishlist",
        "c158" => "actions:offsite_conversion.fb_pixel_add_to_wishlist",
        "c159" => "actions:offline_conversion.add_to_wishlist",
        "c160" => "action_values:app_custom_event.fb_mobile_add_to_wishlist",
        "c161" => "action_values:offsite_conversion.fb_pixel_add_to_wishlist",
        "c162" => "action_values:offline_conversion.add_to_wishlist",
        "c163" => "actions:app_custom_event.fb_mobile_add_to_cart",
        "c164" => "actions:offsite_conversion.fb_pixel_add_to_cart",
        "c165" => "actions:offline_conversion.add_to_cart",
        "c166" => "actions:onsite_conversion.add_to_cart",
        "c167" => "action_values:app_custom_event.fb_mobile_add_to_cart",
        "c168" => "action_values:offsite_conversion.fb_pixel_add_to_cart",
        "c169" => "action_values:offline_conversion.add_to_cart",
        "c170" => "actions:app_custom_event.fb_mobile_initiated_checkout",
        "c171" => "actions:offsite_conversion.fb_pixel_initiate_checkout",
        "c172" => "actions:offline_conversion.initiate_checkout",
        "c173" => "action_values:app_custom_event.fb_mobile_initiated_checkout",
        "c174" => "action_values:offsite_conversion.fb_pixel_initiate_checkout",
        "c175" => "action_values:offline_conversion.initiate_checkout",
        "c176" => "actions:app_custom_event.fb_mobile_level_achieved",
        "c177" => "action_values:app_custom_event.fb_mobile_level_achieved",
        "c178" => "actions:app_custom_event.fb_mobile_tutorial_completion",
        "c179" => "action_values:app_custom_event.fb_mobile_tutorial_completion",
        "c180" => "actions:app_custom_event.fb_mobile_complete_registration",
        "c181" => "actions:offsite_conversion.fb_pixel_complete_registration",
        "c182" => "actions:offline_conversion.complete_registration",
        "c183" => "action_values:app_custom_event.fb_mobile_complete_registration",
        "c184" => "action_values:offsite_conversion.fb_pixel_complete_registration",
        "c185" => "action_values:offline_conversion.complete_registration",
        "c186" => "action_values:app_custom_event.fb_mobile_activate_app",
        "c187" => "actions:mobile_app_install",
        "c188" => "actions:app_install",
        "c189" => "actions:app_custom_event.fb_mobile_achievement_unlocked",
        "c190" => "action_values:app_custom_event.fb_mobile_achievement_unlocked",
        "c191" => "actions:app_custom_event.fb_mobile_spent_credits",
        "c192" => "actions:credit_spent",
        "c193" => "action_values:app_custom_event.fb_mobile_spent_credits",
        "c194" => "action_values:credit_spent",
        "c195" => "actions:app_custom_event.fb_mobile_rate",
        "c196" => "action_values:app_custom_event.fb_mobile_rate",
        "c197" => "actions:app_custom_event.fb_mobile_search",
        "c198" => "actions:offsite_conversion.fb_pixel_search",
        "c199" => "actions:offline_conversion.search",
        "c200" => "action_values:app_custom_event.fb_mobile_search",
        "c201" => "action_values:offsite_conversion.fb_pixel_search",
        "c202" => "action_values:offline_conversion.search",
        "c203" => "actions:app_custom_event.fb_mobile_add_payment_info",
        "c204" => "actions:offsite_conversion.fb_pixel_add_payment_info",
        "c205" => "actions:offline_conversion.add_payment_info",
        "c206" => "action_values:app_custom_event.fb_mobile_add_payment_info",
        "c207" => "action_values:offsite_conversion.fb_pixel_add_payment_info",
        "c208" => "action_values:offline_conversion.add_payment_info",
        "c209" => "actions:offsite_conversion.fb_pixel_lead",
        "c210" => "actions:offline_conversion.lead",
        "c211" => "actions:onsite_conversion.lead_grouped",
        "c212" => "action_values:offsite_conversion.fb_pixel_lead",
        "c213" => "action_values:offline_conversion.lead",
        "c214" => "actions:app_custom_event.other",
        "c215" => "actions:app_custom_event.fb_mobile_purchase",
        "c216" => "actions:offsite_conversion.fb_pixel_purchase",
        "c217" => "actions:offline_conversion.purchase",
        "c218" => "actions:onsite_conversion.purchase",
        "c219" => "action_values:app_custom_event.fb_mobile_purchase",
        "d219" => "action_values:app_custom_event.fb_mobile_purchase@1d_click",
        "c220" => "action_values:offsite_conversion.fb_pixel_purchase",
        "d220" => "action_values:offsite_conversion.fb_pixel_purchase@1d_click",
        "c221" => "action_values:offline_conversion.purchase",
        "d221" => "action_values:offline_conversion.purchase@1d_click",
        "c222" => "action_values:onsite_conversion.purchase",
        'c223' => 'actions:link_click',
        'c224' => 'cost_per_action_type:link_click',
        'c225' => 'page_id',
        'c226' => 'bkCustom:retention_1', // 自定义成效
        'c227' => 'bkCustom:retention_7',// 自定义成效
        'c228' => 'actions:c228',// 自定义成效
        'c229' => 'actions:c229',// CVR

        'c230' => 'unique_actions:omni_purchase',
        'c231' => 'unique_actions:omni_purchase@1d_click',
        'c232' => 'actions:omni_app_install',
        'c233' => 'actions:c233',// 自定义成效
    ];


    const COLUMN_NAME_REPORT_MAP = [
        'dimensions' => [
            '热门细分数据' => [
                "c74",
                "c44",
                "c46",
                "c49",
                "c80",
                "c81",
                "c82",
                "c85",
                "c86",
                "c76",
                "c79",
            ],
            '层级'     => [
                "c74",
                "c44",
                "c46",
                "c49",
                "c75",
                "c45",
                "c47",
                "c48",
            ],
            '时间'     => [
                "c76",
                "c77",
                "c78",
                "c79",

            ],
            '投放'     => [
                "c80",
                "c81",
                "c82",
                "c83",
                "c84",
                "c85",
                "c86",
                "c87",
                "c88",
            ],
            '操作'     => [
                "c89",
                "c90",
            ],
        ],
        'fields'     => [
            '热门指标'  => [
                "c5",
                "c4",
                "c2",
                "c3",
                "c71",
                "c149",
                "c63",
                "c223",
                "c224",
                "c6",
                "c7",
                "c11",
                "c8",
            ],
            '自定义指标' => [
            ],
            '表现'    => [
                "c2",
                "c3",
                "c4",
                "c5",
                "c6",
                "c7",
                "c8",
                "c95",
                "c96",
                "c10",
                "c11",
                "c97",
                "c98",
                "c99",
            ],
            '转化量'   => [
                "c100",
                "c101",
                "c102",
                "c103",
                "c104",
                "c105",
                "c106",
                "c107",
                "c108",
                "c66",
                "c109",
                "c110",
                "c111",
                "c112",
                "c113",
                "c114",
                "c115",
                "c116",
                "c117",
                "c118",
                "c119",
                "c120",
                "c121",
                "c122",
                "c41",
                "c43",
                "c123",
                "c232",
                "c36",
                "c124",
                "c125",
                "c126",
                "c127",
                "c128",
                "c129",
                "c130",
                "c131",
                "c132",
                "c133",
                "c134",
                "c135",
                "c136",
                "c137",
                "c138",
                "c139",
                "c140",
                "c141",
                "c142",
                "c143",
                "c144",
                "c145",
                "c146",
                "c147",
                "c148",
                "c71",
                "c149",
                "c63",
                "c150",
                "c151",
                "c152",
                "c153",
                "c154",
                "c155",
                "c156",
                "c157",
                "c158",
                "c159",
                "c160",
                "c161",
                "c162",
                "c163",
                "c164",
                "c165",
                "c166",
                "c167",
                "c168",
                "c169",
                "c170",
                "c171",
                "c172",
                "c173",
                "c174",
                "c175",
                "c176",
                "c177",
                "c178",
                "c179",
                "c180",
                "c181",
                "c182",
                "c183",
                "c184",
                "c185",
                "c60",
                "c186",
                "c187",
                "c188",
                "c189",
                "c190",
                "c191",
                "c192",
                "c193",
                "c194",
                "c195",
                "c196",
                "c197",
                "c198",
                "c199",
                "c200",
                "c201",
                "c202",
                "c203",
                "c204",
                "c205",
                "c206",
                "c207",
                "c208",
                "c209",
                "c210",
                "c211",
                "c212",
                "c213",
                "c214",
                "c215",
                "c216",
                "c217",
                "c218",
                "c219",
                "c220",
                "c221",
                "c222",
            ]
        ]
    ];
}

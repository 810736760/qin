<?php

namespace App\Definition;

class TikTokColumn
{

    // 细分数据 维度数据
    const DIMENSIONS_LIST = [
        // 'campaign_budget'  => '预算(推广系列)',
//        "ad_id"            => '广告组ID',
//        "diagnosis_status" => '诊断建议',
        "budget" => '预算',
        // "bid_type"              => '出价(广告组)',
//        "schedule"         => '排期(广告组)',
//        "ad_name"          => '广告组名称',
//        "creative_id"      => '创意ID',
//        'campaign_name'    => '推广系列名称',
//        'campaign_id'      => '推广系列ID',
    ];

    // https://ads.tiktok.com/marketing_api/docs?id=1751443967255553
    // 成效数据
    const  METRICS_LIST = [
        "spend" => '总消耗',
        'total_purchase_value'        => '付费总价值',
        'total_complete_payment_rate' => '[网页]支付完成总价值',
        'total_active_pay_roas'       => 'Roi',
        'total_purchase'              => '总购买数',
        'cost_per_total_purchase'     => '每次唯一购买的成本',
        "cpc"                         => 'CPC',
        "cpm"                         => 'CPM',
        "impressions"                 => '展示数',
        "clicks"                      => '点击数',
        "ctr"                         => 'CTR',
        "conversion"                  => '转化数',
        "cost_per_conversion"         => '转化成本',
        "conversion_rate"             => '转化率',
        "result"                      => '成效',
        "cost_per_result"             => '单次成效费用',
        "result_rate"                 => '成效率',
        'real_time_app_install'       => '实时应用安装',
        'real_time_app_install_cost'  => '实时应用安装成本',
        'purchase'                    => '唯一购买',
        'cost_per_purchase'           => '每次唯一购买的成本',
        'purchase_rate'               => '唯一购买率 (%)',
        'complete_payment_roas'       => '[网页]支付完成广告支出回报率',
        'complete_payment'            => '[网页]支付完成',
        'cost_per_complete_payment'   => '[网页]支付完成成本',
        'complete_payment_rate'       => '[网页]支付完成率 (%)',
        'value_per_complete_payment'  => '[网页]单次支付完成价值',
    ];

    // 给前端特殊展示
    const SHOW_NAME = [
        'operation_status',// 当前开关状态
        'name',
        'secondary_status', // 当前总体状态,
        'budget_mode'
    ];
}

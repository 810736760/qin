<?php

return [
    // 目录顺序时调整注意: App\Providers\AppServiceProvider中的getMenu()方法
    0 => [
        'icon' => 'fa-book',
        'name' => '小说管理',
        'menu' => [
            [ 'name' => '小说列表', 'url' => 'novel/index', 'icon' => 'fa-circle-o' ],
            [ 'name' => '热门搜索', 'url' => 'book/search_show', 'icon' => 'fa-circle-o' ],
            [ 'name' => '推荐书本设置', 'url' => 'book/book_recommend_show', 'icon' => 'fa-circle-o' ],
            [ 'name' => '新用户书架设置', 'url' => 'book/first_recommend_show', 'icon' => 'fa-circle-o' ],
            [ 'name' => '首页推荐', 'url' => 'gender/lists', 'icon' => 'fa-circle-o' ],
            [ 'name' => '接口采集测试', 'url' => 'novel/novel_test', 'icon' => 'fa-circle-o' ],
            [ 'name' => '主角名称替换', 'url' => 'novel/book_role_replace', 'icon' => 'fa-circle-o' ],
            [ 'name' => '抓取接口通知', 'url' => 'novel/grab_novel', 'icon' => 'fa-circle-o' ],
            [ 'name' => '定时下线书本列表', 'url' => 'novel/list_shelves', 'icon' => 'fa-circle-o' ],
            [ 'name' => '书本待更新', 'url' => 'novel/to_be_update_index', 'icon' => 'fa-circle-o' ],
            [ 'name' => '书本内容搜索', 'url' => 'novel/es_search', 'icon' => 'fa-circle-o' ],
            //[ 'name' => '内容反馈', 'url' => 'users/book_feedback', 'icon' => 'fa-circle-o' ],
            //[ 'name' => '排行榜(热门榜)', 'url' => 'rank/index', 'icon' => 'fa-circle-o', 'query' => '?key=newbook' ],
            //[ 'name' => '书架页推荐', 'url' => 'KuaiBookRecommend/recommendShow', 'icon' => 'fa-circle-o' ],
        ]
    ],

    1 => [
        'icon' => 'fa-cogs',
        'name' => '投放配置',
        'menu' => [
            [ 'name' => '链接列表', 'url' => 'link/link_list', 'icon' => 'fa-circle-o' ],
            [ 'name' => '设置默认ios链接', 'url' => 'link/show_ios_link', 'icon' => 'fa-circle-o' ],
            [ 'name' => '设置书本推送文案', 'url' => 'push/push_content_show', 'icon' => 'fa-circle-o' ],
            [ 'name' => 'ios封面替换图上传', 'url' => 'public/ios_image', 'icon' => 'fa-circle-o' ],
            [ 'name' => '单链接设置推荐书本', 'url' => 'book/link_recommend_show', 'icon' => 'fa-circle-o' ],
            [ 'name' => '临时书本库添加', 'url' => 'temporary/temporary_show', 'icon' => 'fa-circle-o' ],
            [ 'name' => '阅读页书本推荐', 'url' => 'book/read_content_recommend', 'icon' => 'fa-circle-o' ],
            [ 'name' => '书本付费趋势模型', 'url' => 'book_pay_trend_model/index', 'icon' => 'fa-circle-o' ],
        ]
    ],


    2 => [
        'icon' => 'fa-book',
        'name' => '用户管理',
        'menu' => [
            [ 'name' => '用户列表', 'url' => 'users/show' ],
            [ 'name' => '用户反馈', 'url' => 'user/feedback' ],
            [ 'name' => '调整记录', 'url' => 'users/operation_log' ],
        ]

    ],

    3 => [
        'icon' => 'fa-bars',
        'name' => 'Facebook投放管理',
        'menu' => [
            [ 'name' => '广告账户认领', 'url' => 'facebook/adaccount', 'icon' => 'fa-circle-o' ],
            [ 'name' => '广告系列', 'url' => 'facebook/adcampaign', 'icon' => 'fa-circle-o' ],
            [ 'name' => '广告组', 'url' => 'facebook/adset', 'icon' => 'fa-circle-o' ],
            [ 'name' => '广告投放规则', 'url' => 'facebook/rule', 'icon' => 'fa-circle-o' ],
            [ 'name' => '预警规则', 'url' => 'protect/protect_rule', 'icon' => 'fa-circle-o' ],
            [ 'name' => '预警记录', 'url' => 'protect/protect_result/index', 'icon' => 'fa-circle-o' ],
        ]
    ],

    4  => [
        'icon' => 'fa-bar-chart',
        'name' => '投放数据统计',
        'menu' => [
            [ 'name' => '投放人员数据汇总', 'url' => 'facebookData/totalData' ],
            [ 'name' => 'Facebook广告数据', 'url' => 'facebookData/data_by_link' ],
            [ 'name' => '付费趋势', 'url' => 'abroad/pay_trend_first' ],
            [ 'name' => 'IOS14.5每日数据统计', 'url' => 'dailyBookSummary/index' ],
            // [ 'name' => '付费趋势(自然日新版)', 'url' => 'abroad/pay_trend_first' ],
            // [ 'name' => '海外版APP付费趋势(24小时)', 'url' => 'abroad/pay_trend_all_twenty_four' ],
//            ['name' => '海外版APP书籍付费趋势', 'url' => 'abroad/abroad_book'],
            [ 'name' => '投放打点数据统计', 'url' => 'launch/index' ],
            [ 'name' => '书本数据统计', 'url' => 'abroad/book_statistics_list' ],
        ]

    ],
    5  => [
        'icon' => 'fa-cubes',
        'name' => '数据统计',
        'menu' => [
            [ 'name' => '数据统计', 'url' => 'statistics/index', 'icon' => 'fa-circle-o' ],
            [ 'name' => '订单统计', 'url' => 'statistics/order_statistic', 'icon' => 'fa-circle-o' ],
            [ 'name' => '订单列表', 'url' => 'orders/lists', 'icon' => 'fa-circle-o' ],
            [ 'name' => '单本充值统计', 'url' => 'statistics/recharge_book', 'icon' => 'fa-circle-o' ],
            [ 'name' => '常驻通知统计', 'url' => 'notice/notice_statistics', 'icon' => 'fa-circle-o' ],
            [ 'name' => '推送统计', 'url' => 'push/total_statistic', 'icon' => 'fa-circle-o' ],
            [ 'name' => '开屏数据统计', 'url' => 'advertisement/statistics' ],
            [ 'name' => '用户点击概况', 'url' => 'statistics/user_click' ],
            [ 'name' => '书架页数据统计', 'url' => 'statistics/bookshelf_statistic' ],
            [ 'name' => '用户阅读概况', 'url' => 'statistics/user_read' ],
            [ 'name' => '用户概况', 'url' => 'statistics/user_statistics' ],
            [ 'name' => '档位概况', 'url' => 'statistics/good_gear_total' ]
        ]
    ],
    //个人中心
    6  => [
        'icon' => 'fa-user',
        'name' => '个人中心',
        'menu' => [
            [ 'name' => '个人资料', 'url' => 'profile/index', 'icon' => 'fa-circle-o' ],
            [ 'name' => '修改密码', 'url' => 'profile/password', 'icon' => 'fa-circle-o' ],
        ]
    ],
    // 广告位管理（原资源位管理）
    7  => [
        'icon' => 'fa-picture-o',
        'name' => '广告位管理',
        'menu' => [
            [ 'name' => 'banner管理', 'url' => 'banner/index', 'icon' => 'fa-circle-o', 'query' => '?status=1' ],
            [ 'name' => '开屏广告', 'url' => 'advertisement/advertisement_show', 'icon' => 'fa-circle-o' ],
            [ 'name' => '常驻通知', 'url' => 'notice/notice_show', 'icon' => 'fa-circle-o' ],
            [ 'name' => '弹窗通知', 'url' => 'popup/popup_show', 'icon' => 'fa-circle-o' ],
        ]
    ],
    8  => [
        'icon' => 'fa-linode',
        'name' => '活动管理',
        'menu' => [
            [ 'name' => '活动中心', 'url' => 'activity/activity_show', 'icon' => 'fa-circle-o' ],
            [ 'name' => '阅读页充值活动', 'url' => 'activity/read_recharge_edit', 'icon' => 'fa-circle-o' ],
        ]
    ],
    9  => [
        'icon' => 'fa-comments',
        'name' => '推送管理',
        'menu' => [

            [ 'name' => '添加列表', 'url' => 'push/index', 'icon' => 'fa-circle-o' ],
        ]
    ],
    // 素材管理
    10 => [
        'icon' => 'fa-book',
        'name' => '素材管理',
        'menu' => [
            [ 'name' => '文案标题管理', 'url' => 'material/index', 'icon' => 'fa-circle-o' ],
            [ 'name' => '多媒体素材库', 'url' => 'material/media_index', 'icon' => 'fa-circle-o' ],
            [ 'name' => '素材数据洞察', 'url' => 'material/insights', 'icon' => 'fa-circle-o' ],
            [ 'name' => '素材人员统计', 'url' => 'material/member', 'icon' => 'fa-circle-o' ],
        ]
    ],

    // 翻译
    11 => [
        'icon' => 'fa-book',
        'name' => '翻译管理',
        'menu' => [
            [ 'name' => '快捷翻译', 'url' => 'translation/index', 'icon' => 'fa-circle-o' ],
            [ 'name' => '翻译添加', 'url' => 'translate/index', 'icon' => 'fa-circle-o' ],
            [ 'name' => '翻译统计', 'url' => 'translate/words_census', 'icon' => 'fa-circle-o' ],
            [ 'name' => 'api翻译统计', 'url' => 'translate/tran_word_api', 'icon' => 'fa-circle-o' ],
            [ 'name' => '任务池', 'url' => 'translate/task_pool_show', 'icon' => 'fa-circle-o' ],
            [ 'name' => '个人任务页', 'url' => 'translate/personal_task_list', 'icon' => 'fa-circle-o' ],
            [ 'name' => '已领取任务列表', 'url' => 'translate/tran_auditing_list', 'icon' => 'fa-circle-o' ]
        ]
    ],


    12 => [
        'icon' => 'fa-book',
        'name' => '书商管理',
        'menu' => [
            [ 'name' => '添加主体', 'url' => 'book_channel/add_main', 'icon' => 'fa-circle-o' ],
            [ 'name' => '添加接口', 'url' => 'book_channel/add_channel_name', 'icon' => 'fa-circle-o' ],
            [ 'name' => '添加书商', 'url' => 'book_channel/add_book_channel', 'icon' => 'fa-circle-o' ],
            [ 'name' => '批次列表', 'url' => 'book_channel/batch_list', 'icon' => 'fa-circle-o' ],
            [ 'name' => '书本分配', 'url' => 'book_channel/book_distribute', 'icon' => 'fa-circle-o' ],
            [ 'name' => '数据统计', 'url' => 'book_channel/data_statistics', 'icon' => 'fa-circle-o' ]
        ]
    ],
    13 => [
        'icon' => 'fa-dollar',
        'name' => '档位管理',
        'menu' => [
            [ 'name' => '添加充值档位', 'url' => 'public/pay_recharge_index', 'icon' => 'fa-circle-o' ],
            [ 'name' => '模板充值档位设置', 'url' => 'public/pay_show', 'icon' => 'fa-circle-o' ],
            [ 'name' => '国家充值档位设置', 'url' => 'country/country_pay_show', 'icon' => 'fa-circle-o' ],
        ]
    ],

    //系统管理
    14 => [
        'icon' => 'fa-cogs',
        'name' => '系统管理',
        'menu' => [
            [ 'name' => '管理员列表', 'url' => 'adminmanager/index', 'icon' => 'fa-circle-o' ],
            [ 'name' => '角色管理', 'url' => 'role/index', 'icon' => 'fa-circle-o' ],
            [ 'name' => '权限管理', 'url' => 'rights/index', 'icon' => 'fa-circle-o' ],
            [ 'name' => '更新管理', 'url' => 'appupdate/index', 'icon' => 'fa-circle-o' ],
            [ 'name' => '审核开关', 'url' => 'public/switch_list', 'icon' => 'fa-circle-o' ],
        ]
    ],

];


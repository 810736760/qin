<?php

/**
 * Created by PhpStorm.
 * Title：Redis中key变量可使用类型
 * User: yaokai
 * Date: 2018/5/3 0003
 * Time: 11:21
 */

namespace App\Libs;

use App\Services\Common\PublicService;

class RedisKey
{

    /* ============ string相关 start ================= */
    const DEFAULT_ANDROID_LINK = 'day_link';
    const DEFAULT_IOS_LINK = 'default_ios_link';
    const HKD_USD = 'HKD_USD';

    const TEST_KEY = 'test_key';

    const TEST_KEY_NEW = 'test_key_new';
    const MATERIAL_SYNC_STATUS = 'material_sync_status'; // 素材同步状态

    const ALL_TOKEN_DISABLE = 'all_token_disable_v1'; // 所有管理员Token都过期了

    const USER_TOKEN_DISABLE = 'user_token_disable'; //  管理员Token失效
    /* ============ string相关 end ================= */


    /* ============ hash相关 start ================= */
    const BANNER_V030000 = 'home_banner_v030000:'; //3.0banner
    const TODAY_ABROAD_LINK_STATISTIC = 'today_abroad_link_statistic';
    const HOME_PAGE_LIST = 'home_page_list';//欧美版首页推荐相关

    const MATERIAL_SYNC_LIST = 'material_sync_list'; // 待同步素材
    const MATERIAL_SYNC = 'material_sync_'; // 素材同步
    const MATERIAL_ADD = 'material_add_'; // 素材新增
    const ACCOUNTS = 'accounts'; // 素材的账户
    const STATUS = 'status'; // 是否开启同步
    const ABROAD_GOOGLE_ACCESS_TOKEN = 'abroad_google_access_token'; //海外版app google接口access_token
    const ABROAD_GOOGLE_REFRESH_TOKEN = 'abroad_google_refresh_token';


    /* ============ hash相关 end =============== */
}

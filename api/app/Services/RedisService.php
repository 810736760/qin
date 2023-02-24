<?php

namespace App\Services;

use App\Helper\Tool;
use App\Libs\RedisKey;
use App\Services\DingTalk\AlarmService;
use App\Services\Service;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class RedisService extends Service
{
    const REDIS_EXPIRE_TIME_DATE = 86400;

    // 永不过期
    const REDIS_EXPIRE_FOREVER = -1;

    const REDIS_EXPIRE_TIME_MIN = 60;
//    const REDIS_EXPIRE_TIME_MIN = 600;

    const REDIS_EXPIRE_TIME_HOUR = 3600;

    const REDIS_LOCK_TIME = 30;

    /**
     * @return RedisService
     */
    public static function getIns(): RedisService
    {
        return parent::getInstance();
    }


    public function get($cacheName, $default = '')
    {
        $res = Redis::get($cacheName);
        if (false === $res) {
            return $default;
        }
        return $res;
    }

    public function set($cacheName, $value, $ex = self::REDIS_EXPIRE_TIME_DATE, $nx = false)
    {
        if (empty($cacheName)) {
            return false;
        }
        if ($ex == self::REDIS_EXPIRE_FOREVER) {
            return Redis::set($cacheName, $value);
        }
        $sec = $ex;
        if ($nx) {
            $sec = [
                'nx',
                'ex' => $ex
            ];
        }
        return Redis::command('set', [$cacheName, $value, $sec]);
    }

    public function del($cacheName)
    {
        if (empty($cacheName)) {
            return false;
        }
        if (is_string($cacheName)) {
            $cacheName = [$cacheName];
        }
        return Redis::del(...$cacheName);
    }

    public function mget($arr)
    {
        if (empty($arr)) {
            return false;
        }
        $cache = [];
        try {
            $cache = Redis::mget($arr);
        } catch (\Exception $e) {
            foreach ($arr as $one) {
                $cache[] = $this->get($one);
            }
            Log::info('redis mget error in base ' . $e->getMessage(), [$arr, $cache]);
            AlarmService::dingdingSend(
                'mget 失效,请查看[key]=>' . implode(',', $arr)
            );
        }
        return $cache;
    }

    public function mset($arr, $expire = 0)
    {
        if (empty($arr)) {
            return [];
        }
        if (empty($expire)) {
            return Redis::mset($arr);
        }
        foreach ($arr as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            $this->set($key, $value, $expire);
        }
        return true;
    }

    public function getTtl($key)
    {
        if (empty($key)) {
            return false;
        }
        return Redis::ttl($key);
    }

    public function expire($key, $sec)
    {
        if (empty($key)) {
            return false;
        }
        return Redis::expire($key, $sec);
    }


    public function incr($key)
    {
        if (empty($key)) {
            return false;
        }
        return Redis::incr($key);
    }

    public function mGetByKeys($ids, $pre = '', $isDecode = false): array
    {
        $noHit = $hitData = $hit = [];
        $fmtPre = Tool::fmtIdPreInCache($pre);
        if (empty($ids)) {
            return [$fmtPre, $hit, $hitData, $noHit];
        }
        $hitCache = $this->mget(Tool::fmtIdInCache($ids, $fmtPre));
        foreach ($hitCache as $index => $value) {
            if ($value === null) {
                continue;
            }
            if ($isDecode) {
                $value = json_decode($value, true);
            }
            $hit[] = $ids[$index];
            $hitData[$ids[$index]] = $value;
        }
        $fmtHit = array_diff($ids, $hit);
        foreach ($fmtHit as $one) {
            if (empty($one)) {
                continue;
            }
            $noHit[] = $one;
        }
        return [$fmtPre, $hit, $hitData, $noHit];
    }

    public static function disableUserTokenName(): string
    {
        return Tool::fmtCoIdKey(RedisKey::ALL_TOKEN_DISABLE);
    }

    public static function disableUserToken(): string
    {
        return Tool::fmtCoIdKey(RedisKey::USER_TOKEN_DISABLE);
    }

    /**
     * 全部常用静态缓存
     * @param $key
     * @return mixed|string
     */
    public function getStaticKV($key)
    {
        if ($key == self::disableUserTokenName()) {
            return 1;
        }

        static $cacheStatic = [];
        if (empty($cacheStatic[$key])) {
            $cacheStatic[$key] = $this->get($key);
        }
        return $cacheStatic[$key];
    }

    public function hIncrby($table, $key, $delta = 1)
    {
        if (empty($table) || empty($key)) {
            return false;
        }
        return Redis::HINCRBY($table, $key, $delta);
    }

    public function hGetAll($table)
    {
        if (empty($table)) {
            return false;
        }
        return Redis::HGETALL($table);
    }

    public function hMGet($table, ...$keys)
    {
        if (empty($table)) {
            return false;
        }
        return Redis::HMGET($table, ...$keys);
    }

    public function hLen($table)
    {
        if (empty($table)) {
            return false;
        }
        return Redis::HLEN($table);
    }

    public function resetTTL($key, $ex = self::REDIS_EXPIRE_TIME_DATE)
    {
        if ($this->getTtl($key) === -1) {
            $this->expire($key, $ex);
        }
    }
}

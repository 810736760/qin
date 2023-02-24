<?php

namespace App\Listeners;

use App\Helper\Tool;
use App\Libs\RedisKey;
use App\Libs\RabbitMq\Producer;
use App\Events\MaterialSyncEvent;
use Illuminate\Support\Facades\Redis;

class MaterialSyncListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  MaterialSyncEvent  $event
     * @return void
     */
    public function handle(MaterialSyncEvent $event)
    {
        $userId = $event->userId ?? 0;
        $materialIds = $event->materialIds ?? [];

        try {
            if ($materialIds && $userId) {
                if ($exist = Redis::hget(Tool::fmtCoIdKey(RedisKey::MATERIAL_SYNC_LIST), $userId)) {
                    $materialIds = array_merge($materialIds, json_decode($exist, true));
                }
                Redis::hset(Tool::fmtCoIdKey(RedisKey::MATERIAL_SYNC_LIST), $userId, json_encode($materialIds));
                Producer::publish('AdMaterialSync');
            }
        } catch (\Exception $e) {
            \Log::info('MaterialSyncListener:'. $e->getMessage());
        }

    }
}

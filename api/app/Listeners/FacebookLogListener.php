<?php

namespace App\Listeners;

use App\Events\FacebookLogEvent;
use App\Models\Facebook\FacebookOperationLog;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class FacebookLogListener
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
     * @param  FacebookLogEvent  $event
     * @return void
     */
    public function handle(FacebookLogEvent $event)
    {
        \Log::info('FacebookLogListener');
        $type = $event->type ?? 0;
        $adminId = $event->adminId ?? 0;
        $content = $event->content ?? '';
        $insert = [
            'type' => $type,
            'admin_id' => $adminId,
            'content' => $content,
        ];
        FacebookOperationLog::insert($insert);
    }
}

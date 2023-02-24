<?php

namespace App\Services;

class Service
{
    const DEFAULT_CO = 1;

    public const  TIMEZONE_MAP = [
        'Asia/Shanghai'       => 8,
        'Asia/Hong_Kong'      => 9,
        'America/Los_Angeles' => -7,
        'America/Phoenix'     => -7
    ];


    public const PLATFORM_TYPE_FACEBOOK = 1;
    public const PLATFORM_TYPE_TIKTOK = 2;
    public const PLATFORM_TYPE_LINE = 3;
    public const PLATFORM_TYPE_GOOGLE = 4;

    public const PLATFORM_LIST = [
        self::PLATFORM_TYPE_FACEBOOK => 'facebook',
        self::PLATFORM_TYPE_TIKTOK   => 'tiktok',
        self::PLATFORM_TYPE_LINE     => 'line',
        self::PLATFORM_TYPE_GOOGLE   => 'google',

    ];

    const TYPE_TASK_MODEL_CREATE = 'create';
    const TYPE_TASK_MODEL_EDIT = 'edit';
    const TYPE_TASK_MODEL_DRAFT = 'draft';

    const PER_PAGE = 20;

    public static function getInstance()
    {
        static $services = [];
        $class = get_called_class();
        if (empty($services[$class])) {
            $services[$class] = new $class();
        }
        return $services[$class];
    }
}

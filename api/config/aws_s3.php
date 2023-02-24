<?php
/**
 * aws 配置
 */
return [
    'key'         => env('S3_ACCESS_KEY_ID', 'AKIASPR35Z3KGP7IRO5W'),
    'secret'      => env('S3_SECRET_ACCESS_KEY', '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'),
    'debug'       => env('S3_DEBUG', false),
    'bucket'      => env('S3_BUCKET', 'kuaiwan-adcenter'),
    'domain'      => env('S3_DOMAIN', 'https://material.kwmobi.com/'),
    'ffmpeg_path' => env('FFMPEG_PATH', '/usr/local/ffmpeg/bin/ffmpeg')
];

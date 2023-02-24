<?php
return [

    'secrectKey' => env('QINIU_SECRET_KEY', ''),    //七牛SK
    'accessKey'  => env('QINIU_ACCESS_KEY', ''),    //七牛AK
    'domain'     => env('QINIU_DOMAIN', ''),        //私密空间
    // 'domain_cn'  => 'https://fd.einsuraunce.com/',        // 国内域名访问HK服务器
    'domain_cn'  => 'https://statics.lmmobi.com/',
    'bucket'     => env('QINIU_BUCKET', ''),        //空间名称
];

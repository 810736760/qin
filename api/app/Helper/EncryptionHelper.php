<?php
/**
 * Created by PhpStorm.
 * User: xubin
 * Date: 7/1/21
 * Time: 3:04 PM
 */

namespace App\Helper;

class EncryptionHelper
{

    #@todo AES加解密
    #加密
    public static function encrypt($input): string
    {
        if (is_array($input)) {
            $input = json_encode($input, JSON_UNESCAPED_SLASHES);
        }
        $iv = config('api_conf')['line_iv'];
        $password = config('api_conf')['line_password'];
        $key = hash('sha256', $password, true);
        $str = openssl_encrypt($input, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
        return base64_encode($str);
    }


    //解密
    public static function decrypt($sStr)
    {
        $iv = config('api_conf')['line_iv'];
        $password = config('api_conf')['line_password'];
        $key = hash('sha256', $password, true);
        dump($key);
        $encData = base64_decode($sStr);
        return openssl_decrypt($encData, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    }
}

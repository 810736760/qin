<?php

namespace App\Definition;

class ReturnCode
{
    const SUCCEED = 200;
    const ERROR = 422; //- 反馈给前端提示 不拦截
    const ERROR_PARAMS = 201; //参数错误
    const ERROR_SIGN = 202; //签名错误
    const ERROR_LOGIN_REQUIRED = 203; //需要登录
    const ERROR_INVALID_REFERER = 204; //引用页非法
    const ERROR_DATA_FAILED = 205; //数据错误
    const ERROR_BUSINESS = 206; //业务逻辑错误 -- 反馈给前端提示 不主动清除
    const ERROR_TIMEOUT = 207; //服务超时
    const ERROR_API_REQUEST = 208; //请求其他项目API错误
    const ERROR_USER_PERMISSION = 213; //用户权限错误
    const ERROR_PERMISSIONS = 214; // facebook受众更新错误
    const ERROR_OVER_LIMIT = 215; // 请求超限
    const ERROR_INNER_UNKNOWN = 500; // 内部代码错误

    const INNER_SUCCESS = 1;
    const INNER_FAILED = 0;
}

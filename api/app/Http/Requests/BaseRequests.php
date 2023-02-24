<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class BaseRequests extends FormRequest
{
    /**
     * 验证失败处理
     * @param object $validator
     * @throws Illuminate\Http\Exceptions\HttpResponseException
     */
    public function failedValidation(Validator $validator)
    {
        $error = $validator->errors()->first();
        $response = response()->json([
            'code' => 422,
            'msg'  => $error,
        ]);
 
        throw new HttpResponseException($response);
    }
}

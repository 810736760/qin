<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseRequests;

class BookPayTrendModelRequest extends BaseRequests
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            're_level1' => 'required|numeric',
            're_level2' => 'required|numeric',
            're_level3' => 'required|numeric',
            're_level4' => 'required|numeric',
        ];
    }

    public function messages()
    {
        return [
            're_level1.required' => '级别必传，不能为空',
            're_level2.required' => '级别必传，不能为空',
            're_level3.required' => '级别必传，不能为空',
            're_level4.required' => '级别必传，不能为空',
            're_level1.numeric' => '级别必须是数字类型',
            're_level2.numeric' => '级别必须是数字类型',
            're_level3.numeric' => '级别必须是数字类型',
            're_level4.numeric' => '级别必须是数字类型',
        ];
    }
}

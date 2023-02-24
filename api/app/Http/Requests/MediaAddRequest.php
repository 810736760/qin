<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\BaseRequests;

class MediaAddRequest extends BaseRequests
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
    public function rules(): array
    {
        return [
            'file' => 'required',
            'sex'  => 'required'
        ];

    }

    public function messages(): array
    {
        return [
            'file.required' => '文件必传，不能为空',
            'sex.required'  => '偏好必传，不能为空',
        ];

    }
}

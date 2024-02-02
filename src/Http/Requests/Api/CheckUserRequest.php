<?php

namespace Vtlabs\Core\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CheckUserRequest extends FormRequest
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
            'mobile_number' => 'required|exists:users,mobile_number',
        ];
    }
}

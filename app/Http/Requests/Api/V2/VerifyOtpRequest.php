<?php

namespace App\Http\Requests\Api\V2;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class VerifyOtpRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'string',
                'regex:/^09[0-9]{9}$/'
            ],


            'code' => [
                'required',
                'string',
                'digits:6'
            ]
        ];
    }

    public function messages(): array
    {

        return [

            'phone.required' =>
            'شماره موبایل الزامی است.',


            'phone.regex' =>
            'شماره موبایل صحیح نیست.',


            'code.required' =>
            'کد تایید الزامی است.',


            'code.digits' =>
            'کد تایید باید ۶ رقم باشد.'

        ];
    }
}

<?php

namespace App\Http\Requests\Api\V2;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SendOtpRequest extends FormRequest
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
            'phone' => ['required', 'string', 'regex:/^09[0-9]{9}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone_required' => 'شماره موبایل الزامی است',
            'phone.regex' => 'فرمت شماره موبایل صحیح نیست',
        ];
    }
}   

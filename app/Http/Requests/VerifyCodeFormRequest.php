<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyCodeFormRequest extends FormRequest
{
    public function rules()
    {
        return [
            'verification_code' => 'required|size:' . config('2fa.code_length'),
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TwoFaStartFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'session_id' => 'uuid|nullable',
            'origin' => ['nullable', Rule::in(config('2fa.origins'))],
            'access_id' => 'uuid|required',
            'signature' => 'string|required',
            'url_back' => 'url|required',
            'url_success' => 'url|required',
            'url_failure' => 'url|required',
            'locale' => 'string|nullable|max:2',
            'ip_address' => 'ip',
        ];
    }
}

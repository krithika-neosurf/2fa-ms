<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePhoneFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'telephone_prefix' => 'required|string|min:2|max:5',
            'telephone_number' => 'required|numeric|min:4',
        ];
    }
}

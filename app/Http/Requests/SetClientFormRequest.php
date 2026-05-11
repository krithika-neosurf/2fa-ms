<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Neosurf\Services\ClientService;

class SetClientFormRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'client_id' => [
                'required',
                'uuid',
            ]
        ];
    }
}

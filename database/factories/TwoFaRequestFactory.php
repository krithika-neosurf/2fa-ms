<?php

namespace Database\Factories;

use App\Models\TwoFaRequest;
use Illuminate\Database\Eloquent\Factories\Factory;

class TwoFaRequestFactory extends Factory
{
    protected $model = TwoFaRequest::class;

    public function definition()
    {
        return [
            'id' => $this->faker->uuid(),
            'session_id' => null,
            'client_id' => null,
            'origin' => 'neopass',
            'access_id'=> '5dd0f5c8-2d11-343a-af62-20b4df6ece3c',
            'signature'=> '$argon2id$v=19$m=65536,t=4,p=1$Z2VEOS5majVUaXVSNUxYSw$eXNbz1mVwKSJuSwl+hUgpipxAie8nwQrD0qk54Jwckg',
            'url_back' => 'http://merchant.com/back',
            'url_success' => 'http://merchant.com/success',
            'url_failure' => 'http://merchant.com/failure',
            'locale' => 'en',
            'raw_request' => '{"origin":"neopass","access_id":"5dd0f5c8-2d11-343a-af62-20b4df6ece3c","signature":"$argon2id$v=19$m=65536,t=4,p=1$Z2VEOS5majVUaXVSNUxYSw$eXNbz1mVwKSJuSwl+hUgpipxAie8nwQrD0qk54Jwckg","url_back":"http:\/\/merchant.com\/back","url_success":"http:\/\/merchant.com\/success","url_failure":"http:\/\/merchant.com\/failure","locale":"en","ip_address":"127.0.0.1"}',
            'ip_address' => '127.0.0.1',
            'status' => 'started',
        ];
    }

    public function afterLogin()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => TwoFaRequest::STATUS['VALID_SIGNATURE'],
                'client_id' => 'some-client-id',
            ];
        });
    }

    public function validSession()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => TwoFaRequest::STATUS['VALID_SESSION'],
                'session_id' => 'some-session-id',
            ];
        });
    }

    public function codeSent()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => TwoFaRequest::STATUS['SENT_CODE'],
            ];
        });
    }

    public function invalidCode()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => TwoFaRequest::STATUS['INVALID_CODE'],
            ];
        });
    }

    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => TwoFaRequest::STATUS['COMPLETED'],
            ];
        });
    }

    public function afterSentCode()
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => TwoFaRequest::STATUS['SENT_CODE'],
            ];
        });
    }
}

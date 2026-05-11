<?php

namespace App\Models;

use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Model;

class TwoFaRequest extends Model
{
    use HasFactory;

    public const STATUS = [
        'STARTED' => 'started',
        'INVALID_SIGNATURE' => 'invalid_signature',
        'VALID_SIGNATURE' => 'valid_signature',
        'INVALID_SESSION' => 'invalid_session',
        'VALID_SESSION' => 'valid_session',
        'UPDATED_MOBILE' => 'updated_mobile',
        'SENT_CODE' => 'sent_code',
        'INVALID_CODE' => 'invalid_code',
        'VALID_CODE' => 'valid_code',
        'COMPLETED' => 'completed',
    ];

    public const ORIGIN = [
        'NEOPASS' => 'neopass',
        'PAY' => 'pay'
    ];

    protected $guarded = [];

    public function getIsStartedAttribute(): bool
    {
        // TODO add check here in future after adding other 2fa step
        return self::STATUS['STARTED'] === $this->status;
    }

    public function checkSignature(): bool
    {
        return Hash::check($this->prepRawRequest(), $this->signature);
    }

    private function prepRawRequest(): string
    {
        $requestData = collect(json_decode($this->raw_request, true))
            ->forget('signature')
            ->all();
        
        return serialize($requestData);
    }

    public function setStatusAs(string $status): void
    {
        $this->status = $status;
        $this->save();
    }

    public function getHasSessionAttribute(): bool
    {
        return isset($this->session_id);
    }

    public function getIsCreatedFromSessionAttribute(): bool
    {
        return $this->has_session && !$this->has_client;
    }

    public function getHasClientAttribute(): bool
    {
        return isset($this->client_id);
    }

    public function getIsCreatedFromClientAttribute(): bool
    {
        return $this->has_client;
    }

    public function getHasSentCodeAttribute(): bool
    {
        return self::STATUS['SENT_CODE'] === $this->status;
    }

    public function generateOtpVerificationCode(): string
    {
        $otpVerificationCode = new OtpVerificationCode();

        $this->otpVerificationCodes()->save($otpVerificationCode);

        return $otpVerificationCode->code;
    }

    public function otpVerificationCodes(): HasMany
    {
        return $this->hasMany(OtpVerificationCode::class);
    }

    public function codeRetriesLimitReached(): bool
    {
        return config('2fa.send_code_retries') <= $this->otpVerificationCodes()->count();
    }

    public function checkVerificationCode(string $code): bool
    {
        $otpVerificationCode = $this->otpVerificationCodes()->latest()->first();

        if ($otpVerificationCode && $otpVerificationCode->isValid() && $otpVerificationCode->code == $code) {
            $otpVerificationCode->use();

            return true;
        }

        return false;
    }

    public function getIsInvalidCodeAttribute(): bool
    {
        return self::STATUS['INVALID_CODE'] === $this->status;
    }

    public function getIsCompletedAttribute(): bool
    {
        return self::STATUS['COMPLETED'] === $this->status;
    }

    public function generateTwoFaToken(): string
    {
        $twoFaToken = new TwoFaToken();

        $this->twoFaToken()->save($twoFaToken);

        return $twoFaToken->token;
    }

    public function twoFaToken(): HasOne
    {
        return $this->hasOne(TwoFaToken::class);
    }
}

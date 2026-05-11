<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Model;

class OtpVerificationCode extends Model
{
    use HasFactory;

    public function __construct(array $attributes = [])
    {
        // Dynamically generate code
        if (! isset($attributes['code'])) {
            $attributes['code'] = $this->generateCode();
        }

        parent::__construct($attributes);
    }

    private function generateCode(): string
    {
        $min = pow(10, config('2fa.code_length') - 1);
        $max = ($min * 10) - 1;

        return random_int($min, $max);
    }

    public function isValid(): bool
    {
        return !$this->isUsed() && !$this->isExpired();
    }

    public function isUsed(): bool
    {
        return isset($this->used_at);
    }

    public function isExpired(): bool
    {
        return $this->created_at->diffInMinutes(Carbon::now()) > config('2fa.code_validity');
    }

    public function use(): void
    {
        $this->used_at = Carbon::now();
        $this->save();
    }
}

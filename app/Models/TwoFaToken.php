<?php

namespace App\Models;

use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use GoldSpecDigital\LaravelEloquentUUID\Database\Eloquent\Model;

class TwoFaToken extends Model
{
    use HasFactory;

    public function __construct(array $attributes = [])
    {
        // Dynamically generate code
        if (! isset($attributes['code'])) {
            $attributes['token'] = $this->generateCode();
        }

        parent::__construct($attributes);
    }

    public function generateCode(): string
    {
        return Str::random(60);
    }
}

<?php

namespace App\Providers;

use App\Events\NeedTwoFaSmsCode;
use App\Listeners\SendTwoFaToken;
use App\Listeners\SendTwoFaSmsCode;
use App\Events\TwoFaVerificationSucceeded;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        NeedTwoFaSmsCode::class => [
            SendTwoFaSmsCode::class,
        ],
        TwoFaVerificationSucceeded::class => [
            SendTwoFaToken::class
        ],
    ];

    public function boot()
    {

    }
}

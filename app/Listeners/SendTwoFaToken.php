<?php

namespace App\Listeners;

use App\Models\TwoFaRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\TwoFaVerificationSucceeded;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendTwoFaToken
{
    public function handle(TwoFaVerificationSucceeded $event)
    {
        $response = Http::post($event->twoFaRequest->url_back, [
            'token' => $event->twoFaRequest->twoFaToken->token,
        ]);

        if ($response->successful()) {
            $event->twoFaRequest->setStatusAs(TwoFaRequest::STATUS['COMPLETED']);
        } else {
            $response->throw();
        }
    }
}

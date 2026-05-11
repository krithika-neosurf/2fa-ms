<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\TwoFaRequest;

class NeedTwoFaSmsCode
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public TwoFaRequest $twoFaRequest;

    public function __construct(TwoFaRequest $twoFaRequest)
    {
        $this->twoFaRequest = $twoFaRequest;
    }
}

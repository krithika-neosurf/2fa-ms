<?php

namespace App\Listeners;

use App\Models\TwoFaRequest;
use App\Events\NeedTwoFaSmsCode;
use Neosurf\Services\MessageService;
use App\Repositories\TwoFaRequestRepository;

class SendTwoFaSmsCode
{
    private MessageService $messageService;

    private TwoFaRequestRepository $twoFaRequestRepository;

    public function __construct(TwoFaRequestRepository $twoFaRequestRepository, MessageService $messageService)
    {
        $this->messageService = $messageService;
        $this->twoFaRequestRepository = $twoFaRequestRepository;
    }

    public function handle(NeedTwoFaSmsCode $event)
    {
        $client = $this->twoFaRequestRepository->getClient($event->twoFaRequest);
        app()->setLocale($client['language']);

        $clientMobile = preg_replace('/\D/', '', $client['telephone_prefix'] . $client['telephone_number']);

        if (!$clientMobile) {
            throw new \Exception('No client mobile');
        }

        $code = $event->twoFaRequest->generateOtpVerificationCode();

        $this->messageService->sendSms([
            'to' => $clientMobile,
            'from' => env('APP_NAME'),
            'text' => __('2fa_request.sms', ['code' => $code, 'validity' => config('2fa.code_validity')]),
        ]);
    }
}

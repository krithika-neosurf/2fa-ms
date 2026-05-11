<?php

namespace App\Repositories;

use Illuminate\Support\Str;
use App\Models\TwoFaRequest;
use App\Events\NeedTwoFaSmsCode;
use Neosurf\Services\ClientService;

class TwoFaRequestRepository
{
    private ClientService $clientService;

    public function __construct(ClientService $clientService)
    {
        $this->clientService = $clientService;
    }

    public function getNextActionResponse(TwoFaRequest $twoFaRequest): array
    {
        $next = $this->getNextAction($twoFaRequest);

        return $this->getActionResponse(Str::kebab($next['action']), $twoFaRequest, $next);
    }

    protected function getActionResponse(string $actionUri, TwoFaRequest $twoFaRequest, array $nextAction)
    {
        $urlFormat = config('microservices.2fa_front.url') . '/%s/2fa-requests/%s/%s';

        return [
            'twoFaRequestId' => $twoFaRequest->id,
            'nextAction' => $nextAction['action'],
            'data' => [
                'message' => $nextAction['message'],
            ],
            'callback' => sprintf($urlFormat, app()->currentLocale(), $twoFaRequest->id, $actionUri),
        ];
    }

    protected function getNextAction(TwoFaRequest $twoFaRequest): array
    {
        // First started 2fa request
        if ($twoFaRequest->is_started) {
            return $this->checkStarted($twoFaRequest);
        }

        // Via 2fa Login
        if (!$twoFaRequest->is_started && $twoFaRequest->is_created_from_client && !$this->clientHasMobile($twoFaRequest)) {
            return $this->getMobile(__('messages.login_success'));
        }

        // Via 2fa with session
        if (!$twoFaRequest->is_started && $twoFaRequest->is_created_from_session && !$this->clientHasMobile($twoFaRequest)) {
            return $this->getMobile(__('messages.session_login_success'));
        }

        if ($twoFaRequest->has_sent_code) {
            return $this->verifyCode(__('messages.code_sent'));
        }

        if ($twoFaRequest->is_invalid_code) {
            return $this->failure(__('messages.invalid_code'));
        }

        if ($twoFaRequest->is_completed) {
            return $this->success(__('messages.otp_verification_success'));
        }
        
        return $this->sendCode($twoFaRequest);
    }

    protected function checkStarted(TwoFaRequest $twoFaRequest): array
    {
        if (!$this->checkSignature($twoFaRequest)) {
            return $this->failure(__('errors.invalid_signature'));
        }

        if (!$this->hasSession($twoFaRequest)) {
            return $this->login(__('errors.no_session'));
        }

        if (!$this->hasValidSession($twoFaRequest)) {
            return $this->login(__('errors.invalid_session'));
        }
        
        return $this->loginWithSession();
    }

    protected function clientHasMobile(TwoFaRequest $twoFaRequest): bool
    {
        $client = $this->getClient($twoFaRequest);

        return $client['telephone_prefix'] && $client['telephone_number'];
    }

    public function getClient(TwoFaRequest $twoFaRequest): array
    {
        $userId = $twoFaRequest->client_id;

        if ($twoFaRequest->is_created_from_session) {
            $session = $this->clientService->findSession($twoFaRequest->session_id);
            $userId = $session['session']['user_id'];
        }

        return $this->clientService->find($userId);
    }

    protected function checkSignature(TwoFaRequest $twoFaRequest): bool
    {
        if ($twoFaRequest->checkSignature()) {
            $twoFaRequest->setStatusAs(TwoFaRequest::STATUS['VALID_SIGNATURE']);

            return true;
        }

        $twoFaRequest->setStatusAs(TwoFaRequest::STATUS['INVALID_SIGNATURE']);

        return false;
    }

    protected function hasSession(TwoFaRequest $twoFaRequest): bool
    {
        return $twoFaRequest->has_session;
    }

    protected function hasValidSession(TwoFaRequest $twoFaRequest): bool
    {
        $session = $this->clientService->findSession($twoFaRequest->session_id);

        if ($session['exists']) {
            $twoFaRequest->setStatusAs(TwoFaRequest::STATUS['VALID_SESSION']);

            return true;
        }

        $twoFaRequest->setStatusAs(TwoFaRequest::STATUS['INVALID_SESSION']);

        return false;
    }

    protected function failure(string $message): array
    {
        return [
            'action' => 'failure',
            'message' => $message,
        ];
    }

    protected function success(string $message): array
    {
        return [
            'action' => 'success',
            'message' => $message,
        ];
    }

    protected function login(string $message, bool $usingSession = false): array
    {
        return [
            'action' => $usingSession? 'login-session' : 'login',
            'message' => $message,
        ];
    }

    protected function getMobile(string $message): array
    {
        return [
            'action' => 'getMobile',
            'message' => $message,
        ];
    }

    protected function loginWithSession(): array
    {
        return $this->login(__('messages.login_session'), true);
    }

    protected function sendCode(TwoFaRequest $twoFaRequest): array
    {
        try {
            NeedTwoFaSmsCode::dispatch($twoFaRequest);
            
            $twoFaRequest->setStatusAs(TwoFaRequest::STATUS['SENT_CODE']);
        } catch (\Exception $e) {
            \Log::error($e);

            return $this->failure($e->getMessage());
        }

        return $this->verifyCode(__('messages.code_sent'));
    }

    protected function verifyCode(string $message): array
    {
        return [
            'action' => 'verifyCode',
            'message' => $message,
        ];
    }
}

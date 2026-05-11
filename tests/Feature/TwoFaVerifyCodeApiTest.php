<?php

namespace Tests\Feature;

use Neosurf\NeosurfMsTest;
use App\Models\TwoFaRequest;
use App\Events\NeedTwoFaSmsCode;
use App\Listeners\SendTwoFaSmsCode;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TwoFaVerifyCodeApiTest extends NeosurfMsTest
{
    use RefreshDatabase;

    public function test_twoFaRequestVerifyValidCode_expectedSuccess()
    {
        $twoFaRequest = TwoFaRequest::factory()
            ->afterSentCode()
            ->create();
        $otpCode = $twoFaRequest->generateOtpVerificationCode();

        $this->assertDatabaseHas('two_fa_requests', [
            'id' => $twoFaRequest->id,
            'status' => TwoFaRequest::STATUS['SENT_CODE'],
        ]);
        $this->assertDatabaseHas('otp_verification_codes', [
            'two_fa_request_id' => $twoFaRequest->id,
            'code' => $otpCode,
            'used_at' => null,
        ]);

        Http::fake();

        $this->post(route('2fa_request.verify_code', ['twoFaRequest' => $twoFaRequest->id]), ['verification_code' => $otpCode])
            ->assertJsonStructure([
                'twoFaRequestId',
                'nextAction',
                'data' => [
                    'message'
                ],
                'callback',
            ])->assertJson([
                'nextAction' => 'success',
                'data' => [
                    'message' => __('messages.otp_verification_success'),
                ],
                'callback' => config('microservices.2fa_front.url') . '/en/2fa-requests/' . $twoFaRequest->id .'/success',
            ], true);

        $this->assertDatabaseHas('two_fa_requests', [
            'id' => $twoFaRequest->id,
            'status' => TwoFaRequest::STATUS['COMPLETED'],
        ]);
        $this->assertDatabaseHas('otp_verification_codes', [
            'two_fa_request_id' => $twoFaRequest->id,
            'code' => $otpCode,
            'used_at' => $twoFaRequest->otpVerificationCodes()->latest()->first()->used_at,
        ]);

        $this->assertNotNull($twoFaRequest->otpVerificationCodes()->latest()->first()->used_at);
    }

    public function test_twoFaRequestVerifyValidCodeSuccess_expectedSentTokenToUrlBack()
    {
        $twoFaRequest = TwoFaRequest::factory()
            ->afterSentCode()
            ->create();
        $otpCode = $twoFaRequest->generateOtpVerificationCode();

        Http::fake();

        $this->post(
            route('2fa_request.verify_code', ['twoFaRequest' => $twoFaRequest->id]),
            ['verification_code' => $otpCode]
        );

        Http::assertSent(function (Request $request) use ($twoFaRequest) {
            return $request['token'] == $twoFaRequest->twoFaToken->token &&
                $request->url() == $twoFaRequest->url_back;
        });
    }

    public function test_twoFaRequestVerifyInvalidCode_expectedFailure()
    {
        $twoFaRequest = TwoFaRequest::factory()
            ->afterSentCode()
            ->create();
        $otpCode = $twoFaRequest->generateOtpVerificationCode();

        $this->assertDatabaseHas('two_fa_requests', [
            'id' => $twoFaRequest->id,
            'status' => TwoFaRequest::STATUS['SENT_CODE'],
        ]);
        $this->assertDatabaseHas('otp_verification_codes', [
            'two_fa_request_id' => $twoFaRequest->id,
            'code' => $otpCode,
            'used_at' => null,
        ]);

        Http::fake();
        $invalidOtpCode = '0000';

        $this->post(route('2fa_request.verify_code', ['twoFaRequest' => $twoFaRequest->id]), ['verification_code' => $invalidOtpCode])
            ->assertJsonStructure([
                'twoFaRequestId',
                'nextAction',
                'data' => [
                    'message'
                ],
                'callback',
            ])->assertJson([
                'nextAction' => 'failure',
                'data' => [
                    'message' => __('messages.invalid_code'),
                ],
                'callback' => config('microservices.2fa_front.url') . '/en/2fa-requests/' . $twoFaRequest->id .'/failure',
            ], true);

        $this->assertDatabaseHas('two_fa_requests', [
            'id' => $twoFaRequest->id,
            'status' => TwoFaRequest::STATUS['INVALID_CODE'],
        ]);
        $this->assertDatabaseHas('otp_verification_codes', [
            'two_fa_request_id' => $twoFaRequest->id,
            'code' => $otpCode,
            'used_at' => NULL,
        ]);

        $this->assertNull($twoFaRequest->otpVerificationCodes()->latest()->first()->used_at);
    }

    public function test_twoFaRequestVerifyValidCodeFailedToSendToken_expectedToResendNewCode()
    {
        $twoFaRequest = TwoFaRequest::factory()
            ->afterSentCode()
            ->create();
        $otpCode = $twoFaRequest->generateOtpVerificationCode();

        $this->assertDatabaseHas('two_fa_requests', [
            'id' => $twoFaRequest->id,
            'status' => TwoFaRequest::STATUS['SENT_CODE'],
        ]);
        $this->assertDatabaseHas('otp_verification_codes', [
            'two_fa_request_id' => $twoFaRequest->id,
            'code' => $otpCode,
            'used_at' => null,
        ]);

        Event::fake([NeedTwoFaSmsCode::class]);
        Http::fake([$twoFaRequest->url_back => Http::response(null, 400)]);

        $this->post(route('2fa_request.verify_code', ['twoFaRequest' => $twoFaRequest->id]), ['verification_code' => $otpCode])
            ->assertJsonStructure([
                'twoFaRequestId',
                'nextAction',
                'data' => [
                    'message'
                ],
                'callback',
            ])->assertJson([
                'nextAction' => 'verifyCode',
                'data' => [
                    'message' => __('messages.code_sent'),
                ],
                'callback' => config('microservices.2fa_front.url') . '/en/2fa-requests/' . $twoFaRequest->id .'/verify-code',
            ], true);

        $this->assertDatabaseHas('two_fa_requests', [
            'id' => $twoFaRequest->id,
            'status' => TwoFaRequest::STATUS['SENT_CODE'],
        ]);
        $this->assertDatabaseHas('otp_verification_codes', [
            'two_fa_request_id' => $twoFaRequest->id,
            'code' => $otpCode,
            'used_at' => $twoFaRequest->otpVerificationCodes()->latest()->first()->used_at,
        ]);

        $this->assertNotNull($twoFaRequest->otpVerificationCodes()->latest()->first()->used_at);

        Event::assertDispatched(NeedTwoFaSmsCode::class);
        Event::assertListening(
            NeedTwoFaSmsCode::class,
            SendTwoFaSmsCode::class
        );
    }

    public function test_twoFaRequestVerifyValidCodeWithNoCode_expectedRequiredValidationError()
    {
        $twoFaRequest = TwoFaRequest::factory()
            ->afterSentCode()
            ->create();
        $otpCode = $twoFaRequest->generateOtpVerificationCode();

        $this->postJson(route('2fa_request.verify_code', ['twoFaRequest' => $twoFaRequest->id]), ['verification_code' => null])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['verification_code' => 'required']);
    }

    public function test_twoFaRequestVerifyValidCodeWithIncorrectCodeLength_expectedSizeValidationError()
    {
        $twoFaRequest = TwoFaRequest::factory()
            ->afterSentCode()
            ->create();
        $otpCode = $twoFaRequest->generateOtpVerificationCode();

        $this->postJson(route('2fa_request.verify_code', ['twoFaRequest' => $twoFaRequest->id]), ['verification_code' => config('2fa.code_length') - 1])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['verification_code' => 'The verification code must be ' . config('2fa.code_length') .' characters.']);
    }
}

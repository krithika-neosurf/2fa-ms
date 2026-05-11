<?php

namespace Tests\Feature;

use Mockery;
use Mockery\MockInterface;
use Neosurf\NeosurfMsTest;
use App\Models\TwoFaRequest;
use App\Events\NeedTwoFaSmsCode;
use App\Listeners\SendTwoFaSmsCode;
use Neosurf\Services\ClientService;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TwoFaResendCodeApiTest extends NeosurfMsTest
{
    use RefreshDatabase;

    public function test_twoFaRequestResendCode_expectedNewCodeSent()
    {
        Event::fake();

        $twoFaRequest = TwoFaRequest::factory()->create();

        $this->post(route('2fa_request.resend_code', ['twoFaRequest' => $twoFaRequest->id]))
            ->assertJsonStructure([
                'error',
                'message',
            ])->assertJson([
                'error' => false,
                'message' => 'New code sent'
            ], true);

        $twoFaRequest->refresh();

        Event::assertDispatched(NeedTwoFaSmsCode::class);
        Event::assertListening(
            NeedTwoFaSmsCode::class,
            SendTwoFaSmsCode::class
        );
    }

    public function test_twoFaRequestResendCodeLimitReached_expectedError()
    {
        // Default config for max retries is 5 before failing
        $twoFaRequest = TwoFaRequest::factory()->create();
        $twoFaRequest->generateOtpVerificationCode();
        $twoFaRequest->generateOtpVerificationCode();
        $twoFaRequest->generateOtpVerificationCode();
        $twoFaRequest->generateOtpVerificationCode();
        $twoFaRequest->generateOtpVerificationCode();

        $this->post(route('2fa_request.resend_code', ['twoFaRequest' => $twoFaRequest->id]))
            ->assertJsonStructure([
                'error',
                'message',
            ])->assertJson([
                'error' => true,
                'message' => 'Send code retries limit reached'
            ], true);
    }

    public function test_twoFaRequestResendCodeNoClientMobile_expectedError()
    {
        $this->instance(
            ClientService::class,
            Mockery::mock(ClientService::class, function (MockInterface $mock) {
                $mock->shouldReceive('find')
                    ->andReturn([
                        'telephone_prefix' => '',
                        'telephone_number' => '',
                    ]);
            })
        );

        $twoFaRequest = TwoFaRequest::factory()
            ->afterLogin()
            ->create();

        $this->post(route('2fa_request.resend_code', ['twoFaRequest' => $twoFaRequest->id]))
            ->assertJsonStructure([
                'error',
                'message',
            ])->assertJson([
                'error' => true,
                'message' => 'No client mobile'
            ], true);
    }
}

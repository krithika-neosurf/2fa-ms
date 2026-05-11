<?php

namespace Tests\Feature;

use Mockery;
use Mockery\MockInterface;
use Neosurf\NeosurfMsTest;
use App\Models\TwoFaRequest;
use App\Events\NeedTwoFaSmsCode;
use App\Listeners\SendTwoFaSmsCode;
use Neosurf\Services\ClientService;
use Neosurf\Services\MessageService;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;


class TwoFaNextActionApiTest extends NeosurfMsTest
{
    use RefreshDatabase;

    public function test_twoFaRequestClientFromLoginWithoutMobile_expectedToGetMobile()
    {
        $this->instance(
            ClientService::class,
            Mockery::mock(ClientService::class, function (MockInterface $mock) {
                $mock->shouldReceive('find')
                    ->andReturn([
                        'telephone_prefix' => null,
                        'telephone_number' => null,
                    ]);
            })
        );

        $twoFaRequest = TwoFaRequest::factory()
            ->afterLogin()
            ->create();

        $this->get(route('2fa_request.next_action', ['twoFaRequest' => $twoFaRequest->id]))
            ->assertJsonStructure([
                'twoFaRequestId',
                'nextAction',
                'data' => [
                    'message'
                ],
                'callback',
            ])->assertJson([
                'nextAction' => 'getMobile',
                'data' => [
                    'message' => __('messages.login_success'),
                ],
                'callback' => config('microservices.2fa_front.url') . '/en/2fa-requests/' . $twoFaRequest->id .'/get-mobile',
            ], true);
    }

    public function test_twoFaRequestClientFromLoginWithMobile_expectedToSendCode()
    {
        $this->instance(
            ClientService::class,
            Mockery::mock(ClientService::class, function (MockInterface $mock) {
                $mock->shouldReceive('find')
                    ->andReturn([
                        'telephone_prefix' => '+63',
                        'telephone_number' => '9181234567',
                    ]);
            })
        );

        $this->instance(
            MessageService::class,
            Mockery::mock(MessageService::class, function (MockInterface $mock) {
                $mock->shouldReceive('sendSms');
            })
        );

        $twoFaRequest = TwoFaRequest::factory()
            ->afterLogin()
            ->create();

        $this->get(route('2fa_request.next_action', ['twoFaRequest' => $twoFaRequest->id]))
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
    }

    public function test_twoFaRequestClientFromSessionWithoutMobile_expectedToGetMobile()
    {
        $this->instance(
            ClientService::class,
            Mockery::mock(ClientService::class, function (MockInterface $mock) {
                $mock->shouldReceive('findSession')
                    ->andReturn(['session' => ['user_id' => 'some-client-id']]);
                $mock->shouldReceive('find')
                    ->andReturn([
                        'telephone_prefix' => null,
                        'telephone_number' => null,
                    ]);
            })
        );

        $twoFaRequest = TwoFaRequest::factory()
            ->validSession()
            ->create();

        $this->get(route('2fa_request.next_action', ['twoFaRequest' => $twoFaRequest->id]))
            ->assertJsonStructure([
                'twoFaRequestId',
                'nextAction',
                'data' => [
                    'message'
                ],
                'callback',
            ])->assertJson([
                'nextAction' => 'getMobile',
                'data' => [
                    'message' => __('messages.session_login_success'),
                ],
                'callback' => config('microservices.2fa_front.url') . '/en/2fa-requests/' . $twoFaRequest->id .'/get-mobile',
            ], true);
    }

    public function test_twoFaRequestClientFromSessionWithMobile_expectedToSendCode()
    {
        Event::fake();
        $this->instance(
            ClientService::class,
            Mockery::mock(ClientService::class, function (MockInterface $mock) {
                $mock->shouldReceive('findSession')
                    ->andReturn(['session' => ['user_id' => 'some-client-id']]);
                $mock->shouldReceive('find')
                    ->andReturn([
                        'telephone_prefix' => '+63',
                        'telephone_number' => '9181234567',
                    ]);
            })
        );

        $twoFaRequest = TwoFaRequest::factory()
            ->validSession()
            ->create();

        $this->get(route('2fa_request.next_action', ['twoFaRequest' => $twoFaRequest->id]))
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

        Event::assertDispatched(NeedTwoFaSmsCode::class);
        Event::assertListening(
            NeedTwoFaSmsCode::class,
            SendTwoFaSmsCode::class
        );
    }

    public function test_twoFaRequestWithOtpCodeSent_expectedToVerifyCode()
    {
        $twoFaRequest = TwoFaRequest::factory()
            ->codeSent()
            ->create();

        $this->get(route('2fa_request.next_action', ['twoFaRequest' => $twoFaRequest->id]))
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
    }

    public function test_twoFaRequestWithInvalidCode_expectedFailure()
    {
        $twoFaRequest = TwoFaRequest::factory()
            ->invalidCode()
            ->create();

        $this->get(route('2fa_request.next_action', ['twoFaRequest' => $twoFaRequest->id]))
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
    }

    public function test_twoFaRequestWithCompleted_expectedSuccess()
    {
        $twoFaRequest = TwoFaRequest::factory()
            ->completed()
            ->create();

        $this->get(route('2fa_request.next_action', ['twoFaRequest' => $twoFaRequest->id]))
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
    }

    public function test_twoFaRequestSendCodeViaSmsFailed_expectedFailure()
    {
        $this->instance(
            ClientService::class,
            Mockery::mock(ClientService::class, function (MockInterface $mock) {
                $mock->shouldReceive('find')
                    ->andReturn([
                        'telephone_prefix' => '+63',
                        'telephone_number' => '9181234567',
                    ]);
            })
        );

        $this->instance(
            MessageService::class,
            Mockery::mock(MessageService::class, function (MockInterface $mock) {
                $mock->shouldReceive('sendSms')
                    ->andThrow(new \Exception('Something went wrong!'));
            })
        );

        $twoFaRequest = TwoFaRequest::factory()
            ->afterLogin()
            ->create();

        $this->get(route('2fa_request.next_action', ['twoFaRequest' => $twoFaRequest->id]))
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
                    'message' => 'Something went wrong!',
                ],
                'callback' => config('microservices.2fa_front.url') . '/en/2fa-requests/' . $twoFaRequest->id .'/failure',
            ], true);
    }
}

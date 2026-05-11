<?php

namespace Tests\Feature;

use Mockery;
use Neosurf\NeosurfMsTest;
use Mockery\MockInterface;
use App\Models\TwoFaRequest;
use App\Events\NeedTwoFaSmsCode;
use App\Listeners\SendTwoFaSmsCode;
use Neosurf\Services\ClientService;
use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TwoFaUpdateClientMobileApiTest extends NeosurfMsTest
{
    use RefreshDatabase;

    protected function stubRequest(array $data = [])
    {
        return array_replace([
            'telephone_prefix' => '+63',
            'telephone_number' => '9181234567',
        ], $data);
    }

    public function test_setClientMobileTwoFaRequest_expectedSendCode()
    {
        Event::fake();
        $this->instance(
            ClientService::class,
            Mockery::mock(ClientService::class, function (MockInterface $mock) {
                $mock->shouldReceive('updatePhone')
                    ->with($this->stubRequest());
            })
        );

        $twoFaRequest = TwoFaRequest::factory()->create();

        $this->post(route('2fa_request.set_mobile', ['twoFaRequest' => $twoFaRequest->id]), $this->stubRequest())
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

    public function test_setClientMobileTwoFaRequestWithNoTelephonePrefix_expectedRequiredValidationError()
    {
        $request = $this->stubRequest(['telephone_prefix' => null]);

        $twoFaRequest = TwoFaRequest::factory()->create();
        $this->postJson(route('2fa_request.set_mobile', ['twoFaRequest' => $twoFaRequest->id]), $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['telephone_prefix' => 'required']);
    }

    public function test_setClientMobileTwoFaRequestWithNotStringTelephonePrefix_expectedStringValidationError()
    {
        $request = $this->stubRequest(['telephone_prefix' => 123]);

        $twoFaRequest = TwoFaRequest::factory()->create();
        $this->postJson(route('2fa_request.set_mobile', ['twoFaRequest' => $twoFaRequest->id]), $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['telephone_prefix' => 'string']);
    }

    public function test_setClientMobileTwoFaRequestWithMin2TelephonePrefix_expectedMinValidationError()
    {
        $request = $this->stubRequest(['telephone_prefix' => '1']);

        $twoFaRequest = TwoFaRequest::factory()->create();
        $this->postJson(route('2fa_request.set_mobile', ['twoFaRequest' => $twoFaRequest->id]), $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['telephone_prefix' => 'The telephone prefix must be at least 2 characters.']);
    }

    public function test_setClientMobileTwoFaRequestWithMax5TelephonePrefix_expectedMaxValidationError()
    {
        $request = $this->stubRequest(['telephone_prefix' => '+123456']);

        $twoFaRequest = TwoFaRequest::factory()->create();
        $this->postJson(route('2fa_request.set_mobile', ['twoFaRequest' => $twoFaRequest->id]), $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['telephone_prefix' => 'The telephone prefix may not be greater than 5 characters.']);
    }

    public function test_setClientMobileTwoFaRequestWithNoTelephoneNumber_expectedRequiredValidationError()
    {
        $request = $this->stubRequest(['telephone_number' => null]);

        $twoFaRequest = TwoFaRequest::factory()->create();
        $this->postJson(route('2fa_request.set_mobile', ['twoFaRequest' => $twoFaRequest->id]), $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['telephone_number' => 'required']);
    }

    public function test_setClientMobileTwoFaRequestWithNotNumericTelephoneNumber_expectedNumericValidationError()
    {
        $request = $this->stubRequest(['telephone_number' => 'not-number']);

        $twoFaRequest = TwoFaRequest::factory()->create();
        $this->postJson(route('2fa_request.set_mobile', ['twoFaRequest' => $twoFaRequest->id]), $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['telephone_number' => 'The telephone number must be a number.']);
    }

    public function test_setClientMobileTwoFaRequestWithMin4TelephoneNumber_expectedMinValidationError()
    {
        $request = $this->stubRequest(['telephone_number' => '1']);

        $twoFaRequest = TwoFaRequest::factory()->create();
        $this->postJson(route('2fa_request.set_mobile', ['twoFaRequest' => $twoFaRequest->id]), $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['telephone_number' => 'The telephone number must be at least 4.']);
    }
}

<?php

namespace Tests\Feature;

use Mockery;
use Neosurf\NeosurfMsTest;
use Mockery\MockInterface;
use Illuminate\Support\Arr;
use App\Models\TwoFaRequest;
use Neosurf\Services\ClientService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TwoFaStartApiTest extends NeosurfMsTest
{
    use RefreshDatabase;

    protected function getStubTwoFaBody(array $data = []): array
    {
        $stub = array_replace([
            "session_id" => null,
            "origin" => "neopass",
            "access_id" => "5dd0f5c8-2d11-343a-af62-20b4df6ece3c",
            "url_back" => "http://merchant.com/back",
            "url_success" => "http://merchant.com/success",
            "url_failure" => "http://merchant.com/failure",
            "locale" => "en",
            "ip_address" => "127.0.0.1",
        ], $data);

        if (!$stub['session_id']) {
            unset($stub['session_id']);
        }

        return $stub;
    }

    protected function generateSignature($data): string
    {
        return password_hash(serialize($data), PASSWORD_ARGON2ID);
    }

    public function test_startTwoFaRequestWithInvalidSignature_expectedFailure()
    {
        $request = $this->getStubTwoFaBody(['signature' => 'some-invalid-signature']);

        $response = $this->post(route('start'), $request);
        $response->assertJsonStructure([
                'twoFaRequestId',
                'nextAction',
                'data' => [
                    'message'
                ],
                'callback',
            ])->assertJson([
                'nextAction' => 'failure',
                'data' => [
                    'message' => __('errors.invalid_signature'),
                ],
                'callback' => config('microservices.2fa_front.url') . '/en/2fa-requests/' . $response['twoFaRequestId'] .'/failure',
            ], true);
        
        $this->assertDatabaseHas('two_fa_requests', [
            'id' => $response['twoFaRequestId'],
            'status' => TwoFaRequest::STATUS['INVALID_SIGNATURE'],
        ]);
    }

    public function test_startTwoFaRequestWithNoSession_expectedLogin()
    {
        $request = $this->getStubTwoFaBody();
        $request = array_merge($request, ['signature' => $this->generateSignature($request)]);

        $response = $this->post(route('start'), $request);
        $response->assertJsonStructure([
                'twoFaRequestId',
                'nextAction',
                'data' => [
                    'message'
                ],
                'callback',
            ])->assertJson([
                'nextAction' => 'login',
                'data' => [
                    'message' => __('errors.no_session'),
                ],
                'callback' => config('microservices.2fa_front.url') . '/en/2fa-requests/' . $response['twoFaRequestId'] .'/login',
            ], true);
        
        $this->assertDatabaseHas('two_fa_requests', [
            'id' => $response['twoFaRequestId'],
            'status' => TwoFaRequest::STATUS['VALID_SIGNATURE'],
        ]);
    }

    public function test_startTwoFaRequestWithNonExistentSession_expectedLogin()
    {
        $this->instance(
            ClientService::class,
            Mockery::mock(ClientService::class, function (MockInterface $mock) {
                $mock->shouldReceive('findSession')
                    ->once()
                    ->andReturn(['exists' => false]);
            })
        );

        $request = $this->getStubTwoFaBody(['session_id' => '00000000-0000-0000-0000-000000000000']);
        $request = array_merge($request, ['signature' => $this->generateSignature($request)]);

        $response = $this->post(route('start'), $request);
        $response->assertJsonStructure([
                'twoFaRequestId',
                'nextAction',
                'data' => [
                    'message'
                ],
                'callback',
            ])->assertJson([
                'nextAction' => 'login',
                'data' => [
                    'message' => __('errors.invalid_session'),
                ],
                'callback' => config('microservices.2fa_front.url') . '/en/2fa-requests/' . $response['twoFaRequestId'] .'/login',
            ], true);
        
        $this->assertDatabaseHas('two_fa_requests', [
            'id' => $response['twoFaRequestId'],
            'status' => TwoFaRequest::STATUS['INVALID_SESSION'],
        ]);
    }

    public function test_startTwoFaRequestWithValidSession_expectedLoginSession()
    {
        $this->instance(
            ClientService::class,
            Mockery::mock(ClientService::class, function (MockInterface $mock) {
                $mock->shouldReceive('findSession')
                    ->once()
                    ->andReturn(['exists' => true]);
            })
        );

        $request = $this->getStubTwoFaBody(['session_id' => '00000000-0000-0000-0000-000000000000']);
        $request = array_merge($request, ['signature' => $this->generateSignature($request)]);

        $response = $this->post(route('start'), $request);
        $response->assertJsonStructure([
                'twoFaRequestId',
                'nextAction',
                'data' => [
                    'message'
                ],
                'callback',
            ])->assertJson([
                'nextAction' => 'login-session',
                'data' => [
                    'message' => __('messages.login_session'),
                ],
                'callback' => config('microservices.2fa_front.url') . '/en/2fa-requests/' . $response['twoFaRequestId'] .'/login-session',
            ], true);
        
        $this->assertDatabaseHas('two_fa_requests', [
            'id' => $response['twoFaRequestId'],
            'status' => TwoFaRequest::STATUS['VALID_SESSION'],
        ]);
    }

    public function test_startTwoFaRequestWithNotUuidSession_expectedUuidValidationError()
    {
        $request = $this->getStubTwoFaBody(['session_id' => 'not-uuid-session']);
        
        $this->postJson(route('start'), $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['session_id' => 'The session id must be a valid UUID.']);
    }

    public function test_startTwoFaRequestWithInvalidOrigin_expectedInValidationError()
    {
        $request = $this->getStubTwoFaBody(['origin' => 'invalid-origin']);
        
        $this->postJson(route('start'), $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['origin' => 'in']);
    }

    public function test_startTwoFaRequestWithNotUuidAccessId_expectedUuidValidationError()
    {
        $request = $this->getStubTwoFaBody(['access_id' => 'not-uuid-access-id']);
        
        $this->postJson(route('start'), $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['access_id' => 'The access id must be a valid UUID.']);
    }

    public function test_startTwoFaRequestWithNoAccessId_expectedRequiredValidationError()
    {
        $request = $this->getStubTwoFaBody();
        Arr::pull($request, 'access_id');
        
        $this->postJson(route('start'), $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['access_id' => 'required']);
    }

    public function test_startTwoFaRequestWithNotStringSignature_expectedStringValidationError()
    {
        $request = $this->getStubTwoFaBody(['signature' => 123]);
        
        $this->postJson(route('start'), $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['signature' => 'string']);
    }

    public function test_startTwoFaRequestWithNoSignature_expectedRequiredValidationError()
    {
        $request = $this->getStubTwoFaBody();
        
        $this->postJson(route('start'), $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['signature' => 'required']);
    }

    public function test_startTwoFaRequestWithNotUrlUrlBack_expectedUrlValidationError()
    {
        $request = $this->getStubTwoFaBody(['url_back' => 'not-url']);
        
        $this->postJson(route('start'), $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['url_back' => 'url']);
    }

    public function test_startTwoFaRequestWithNoUrlBack_expectedRequiredValidationError()
    {
        $request = $this->getStubTwoFaBody();
        Arr::pull($request, 'url_back');
        
        $this->postJson(route('start'), $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['url_back' => 'required']);
    }

    public function test_startTwoFaRequestWithNotUrlUrlSuccess_expectedUrlValidationError()
    {
        $request = $this->getStubTwoFaBody(['url_success' => 'not-url']);
        
        $this->postJson(route('start'), $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['url_success' => 'url']);
    }

    public function test_startTwoFaRequestWithNoUrlSuccess_expectedRequiredValidationError()
    {
        $request = $this->getStubTwoFaBody();
        Arr::pull($request, 'url_success');
        
        $this->postJson(route('start'), $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['url_success' => 'required']);
    }

    public function test_startTwoFaRequestWithNotUrlUrlFailure_expectedUrlValidationError()
    {
        $request = $this->getStubTwoFaBody(['url_failure' => 'not-url']);
        
        $this->postJson(route('start'), $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['url_failure' => 'url']);
    }

    public function test_startTwoFaRequestWithNoUrlFailure_expectedRequiredValidationError()
    {
        $request = $this->getStubTwoFaBody();
        Arr::pull($request, 'url_failure');
        
        $this->postJson(route('start'), $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['url_failure' => 'required']);
    }

    public function test_startTwoFaRequestWithNotStringLocale_expectedStringValidationError()
    {
        $request = $this->getStubTwoFaBody(['locale' => 12]);
        
        $this->postJson(route('start'), $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['locale' => 'string']);
    }

    public function test_startTwoFaRequestWithNotMax2Locale_expectedMaxValidationError()
    {
        $request = $this->getStubTwoFaBody(['locale' => 'english']);
        
        $this->postJson(route('start'), $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['locale' => 'The locale may not be greater than 2 characters.']);
    }

    public function test_startTwoFaRequestWithNotIpIpAddress_expectedIpValidationError()
    {
        $request = $this->getStubTwoFaBody(['ip_address' => 'not-ip-address']);
        
        $this->postJson(route('start'), $request)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['ip_address' => 'ip']);
    }
}

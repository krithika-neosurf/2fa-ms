<?php

namespace Tests\Feature;

use Neosurf\NeosurfMsTest;
use App\Models\TwoFaRequest;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TwoFaSetClientApiTest extends NeosurfMsTest
{
    use RefreshDatabase, WithFaker;

    protected function stubRequest(array $data = [])
    {
        return array_replace(['client_id' => $this->faker->uuid], $data);
    }

    public function test_setClientTwoFaRequest()
    {
        $twoFaRequest = TwoFaRequest::factory()->create();

        $this->post(route('2fa_request.set_client', ['twoFaRequest' => $twoFaRequest->id]), $this->stubRequest())
            ->assertNoContent();

        $twoFaRequest->refresh();
        $this->assertDatabaseHas('two_fa_requests', [
            'id' => $twoFaRequest->id,
            'client_id' => $twoFaRequest->client_id,
        ]);
    }

    public function test_setClientTwoFaRequestWithNoClientId_expectedRequiredValidationError()
    {
        $twoFaRequest = TwoFaRequest::factory()->create();

        $this->postJson(route('2fa_request.set_client', ['twoFaRequest' => $twoFaRequest->id]), $this->stubRequest(['client_id' => null]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['client_id' => 'required']);
    }

    public function test_setClientTwoFaRequestWithNotUuidClientId_expectedUuidValidationError()
    {
        $twoFaRequest = TwoFaRequest::factory()->create();

        $this->postJson(route('2fa_request.set_client', ['twoFaRequest' => $twoFaRequest->id]), $this->stubRequest(['client_id' => 1]))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['client_id' => 'The client id must be a valid UUID.']);
    }
}

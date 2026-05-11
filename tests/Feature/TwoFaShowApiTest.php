<?php

namespace Tests\Feature;

use Neosurf\NeosurfMsTest;
use App\Models\TwoFaRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TwoFaShowApiTest extends NeosurfMsTest
{
    use RefreshDatabase;

    public function test_showTwoFaRequest()
    {
        $twoFaRequest = TwoFaRequest::factory()->create();

        $this->get(route('2fa_request.show', ['twoFaRequest' => $twoFaRequest->id]))
            ->assertJson($twoFaRequest->toArray(), true);
    }
}

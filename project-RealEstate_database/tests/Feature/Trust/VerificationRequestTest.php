<?php

namespace Tests\Feature\Trust;

use App\Models\User;
use App\Models\VerificationRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\InteractsWithTrustApi;
use Tests\TestCase;

class VerificationRequestTest extends TestCase
{
    use InteractsWithTrustApi;
    use RefreshDatabase;

    public function test_user_can_submit_verification_request(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAsApiUser($user)->postJson(
            $this->apiPrefix().'/my/verification-requests',
            [
                'document_type' => 'national_id',
                'document_path' => 'verification-documents/test.pdf',
            ]
        );

        $this->assertApiSuccess($response, 201);
        $this->assertDatabaseHas('verification_requests', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);
    }

    public function test_user_can_list_own_verification_requests(): void
    {
        $user = User::factory()->create();
        VerificationRequest::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAsApiUser($user)->getJson(
            $this->apiPrefix().'/my/verification-requests'
        );

        $this->assertApiSuccess($response);
        $this->assertCount(1, $response->json('data'));
    }
}
